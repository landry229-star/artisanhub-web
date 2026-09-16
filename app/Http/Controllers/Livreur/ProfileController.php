<?php

namespace App\Http\Controllers\Livreur;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UpdatesPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use UpdatesPassword;

    public function edit()
    {
        $user = Auth::user();
        return view('livreur.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'             => ['required', 'string', 'max:100'],
            'phone'            => ['nullable', 'string', 'max:20'],
            'city'             => ['required', 'string', 'max:100'],
            'delivery_address' => ['nullable', 'string', 'max:255'],
            'preferred_language' => ['nullable', 'string', 'in:'.implode(',', array_keys(\App\Models\User::availableLanguages()))],
            'avatar'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $data = $request->only('name', 'phone', 'city', 'delivery_address', 'preferred_language');

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }
}
