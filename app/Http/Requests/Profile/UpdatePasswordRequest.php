<?php

namespace App\Http\Requests\Profile;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UpdatePasswordRequest extends FormRequest
{
    /**
     * Bag d'erreurs dédié : évite que les erreurs de ce formulaire
     * s'affichent sur le formulaire "infos personnelles" de la même page.
     */
    protected $errorBag = 'updatePassword';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // La règle "current_password" vérifie le hash du mot de passe
            // de l'utilisateur actuellement authentifié (guard "web").
            'current_password' => ['required', 'current_password'],
            'password'          => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required'         => 'Veuillez saisir votre mot de passe actuel.',
            'current_password.current_password' => "Le mot de passe actuel saisi n'est pas correct.",
            'password.required'                 => 'Veuillez saisir un nouveau mot de passe.',
            'password.confirmed'                => 'La confirmation ne correspond pas au nouveau mot de passe.',
            'password.min'                      => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
        ];
    }
}
