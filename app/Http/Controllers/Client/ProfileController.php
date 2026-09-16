<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UpdatesPassword;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    use UpdatesPassword;

    public function edit()
    {
        $user = Auth::user();
        return view('client.profile.edit', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'name'  => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'city'  => ['required', 'string', Rule::in(config('artisanhub.cities', []))],
            'quartier' => ['nullable', 'string', 'max:120'],
            'delivery_address' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'preferred_language' => ['nullable', 'string', 'in:'.implode(',', array_keys(\App\Models\User::availableLanguages()))],
            'avatar'=> ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'email_notifications' => ['sometimes', 'boolean'],
            'order_notifications' => ['sometimes', 'boolean'],
            'whatsapp_opt_in' => ['sometimes', 'boolean'],
        ]);

        $data = $request->only(
            'name', 'phone', 'city', 'quartier', 'delivery_address',
            'latitude', 'longitude', 'preferred_language'
        );
        $data['email_notifications'] = $request->boolean('email_notifications');
        $data['order_notifications'] = $request->boolean('order_notifications');
        $data['whatsapp_opt_in'] = $request->boolean('whatsapp_opt_in');

        if ($request->input('phone') !== $user->phone) {
            $data['phone_verified_at'] = null;
            $data['phone_otp_code'] = null;
            $data['phone_otp_expires_at'] = null;
            $data['phone_otp_attempts'] = 0;
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function clearDeliveryAddress()
    {
        Auth::user()->update([
            'delivery_address' => null,
            'quartier' => null,
            'latitude' => null,
            'longitude' => null,
        ]);

        return back()->with('success', 'Adresse de livraison supprimée.');
    }
}
