@extends('layouts.app')
@section('title', 'Explorer les artisans')
@section('meta_description', "Parcourez les artisans vérifiés du Bénin par ville, quartier ou spécialité : poterie, menuiserie, couture, bijouterie et plus. Filtrez par disponibilité et distance.")

@section('content')

{{-- Hero recherche --}}
<div style="background:linear-gradient(135deg,#2C1A0E 0%,#5C3D1E 60%,#9E4A1E 100%);padding:48px 0 40px">
    <div class="container">
        <p style="color:#D4A853;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;text-align:center" class="mb-2">EXPLORER</p>
        <h1 style="font-family:'Playfair Display',serif;color:#fff;font-size:2rem;text-align:center" class="mb-4">
            Trouvez l'artisan qu'il vous faut
        </h1>

        <form method="GET" action="{{ route('artisans.index') }}" id="search-form">
            {{-- Barre principale --}}
            <div class="row g-2 justify-content-center mb-3">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input type="text" name="q" class="form-control border-start-0"
                               value="{{ request('q') }}"
                               placeholder="Menuisier, couturière, forgeron...">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="city" class="form-select">
                        <option value="">📍 Toutes les villes</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city')===$city?'selected':'' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="category" class="form-select">
                        <option value="">🔧 Catégorie</option>
                        @foreach($categories as $key => $cat)
                            @if($key !== 'livraison')
                                <option value="{{ $key }}" {{ request('category')===$key?'selected':'' }}>
                                    {{ $cat['icon'] }} {{ $cat['label'] }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-clay w-100">
                        <i class="bi bi-search me-1"></i>Rechercher
                    </button>
                </div>
            </div>

            {{-- Géolocalisation : "Autour de moi" --}}
            <div class="text-center mb-2">
                <button type="button" class="btn btn-sm btn-outline-light" onclick="searchNearMe()">
                    <i class="bi bi-geo-alt me-1"></i>Autour de moi
                    @if(request('lat'))
                        <span class="badge ms-1" style="background:#C4622D">activé</span>
                    @endif
                </button>
                <input type="hidden" name="lat" id="input-lat" value="{{ request('lat') }}">
                <input type="hidden" name="lng" id="input-lng" value="{{ request('lng') }}">
            </div>

            {{-- Filtres avancés --}}
            <div class="text-center mb-2">
                <button type="button" class="btn btn-sm btn-outline-light"
                        onclick="toggleFilters()">
                    <i class="bi bi-sliders me-1"></i>Filtres avancés
                    @if(request('price_min') || request('price_max') || request('min_rating') || request('available'))
                        <span class="badge ms-1" style="background:#C4622D">actifs</span>
                    @endif
                </button>
            </div>

            <div id="advanced-filters"
                 style="display:{{ (request('price_min')||request('price_max')||request('min_rating')||request('available')) ? 'block' : 'none' }}">
                <div class="row g-2 justify-content-center">
                    <div class="col-md-2">
                        <input type="number" name="price_min" class="form-control"
                               value="{{ request('price_min') }}"
                               placeholder="Prix min (XOF)">
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="price_max" class="form-control"
                               value="{{ request('price_max') }}"
                               placeholder="Prix max (XOF)">
                    </div>
                    <div class="col-md-2">
                        <select name="min_rating" class="form-select">
                            <option value="">⭐ Note min.</option>
                            @foreach([4=>'4+ étoiles',3=>'3+ étoiles',2=>'2+ étoiles'] as $v=>$l)
                                <option value="{{ $v }}" {{ request('min_rating')==$v?'selected':'' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="text" name="quartier" class="form-control"
                               value="{{ request('quartier') }}"
                               placeholder="🏘️ Quartier">
                    </div>
                    <div class="col-md-2">
                        <select name="sort" class="form-select">
                            <option value="rating"    {{ request('sort','rating')==='rating'   ?'selected':'' }}>⭐ Mieux notés</option>
                            <option value="reviews"   {{ request('sort')==='reviews'           ?'selected':'' }}>💬 Plus d'avis</option>
                            <option value="price_asc" {{ request('sort')==='price_asc'         ?'selected':'' }}>💰 Prix croissant</option>
                            <option value="price_desc"{{ request('sort')==='price_desc'        ?'selected':'' }}>💰 Prix décroissant</option>
                            <option value="newest"    {{ request('sort')==='newest'            ?'selected':'' }}>🆕 Récents</option>
                            @if(request('lat'))
                                <option value="distance" {{ request('sort')==='distance' ?'selected':'' }}>📍 Plus proches</option>
                            @endif
                        </select>
                    </div>
                    <div class="col-md-2">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" name="available"
                                   value="1" id="available" {{ request('available')?'checked':'' }}
                                   onchange="this.form.submit()">
                            <label class="form-check-label text-white" for="available">
                                Disponible
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="container py-5">

    {{-- Stats + reset --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <strong class="text-clay">{{ $artisans->total() }}</strong>
            <span class="text-muted"> artisan(s) trouvé(s)</span>
            @if($artisans->total() < $totalArtisans)
                <span class="text-muted"> sur {{ $totalArtisans }}</span>
            @endif
            @if(request('q'))
                <span class="ms-2 badge" style="background:#F5EFE6;color:#C4622D">
                    "{{ request('q') }}"
                </span>
            @endif
        </div>
        @if(request()->hasAny(['q','city','category','available','price_min','price_max','min_rating','sort']))
            <a href="{{ route('artisans.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-x-circle me-1"></i>Réinitialiser les filtres
            </a>
        @endif
    </div>

    @if($artisans->isEmpty())
        <div class="text-center py-5">
            <i class="bi bi-search" style="font-size:3rem;opacity:.2;display:block;margin-bottom:1rem"></i>
            <h5 class="fw-700">Aucun artisan trouvé</h5>
            <p class="text-muted">Essayez d'autres mots-clés ou élargissez vos filtres.</p>
            <a href="{{ route('artisans.index') }}" class="btn btn-clay mt-2">
                Voir tous les artisans
            </a>
        </div>
    @else
        <div class="row g-4">
            @foreach($artisans as $profile)
                @include('client.search._artisan_card', ['profile' => $profile])
            @endforeach
        </div>

        <div class="mt-4 d-flex justify-content-center">
            {{ $artisans->links() }}
        </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function toggleFilters() {
    const f = document.getElementById('advanced-filters');
    f.style.display = f.style.display === 'none' ? 'block' : 'none';
}

function searchNearMe() {
    if (!navigator.geolocation) {
        alert("La géolocalisation n'est pas disponible sur ce navigateur.");
        return;
    }
    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById('input-lat').value = pos.coords.latitude;
            document.getElementById('input-lng').value = pos.coords.longitude;
            document.getElementById('search-form').submit();
        },
        () => alert("Impossible de récupérer votre position. Vérifiez les autorisations de localisation."),
        { enableHighAccuracy: true, timeout: 8000 }
    );
}
</script>
@endpush
