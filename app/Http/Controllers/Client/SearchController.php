<?php
namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ArtisanProfile;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = ArtisanProfile::with('user')
            ->whereHas('user', fn($q) => $q->where('is_active', true));

        // Mot-clé
        if ($request->filled('q')) {
            $kw = $request->q;
            $query->where(function ($q) use ($kw) {
                $q->where('specialty', 'like', "%{$kw}%")
                  ->orWhere('bio', 'like', "%{$kw}%")
                  ->orWhereHas('user', fn($u) => $u->where('name', 'like', "%{$kw}%"));
            });
        }

        // Ville
        if ($request->filled('city')) {
            $query->whereHas('user', fn($q) => $q->where('city', $request->city));
        }

        // Quartier (recherche approximative, complète le filtre ville)
        if ($request->filled('quartier')) {
            $query->whereHas('user', fn($q) => $q->where('quartier', 'like', "%{$request->quartier}%"));
        }

        // "Autour de moi" : distance en km calculée depuis la position du
        // navigateur (Haversine). N'affecte que les artisans ayant renseigné
        // leurs coordonnées ; les autres restent visibles mais non triés par distance.
        $lat = $request->filled('lat') ? (float) $request->lat : null;
        $lng = $request->filled('lng') ? (float) $request->lng : null;
        $supportsHaversine = in_array(\Illuminate\Support\Facades\DB::connection()->getDriverName(), ['mysql', 'pgsql']);
        if ($lat !== null && $lng !== null && $supportsHaversine) {
            $haversine = "(6371 * acos(cos(radians({$lat})) * cos(radians(users.latitude))
                * cos(radians(users.longitude) - radians({$lng})) + sin(radians({$lat}))
                * sin(radians(users.latitude))))";
            $query->addSelect([
                'distance_km' => \App\Models\User::selectRaw($haversine)
                    ->whereColumn('users.id', 'artisan_profiles.user_id'),
            ]);
        }

        // Catégorie
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Disponible
        if ($request->boolean('available')) {
            $query->where('is_available', true);
        }

        // Prix
        if ($request->filled('price_min')) {
            $query->where('hourly_rate', '>=', (int)$request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('hourly_rate', '<=', (int)$request->price_max);
        }

        // Note minimale
        if ($request->filled('min_rating')) {
            $query->where('rating', '>=', (float)$request->min_rating);
        }

        // Tri
        $canSortByDistance = $lat !== null && $supportsHaversine;
        match ($request->get('sort', $canSortByDistance ? 'distance' : 'rating')) {
            'reviews'    => $query->orderByDesc('reviews_count'),
            'price_asc'  => $query->orderBy('hourly_rate'),
            'price_desc' => $query->orderByDesc('hourly_rate'),
            'newest'     => $query->latest(),
            'distance'   => $canSortByDistance ? $query->orderBy('distance_km') : $query->orderByDesc('rating'),
            default      => $query->orderByDesc('rating'),
        };

        $artisans      = $query->paginate(12)->withQueryString();
        $cities        = array_unique(config('artisanhub.cities'));
        $categories    = config('artisanhub.categories');
        $totalArtisans = ArtisanProfile::whereHas('user', fn($q) => $q->where('is_active', true))->count();

        return view('client.search.index', compact('artisans','cities','categories','totalArtisans'));
    }

    public function show(string $idSlug)
    {
        // L'URL est {id}-{slug-optionnel} : l'ID (chiffres en tête) fait
        // foi, le reste n'est que décoratif pour le SEO.
        $id = (int) strtok($idSlug, '-');

        $artisan = User::where('role', 'artisan')
            ->where('is_active', true)
            ->with([
                'artisanProfile.portfolioItems',
                'artisanProfile.services' => fn($q) => $q->where('is_active', true),
                'reviewsReceived.client',
            ])
            ->findOrFail($id);

        // Redirection 301 si l'URL visitée n'est pas la forme canonique
        // (ancien lien avec ID seul, ou slug périmé après un changement de
        // nom/ville) — préserve le référencement et évite le contenu dupliqué.
        if ($idSlug !== $artisan->routeSlug()) {
            return redirect()->route('artisans.show', $artisan->routeSlug(), 301);
        }

        return view('client.search.show', compact('artisan'));
    }
}
