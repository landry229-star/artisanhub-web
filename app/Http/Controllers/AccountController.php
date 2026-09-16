<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
{
    public function destroy(Request $request)
    {
        $request->validate(['password' => ['required', 'current_password']]);

        $user = $request->user();
        abort_if($user->isAdmin(), 422, 'Un compte administrateur doit être désactivé manuellement.');

        if ($user->avatar) {
            Storage::disk('public')->delete($user->avatar);
        }
        if ($user->id_document_path) {
            Storage::disk('local')->delete($user->id_document_path);
        }

        $user->forceFill([
            'name' => 'Compte supprimé',
            'email' => 'deleted-'.$user->id.'@invalid.artisanhub.local',
            'phone' => null,
            'avatar' => null,
            'id_document_path' => null,
            'id_document_type' => null,
            'id_document_status' => 'none',
            'id_document_rejected_reason' => null,
            'phone_verified_at' => null,
            'phone_otp_code' => null,
            'phone_otp_expires_at' => null,
            'is_active' => false,
        ])->save();

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Votre compte a été anonymisé et désactivé.');
    }
}
