<?php
namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    protected function prepareForValidation(): void
    {
        if ($this->filled('category')) {
            $this->merge(['category' => mb_strtolower($this->input('category'))]);
        }
    }

    public function rules(): array
    {
        $cities = array_merge(...array_values(config('benin_villes')));

        return [
            'name'                    => ['required', 'string', 'max:100'],
            'email'                   => ['required', 'email', 'unique:users,email'],
            'password'                => ['required', 'string', 'min:8', 'confirmed'],
            // Bug 7 — livreur ajouté
            'role'                    => ['required', 'in:artisan,client,livreur'],
            'phone'                   => ['nullable', 'string', 'max:20'],
            'city'                    => ['required', 'string', 'in:' . implode(',', $cities)],
            // Champs artisan/livreur
            'specialty'               => ['required_if:role,artisan', 'nullable', 'string', 'max:100'],
            'category'                => ['required_if:role,artisan', 'nullable', Rule::in(array_keys(config('artisanhub.categories', [])))],
            'available_for_delivery'  => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'          => 'Le nom est obligatoire.',
            'email.required'         => 'L\'email est obligatoire.',
            'email.unique'           => 'Cet email est déjà utilisé.',
            'password.min'           => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed'     => 'Les mots de passe ne correspondent pas.',
            'role.required'          => 'Veuillez choisir votre rôle.',
            'role.in'                => 'Rôle invalide.',
            'city.required'          => 'La ville est obligatoire.',
            'city.in'                => 'Veuillez choisir une ville du Bénin.',
            'specialty.required_if'  => 'La spécialité est obligatoire pour un artisan.',
            'category.required_if'   => 'La catégorie est obligatoire pour un artisan.',
        ];
    }
}
