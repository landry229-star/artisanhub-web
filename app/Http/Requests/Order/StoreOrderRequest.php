<?php
namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ArtisanProfile;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $cities = array_merge(...array_values(config('benin_villes')));

        return [
            // Fix : sans le where('role','artisan'), un client pouvait passer
            // commande contre n'importe quel user_id (autre client, livreur,
            // voire admin) puisque 'exists:users,id' ne filtre pas le rôle.
            'artisan_id'      => ['required', Rule::exists('users', 'id')->where(fn ($q) => $q->where('role', 'artisan')->where('is_active', true))],
            'service_id'      => ['nullable', Rule::exists('services', 'id')->where(fn ($q) => $q
                ->where('is_active', true)
                ->whereIn('artisan_profile_id', ArtisanProfile::where('user_id', $this->input('artisan_id'))->select('id')))],
            'title'           => ['required', 'string', 'min:5', 'max:150'],
            'description'     => ['required', 'string', 'min:20', 'max:2000'],
            'budget'          => ['nullable', 'integer', 'min:1000'],
            'deadline'        => ['nullable', 'date', 'after:today'],
            'needs_delivery'  => ['nullable', 'boolean'],
            'delivery_city'   => [Rule::requiredIf(fn () => $this->boolean('needs_delivery')), 'nullable', 'string', 'in:' . implode(',', $cities)],
            'images'          => ['nullable', 'array', 'max:5'],
            'images.*'        => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.min'                  => 'Le titre doit contenir au moins 5 caractères.',
            'description.min'            => 'La description doit contenir au moins 20 caractères.',
            'budget.min'                 => 'Le budget minimum est 1 000 XOF.',
            'deadline.after'             => 'La date limite doit être dans le futur.',
            'delivery_city.required_if'  => 'Veuillez choisir la ville de livraison.',
            'delivery_city.in'           => 'Veuillez choisir une ville du Bénin.',
            'images.max'                => 'Vous pouvez joindre au maximum 5 images.',
            'images.*.image'            => 'Chaque fichier doit être une image.',
            'images.*.mimes'            => 'Les images doivent être au format JPG, PNG ou WEBP.',
            'images.*.max'              => 'Chaque image ne doit pas dépasser 5 Mo.',
        ];
    }
}
