<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\AdminExportLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'admin')->with('artisanProfile');

        if ($request->filled('role'))   $query->where('role', $request->role);
        if ($request->filled('search')) {
            // Important : le orWhere doit être encapsulé dans une closure,
            // sinon il casse par précédence SQL le where('role','!=','admin')
            // ci-dessus (et les filtres role/city ci-dessous) — un email
            // correspondant à un compte admin le faisait apparaître dans les
            // résultats malgré le filtre, et les combinaisons role+search ou
            // city+search ne filtraient plus correctement.
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        if ($request->filled('city'))   $query->where('city', $request->city);

        $users = $query->latest()->paginate(20);
        return view('admin.users.index', compact('users'));
    }

    public function verify(User $user)
    {
        abort_if($user->role !== 'artisan', 422);
        $user->forceFill(['is_verified' => true])->save();
        AdminAuditLog::record('admin.user.verify', $user, [
            'user_id' => $user->id,
            'user_name' => $user->name,
            'verified' => true,
        ]);
        return back()->with('success', "Artisan {$user->name} vérifié.");
    }

    /** Approuve la pièce d'identité soumise par l'utilisateur. */
    public function approveDocument(User $user)
    {
        abort_if($user->id_document_status !== 'pending', 422, 'Aucun document en attente pour cet utilisateur.');

        $user->update([
            'id_document_status'      => 'approved',
            'id_document_reviewed_at' => now(),
        ]);
        AdminAuditLog::record('admin.user.document.approve', $user, [
            'document_type' => $user->id_document_type,
            'status' => 'approved',
        ]);

        return back()->with('success', "Pièce d'identité de {$user->name} approuvée.");
    }

    /** Rejette la pièce d'identité (motif obligatoire). */
    public function rejectDocument(Request $request, User $user)
    {
        $request->validate(['reason' => ['required', 'string', 'max:255']]);
        abort_if($user->id_document_status !== 'pending', 422);

        $user->update([
            'id_document_status'            => 'rejected',
            'id_document_rejected_reason'   => $request->reason,
            'id_document_reviewed_at'       => now(),
        ]);
        AdminAuditLog::record('admin.user.document.reject', $user, [
            'reason' => $request->reason,
            'status' => 'rejected',
        ]);

        return back()->with('success', "Pièce d'identité de {$user->name} rejetée.");
    }

    public function suspend(User $user)
    {
        abort_if($user->role === 'admin', 422, 'Impossible de suspendre un admin.');
        DB::transaction(function () use ($user) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            abort_if($locked->role === 'admin', 422, 'Impossible de suspendre un admin.');
            $locked->forceFill(['is_active' => !$locked->is_active])->save();
            return $locked;
        });
        $user->refresh();
        $action = $user->is_active ? 'réactivé' : 'suspendu';
        AdminAuditLog::record('admin.user.suspend', $user, [
            'is_active' => $user->is_active,
            'status_label' => $action,
        ]);
        return back()->with('success', "Compte {$action}.");
    }

    public function destroy(User $user)
    {
        abort_if($user->role === 'admin', 422, 'Impossible de supprimer un admin.');
        AdminAuditLog::record('admin.user.destroy', $user, [
            'deleted' => true,
            'role' => $user->role,
        ]);
        $user->delete();
        return back()->with('success', 'Utilisateur supprimé.');
    }

    public function export(Request $request)
    {
        $query = User::where('role', '!=', 'admin')->select(['id','name','email','role','city','is_verified','is_active','created_at']);
        $rows = $query->latest('id')->get();
        $format = $request->string('format')->value() ?: 'csv';
        $filters = $request->except('format');

        if ($format === 'pdf') {
            AdminExportLog::create([
                'user_id' => Auth::id(),
                'resource' => 'utilisateurs',
                'format' => 'pdf',
                'filename' => 'utilisateurs-' . now()->format('Ymd-His') . '.pdf',
                'filters' => $filters,
            ]);

            return Pdf::loadView('admin.exports.users', compact('rows'))->setPaper('a4', 'landscape')->download('utilisateurs-' . now()->format('Ymd-His') . '.pdf');
        }
        if ($format === 'excel') {
            AdminExportLog::create([
                'user_id' => Auth::id(),
                'resource' => 'utilisateurs',
                'format' => 'excel',
                'filename' => 'utilisateurs-' . now()->format('Ymd-His') . '.xls',
                'filters' => $filters,
            ]);

            return response()->view('admin.exports.users-excel', compact('rows'), 200, [
                'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename=utilisateurs-' . now()->format('Ymd-His') . '.xls',
            ]);
        }

        $filename = 'utilisateurs-' . now()->format('Ymd-His') . '.csv';
        AdminExportLog::create([
            'user_id' => Auth::id(),
            'resource' => 'utilisateurs',
            'format' => 'csv',
            'filename' => $filename,
            'filters' => $filters,
        ]);

        $headers = ['Content-Type' => 'text/csv; charset=UTF-8', 'Content-Disposition' => 'attachment; filename=' . $filename];

        return response()->stream(function () use ($query) {
            $f = fopen('php://output', 'w');
            fputs($f, "\xEF\xBB\xBF"); // BOM UTF-8
            fputcsv($f, ['ID','Nom','Email','Rôle','Ville','Vérifié','Actif','Inscrit le']);
            $query->chunkById(500, function ($users) use ($f) {
                foreach ($users as $u) {
                    fputcsv($f, [
                        $u->id, $u->name, $u->email, $u->role,
                        $u->city, $u->is_verified ? 'Oui' : 'Non',
                        $u->is_active ? 'Oui' : 'Non',
                        $u->created_at->format('d/m/Y'),
                    ]);
                }
            });
            fclose($f);
        }, 200, $headers);
    }
}
