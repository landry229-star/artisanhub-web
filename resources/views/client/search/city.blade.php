@extends('layouts.app')
@section('title', 'Artisans à '.$city.' — ArtisanHub')
@section('meta_description', "Trouvez un artisan qualifié à ".$city.", Bénin : poterie, menuiserie, couture, bijouterie et plus. Devis gratuit, garantie satisfait ou repris.")

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Accueil", "item": {!! json_encode(route('home')) !!}},
        {"@type": "ListItem", "position": 2, "name": "Artisans", "item": {!! json_encode(route('artisans.index')) !!}},
        {"@type": "ListItem", "position": 3, "name": {!! json_encode($city) !!}}
    ]
}
</script>
@endpush

@section('content')
<div style="background:linear-gradient(135deg,#2C1A0E 0%,#5C3D1E 60%,#9E4A1E 100%);padding:48px 0 40px">
    <div class="container">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider-color:#D4A853">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" style="color:#D4A853">Accueil</a></li>
                <li class="breadcrumb-item"><a href="{{ route('artisans.index') }}" style="color:#D4A853">Artisans</a></li>
                <li class="breadcrumb-item active" style="color:#fff" aria-current="page">{{ $city }}</li>
            </ol>
        </nav>
        <p style="color:#D4A853;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase;text-align:center" class="mb-2">
            📍 VILLE
        </p>
        <h1 style="font-family:'Playfair Display',serif;color:#fff;font-size:2rem;text-align:center" class="mb-2">
            Artisans à {{ $city }}
        </h1>
        <p style="color:#C8B5A0;text-align:center;max-width:560px;margin:0 auto" class="mb-4">
            {{ $artisans->total() }} artisan{{ $artisans->total() > 1 ? 's' : '' }} vérifié{{ $artisans->total() > 1 ? 's' : '' }} à {{ $city }}, prêts à recevoir votre demande de devis.
        </p>

        <form method="GET" class="d-flex justify-content-center">
            <select name="category" class="form-select w-auto" onchange="this.form.submit()">
                <option value="">🔧 Tous les métiers</option>
                @foreach($categories as $key => $cat)
                    <option value="{{ $key }}" @selected(request('category') === $key)>{{ $cat['icon'] }} {{ $cat['label'] }}</option>
                @endforeach
            </select>
        </form>
    </div>
</div>

<div class="container py-5">
    @if($artisans->isEmpty())
        <div class="text-center py-5">
            <p style="font-size:2rem">📍</p>
            <h2 class="fw-700" style="font-size:1.2rem">Aucun artisan à {{ $city }} pour l'instant{{ request('category') ? ' dans ce métier' : '' }}</h2>
            <p class="text-muted">Essayez un autre métier, ou revenez bientôt — de nouveaux artisans s'inscrivent chaque semaine.</p>
            <a href="{{ route('city.show', $citySlug) }}" class="btn btn-clay mt-2">Voir tous les métiers</a>
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

    {{-- Maillage interne : métiers disponibles à cette ville --}}
    <div class="mt-5 pt-4 border-top">
        <h2 class="fw-700 mb-3" style="font-size:1.1rem">Parcourir par métier</h2>
        <div class="d-flex flex-wrap gap-2">
            @foreach($categories as $key => $cat)
                <a href="{{ route('category.show', $key) }}?city={{ urlencode($city) }}" class="btn btn-outline-secondary btn-sm">
                    {{ $cat['icon'] }} {{ $cat['label'] }}
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection
