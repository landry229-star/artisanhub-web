<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FavoriteController extends Controller
{
    /** Toggle favori (ajouter/retirer) */
    public function toggle(int $artisanId)
    {
        $user    = Auth::user();
        $artisan = User::where('role','artisan')->findOrFail($artisanId);

        $exists = DB::table('favorites')
            ->where('client_id', $user->id)
            ->where('artisan_id', $artisanId)
            ->exists();

        if ($exists) {
            DB::table('favorites')
                ->where('client_id', $user->id)
                ->where('artisan_id', $artisanId)
                ->delete();
            $message = "Artisan retiré de vos favoris.";
            $isFav   = false;
        } else {
            DB::table('favorites')->insert([
                'client_id'  => $user->id,
                'artisan_id' => $artisanId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $message = "✅ Artisan ajouté à vos favoris !";
            $isFav   = true;
        }

        if (request()->ajax()) {
            return response()->json(['is_favorite' => $isFav, 'message' => $message]);
        }

        return back()->with('success', $message);
    }

    /** Liste des favoris du client */
    public function index()
    {
        $favoriteIds = DB::table('favorites')
            ->where('client_id', Auth::id())
            ->pluck('artisan_id');

        $artisans = User::whereIn('id', $favoriteIds)
            ->where('role','artisan')
            ->with('artisanProfile')
            ->get();

        return view('client.favorites.index', compact('artisans'));
    }
}
