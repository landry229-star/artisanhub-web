<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailVerificationController extends Controller
{
    public function notice()
    {
        if (auth()->check() && auth()->user()->email_verified_at) {
            $dashboards = ['artisan'=>'artisan.dashboard','client'=>'client.dashboard','livreur'=>'livreur.dashboard','admin'=>'admin.dashboard'];
            return redirect()->route($dashboards[auth()->user()->role] ?? 'home');
        }
        return view('auth.verify_email_notice');
    }

    public function send()
    {
        $user = Auth::user();
        if ($user->email_verified_at) return back()->with('info', 'Email déjà vérifié.');

        $token = Str::random(64);
        $user->email_verification_token = $token;
        $user->save();

        $url = route('email.verify', ['token' => $token, 'email' => $user->email]);

        try {
            Mail::send('emails.verify_email', ['user'=>$user,'url'=>$url],
                fn($m) => $m->to($user->email,$user->name)->subject('Vérifiez votre email — ArtisanHub')
            );
            return back()->with('success', 'Email renvoyé à '.$user->email.'. Vérifiez aussi vos spams.');
        } catch (\Exception $e) {
            Log::error("Email vérif échoué [{$user->email}] : ".$e->getMessage());
            return back()->with('error', 'Impossible d\'envoyer l\'email. Réessayez.');
        }
    }

    public function verify(Request $request)
    {
        if (!$request->filled('token') || !$request->filled('email')) {
            return redirect()->route('home')->with('error', 'Lien invalide.');
        }

        $user = User::where('email', $request->email)
                    ->where('email_verification_token', $request->token)
                    ->first();

        if (!$user) {
            return redirect()->route('home')
                ->with('error', 'Lien invalide ou déjà utilisé. Demandez un nouveau lien.');
        }

        $user->forceFill([
            'email_verified_at'        => now(),
            'email_verification_token' => null,
        ])->save();

        if (!Auth::check()) Auth::login($user);

        $dashboards = ['artisan'=>'artisan.dashboard','client'=>'client.dashboard','livreur'=>'livreur.dashboard','admin'=>'admin.dashboard'];

        return redirect()->route($dashboards[$user->role] ?? 'home')
            ->with('success', '✅ Email vérifié ! Bienvenue sur ArtisanHub, '.$user->name.' !');
    }
}
