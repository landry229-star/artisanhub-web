<?php
namespace App\Http\Controllers;

use App\Services\WhatsAppSmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

/**
 * KYC léger, ouvert à tous les rôles authentifiés (surtout utile pour les
 * artisans qui veulent atteindre le palier Expert et proposer la garantie
 * "satisfait ou repris" — voir ArtisanProfile::canOfferGuarantee()).
 *
 * Deux briques indépendantes :
 *  1. Pièce d'identité (upload) → revue manuelle par un admin.
 *  2. Numéro de téléphone → vérifié par un code OTP à 6 chiffres.
 */
class KycController extends Controller
{
    public function __construct(private WhatsAppSmsService $sms) {}

    /** Formulaire d'état KYC (affiché dans le profil). */
    public function show()
    {
        $user = Auth::user();
        return view('kyc.show', compact('user'));
    }

    /** Upload / remplacement de la pièce d'identité. */
    public function uploadDocument(Request $request)
    {
        $request->validate([
            'id_document_type' => ['required', 'in:cni,passeport,permis'],
            'id_document'      => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ], [], [
            'id_document' => 'document',
        ]);

        $user = Auth::user();

        if ($user->id_document_path) {
            Storage::disk('local')->delete($user->id_document_path);
        }

        // Disque privé (storage/app/private) : jamais exposé via /storage/...,
        // uniquement accessible via KycController::viewDocument() avec autorisation.
        $path = $request->file('id_document')->store('kyc', 'local');

        $user->update([
            'id_document_path'             => $path,
            'id_document_type'             => $request->id_document_type,
            'id_document_status'           => 'pending',
            'id_document_rejected_reason'  => null,
            'id_document_reviewed_at'      => null,
        ]);

        return back()->with('success', '📄 Document envoyé. Un administrateur va le vérifier sous peu.');
    }

    /** Envoie un code OTP à 6 chiffres par WhatsApp/SMS. */
    public function sendPhoneOtp()
    {
        $user = Auth::user();

        if (!$user->phone) {
            return back()->with('error', 'Renseignez d\'abord un numéro de téléphone dans votre profil.');
        }

        $code = (string) random_int(100000, 999999);

        $user->update([
            'phone_otp_code'       => Hash::make($code),
            'phone_otp_expires_at' => now()->addMinutes(10),
            'phone_otp_attempts'   => 0,
        ]);

        if (!$this->sms->send($this->toE164($user->phone), "Votre code de vérification ArtisanHub : {$code} (valable 10 min).")) {
            $user->update([
                'phone_otp_code' => null,
                'phone_otp_expires_at' => null,
                'phone_otp_attempts' => 0,
            ]);

            return back()->with('error', 'Impossible d\'envoyer le code. Veuillez réessayer.');
        }

        return back()->with('success', '📲 Code envoyé par WhatsApp/SMS. Il est valable 10 minutes.');
    }

    /** Vérifie le code OTP saisi par l'utilisateur. */
    public function verifyPhoneOtp(Request $request)
    {
        $request->validate(['code' => ['required', 'string', 'size:6']]);

        $user = Auth::user();

        if (!$user->phone_otp_code || !$user->phone_otp_expires_at || $user->phone_otp_expires_at->isPast()) {
            return back()->with('error', 'Code expiré. Demandez un nouveau code.');
        }

        if ($user->phone_otp_attempts >= 5) {
            return back()->with('error', 'Trop de tentatives. Demandez un nouveau code.');
        }

        if (!Hash::check($request->code, $user->phone_otp_code)) {
            $user->increment('phone_otp_attempts');
            return back()->with('error', 'Code incorrect.');
        }

        $user->update([
            'phone_verified_at'    => now(),
            'phone_otp_code'       => null,
            'phone_otp_expires_at' => null,
            'phone_otp_attempts'   => 0,
        ]);

        return back()->with('success', '✅ Numéro de téléphone vérifié !');
    }

    /**
     * Sert le document d'identité de manière sécurisée : uniquement son
     * propriétaire ou un admin peuvent y accéder — jamais d'URL publique
     * devinable (le fichier vit sur le disque privé 'local').
     */
    public function viewDocument(\App\Models\User $user)
    {
        $viewer = Auth::user();
        abort_unless(
            $viewer->id === $user->id || $viewer->role === 'admin',
            403,
            "Vous n'êtes pas autorisé à consulter ce document."
        );
        abort_unless($user->id_document_path && Storage::disk('local')->exists($user->id_document_path), 404);

        return Storage::disk('local')->response($user->id_document_path);
    }

    private function toE164(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);
        if (str_starts_with($digits, '229')) return '+' . $digits;
        return '+229' . ltrim($digits, '0');
    }
}
