<?php
namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:100'],
            'phone'       => ['nullable', 'string', 'max:20'],
            'city'        => ['required', 'string', Rule::in(config('artisanhub.cities', []))],
            'quartier'    => ['nullable', 'string', 'max:120'],
            'latitude'    => ['nullable', 'numeric', 'between:-90,90'],
            'longitude'   => ['nullable', 'numeric', 'between:-180,180'],
            'whatsapp_opt_in' => ['nullable', 'boolean'],
            'preferred_language' => ['nullable', 'string', 'in:'.implode(',', array_keys(\App\Models\User::availableLanguages()))],
            'avatar'      => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'specialty'   => ['required', 'string', 'max:100'],
            'category'    => ['required', 'string', 'in:'.implode(',', array_keys(config('artisanhub.categories', [])))],
            'bio'         => ['nullable', 'string', 'max:500'],
            'hourly_rate' => ['nullable', 'integer', 'min:0'],
            'years_experience'       => ['nullable', 'integer', 'min:0', 'max:80'],
            'typical_delivery_days'  => ['nullable', 'integer', 'min:0', 'max:365'],
            'service_radius_km'      => ['nullable', 'integer', 'min:0', 'max:1000'],
            'materials'   => ['nullable', 'string', 'max:255'],
            'languages'   => ['nullable', 'string', 'max:255'],
        ];
    }
}
