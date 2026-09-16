<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /** Page "mot de passe oublié" */
    public function showForgotForm()
    {
        return view('auth.forgot_password');
    }

    /** Envoyer le lien de réinitialisation */
    public function sendResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Anti-enumération : même message si email existe ou non
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Supprimer l'ancien token
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();

            $token = Str::random(64);
            DB::table('password_reset_tokens')->insert([
                'email'      => $request->email,
                'token'      => Hash::make($token),
                'created_at' => now(),
            ]);

            $url = url('/mot-de-passe/reinitialiser') . '?' . http_build_query([
                'token' => $token,
                'email' => $request->email,
            ]);

            try {
                Mail::send('emails.reset_password', [
                    'user' => $user,
                    'url'  => $url,
                ], fn($m) => $m->to($request->email, $user->name)
                                ->subject('Réinitialisez votre mot de passe — ArtisanHub'));
            } catch (\Exception $e) {
                Log::error("Reset password email failed [{$request->email}]: " . $e->getMessage());
            }
        }

        return back()->with('success',
            'Si un compte existe avec cet email, vous recevrez un lien sous quelques minutes. Vérifiez aussi vos spams.'
        );
    }

    /** Formulaire nouveau mot de passe */
    public function showResetForm(Request $request)
    {
        if (!$request->filled('token') || !$request->filled('email')) {
            return redirect()->route('password.forgot')->with('error', 'Lien invalide.');
        }
        return view('auth.reset_password', [
            'token' => $request->token,
            'email' => $request->email,
        ]);
    }

    /** Enregistrer le nouveau mot de passe */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'                 => ['required', 'email'],
            'token'                 => ['required'],
            'password'              => ['required', 'min:8', 'confirmed'],
            'password_confirmation' => ['required'],
        ], [
            'password.min'       => 'Minimum 8 caractères.',
            'password.confirmed' => 'Les mots de passe ne correspondent pas.',
        ]);

        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (!$record || !Hash::check($request->token, $record->token)) {
            return back()->withErrors(['token' => 'Lien invalide ou expiré.']);
        }

        // Expiration après 60 minutes
        if (now()->diffInMinutes($record->created_at) > 60) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return redirect()->route('password.forgot')
                ->with('error', 'Ce lien a expiré. Demandez un nouveau lien.');
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => Hash::make($request->password)]);

        // Invalider le token utilisé
        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('login')
            ->with('success', '✅ Mot de passe modifié ! Vous pouvez vous connecter.');
    }
}
