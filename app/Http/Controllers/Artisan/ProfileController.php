<?php
namespace App\Http\Controllers\Artisan;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\UpdatesPassword;
use App\Http\Requests\Profile\UpdateProfileRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    use UpdatesPassword;

    public function edit()
    {
        $user    = Auth::user();
        $profile = $user->artisanProfile ?: $user->artisanProfile()->create([
            'specialty' => 'Artisan',
            'category' => 'autre',
        ]);
        $cities  = config('artisanhub.cities');
        $categories = config('artisanhub.categories');
        return view('artisan.profile.edit', compact('user', 'profile', 'cities', 'categories'));
    }

    public function update(UpdateProfileRequest $request)
    {
        $user    = Auth::user();
        $profile = $user->artisanProfile ?: $user->artisanProfile()->create([
            'specialty' => 'Artisan',
            'category' => 'autre',
        ]);

        // Mise à jour user
        $userData = $request->only('name', 'phone', 'city', 'quartier', 'latitude', 'longitude', 'preferred_language');
        if ($request->input('phone') !== $user->phone) {
            $userData['phone_verified_at'] = null;
            $userData['phone_otp_code'] = null;
            $userData['phone_otp_expires_at'] = null;
            $userData['phone_otp_attempts'] = 0;
        }
        $userData['whatsapp_opt_in'] = $request->boolean('whatsapp_opt_in');
        if ($request->hasFile('avatar')) {
            if ($user->avatar) Storage::disk('public')->delete($user->avatar);
            $userData['avatar'] = app(\App\Services\ImageService::class)->store(
                $request->file('avatar'),
                'avatars',
                500,
                500,
                82
            );
        }
        $user->update($userData);

        // Mise à jour profil artisan
        $profile->update($request->only(
            'specialty', 'category', 'bio', 'hourly_rate',
            'years_experience', 'typical_delivery_days', 'service_radius_km',
            'materials', 'languages',
        ));

        return back()->with('success', 'Profil mis à jour avec succès.');
    }

    public function toggleAvailability()
    {
        $profile = Auth::user()->artisanProfile;
        $profile->update(['is_available' => !$profile->is_available]);
        $status = $profile->is_available ? 'Disponible' : 'Occupé';
        return back()->with('success', "Statut mis à jour : {$status}");
    }
}
