<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\Profile\UpdatePasswordRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

/**
 * À utiliser dans les ProfileController de chaque espace (artisan, client,
 * livreur, admin) pour ajouter l'action "changer mon mot de passe" sans
 * dupliquer la logique.
 */
trait UpdatesPassword
{
    public function updatePassword(UpdatePasswordRequest $request)
    {
        $user = Auth::user();

        $user->update([
            'password' => Hash::make($request->validated()['password']),
        ]);

        return back()->with('success', 'Mot de passe modifié avec succès.');
    }
}
