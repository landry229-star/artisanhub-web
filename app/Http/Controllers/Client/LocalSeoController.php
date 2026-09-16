<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ArtisanProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LocalSeoController extends Controller
{
    /**
     * Page /categorie/{category} : tous les artisans d'un métier donné,
     * toutes villes confondues. Cible les recherches type "poterie Bénin".
     */
    public function category(Request $request, string $category)
    {
        $categories = config('artisanhub.categories');

        if (! array_key_exists($category, $categories)) {
            throw new NotFoundHttpException;
        }

        $query = ArtisanProfile::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true))
            ->where('category', $category);

        if ($request->filled('city')) {
            $query->whereHas('user', fn($q) => $q->where('city', $request->city));
        }

        $artisans = $query->orderByDesc('rating')->paginate(12)->withQueryString();

        return view('client.search.category', [
            'artisans'     => $artisans,
            'category'     => $category,
            'categoryData' => $categories[$category],
            'categories'   => $categories,
            'cities'       => config('artisanhub.cities'),
        ]);
    }

    /**
     * Page /ville/{citySlug} : tous les artisans d'une commune, toutes
     * spécialités confondues. Cible les recherches type "artisan Cotonou".
     */
    public function city(Request $request, string $citySlug)
    {
        $cityName = collect(config('artisanhub.cities'))
            ->first(fn($c) => Str::slug($c) === $citySlug);

        if (! $cityName) {
            throw new NotFoundHttpException;
        }

        $query = ArtisanProfile::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true)->where('city', $cityName));

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $artisans = $query->orderByDesc('rating')->paginate(12)->withQueryString();

        return view('client.search.city', [
            'artisans'   => $artisans,
            'city'       => $cityName,
            'citySlug'   => $citySlug,
            'categories' => config('artisanhub.categories'),
        ]);
    }
}
