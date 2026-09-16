<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\ArtisanProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function showRegister() { return view('auth.register'); }

    public function register(RegisterRequest $request)
    {
        // 1. Générer le token de vérification
        $token = Str::random(64);

        // 2. Créer l'utilisateur (email NON vérifié)
        // forceCreate : role/is_active/email_verified_at ne sont plus dans
        // $fillable (fix mass-assignment) — ce create reste sûr car 'role'
        // provient de RegisterRequest, validé strictement à artisan/client/livreur.
        $user = User::forceCreate([
            'name'                     => $request->name,
            'email'                    => $request->email,
            'password'                 => Hash::make($request->password),
            'role'                     => $request->role,
            'phone'                    => $request->phone,
            'city'                     => $request->city,
            'is_active'                => true,
            'email_verification_token' => $token,
            'email_verified_at'        => null,
        ]);

        // 3. Créer le profil artisan si besoin
        if ($user->role === 'artisan') {
            ArtisanProfile::create([
                'user_id'   => $user->id,
                'specialty' => $request->specialty ?? '',
                'category'  => $request->category  ?? '',
            ]);
        }

        // 4. Envoyer l'email de vérification
        $url = route('email.verify', [
            'token' => $token,
            'email' => $user->email,
        ]);

        try {
            Mail::send('emails.verify_email', [
                'user' => $user,
                'url'  => $url,
            ], fn($m) => $m->to($user->email, $user->name)
                            ->subject('Vérifiez votre email — ArtisanHub'));
        } catch (\Exception $e) {
            Log::error("Email vérification non envoyé [{$user->email}] : " . $e->getMessage());
        }

        // 5. Connecter + rediriger vers page "vérifiez votre email"
        Auth::login($user);

        return redirect()->route('email.notice')
            ->with('success', 'Bienvenue ! Un email de vérification a été envoyé à ' . $user->email);
    }

    public function showLogin() { return view('auth.login'); }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Email ou mot de passe incorrect.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $user = Auth::user();

        if (!$user->is_active) {
            Auth::logout();
            return back()->withErrors(['email' => 'Votre compte a été suspendu.']);
        }

        // Email non vérifié → page de notice
        if (!$user->email_verified_at) {
            return redirect()->route('email.notice');
        }

        $dashboards = [
            'artisan' => 'artisan.dashboard',
            'client'  => 'client.dashboard',
            'livreur' => 'livreur.dashboard',
            'admin'   => 'admin.dashboard',
        ];

        return redirect()->intended(route($dashboards[$user->role] ?? 'home'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home')->with('success', 'Vous êtes déconnecté.');
    }
}
