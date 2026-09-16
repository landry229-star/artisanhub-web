@extends('layouts.app')
@section('title', $artisan->name.' — '.$artisan->artisanProfile->specialty.' à '.$artisan->city)
@section('meta_description', $artisan->name.', '.$artisan->artisanProfile->specialty.' à '.$artisan->city.'. '.\Illuminate\Support\Str::limit(strip_tags($artisan->artisanProfile->bio ?? ''), 120, '').' Devis gratuit sur ArtisanHub.')
@section('og_type', 'profile')
@section('og_image', $artisan->artisanProfile->portfolioItems->first()?->image_path ? asset('storage/'.$artisan->artisanProfile->portfolioItems->first()->image_path) : asset('images/hero-artisans.png'))

@push('head')
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": {!! json_encode($artisan->name) !!},
    "description": {!! json_encode(\Illuminate\Support\Str::limit(strip_tags($artisan->artisanProfile->bio ?? ''), 200, '')) !!},
    "image": {!! json_encode($artisan->avatarUrl()) !!},
    "url": {!! json_encode(route('artisans.show', $artisan->routeSlug())) !!},
    "address": {
        "@type": "PostalAddress",
        "addressLocality": {!! json_encode($artisan->city) !!},
        "addressCountry": "BJ"
    }
    @if($artisan->artisanProfile->hourly_rate)
    ,"priceRange": {!! json_encode(number_format($artisan->artisanProfile->hourly_rate, 0, ',', ' ').' XOF/h') !!}
    @endif
    @if($artisan->artisanProfile->reviews_count > 0)
    ,"aggregateRating": {
        "@type": "AggregateRating",
        "ratingValue": {{ $artisan->artisanProfile->rating }},
        "reviewCount": {{ $artisan->artisanProfile->reviews_count }},
        "bestRating": "5",
        "worstRating": "1"
    }
    @endif
}
</script>
<script type="application/ld+json">
{
    "@@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Accueil", "item": {!! json_encode(route('home')) !!}},
        {"@type": "ListItem", "position": 2, "name": "Artisans", "item": {!! json_encode(route('artisans.index')) !!}},
        {"@type": "ListItem", "position": 3, "name": {!! json_encode($artisan->name) !!}}
    ]
}
</script>
@endpush

@section('content')
<div class="container py-5">
    <div class="row g-4">
        <div class="col-lg-8">

            {{-- Header artisan --}}
            <div class="card p-4 mb-4">
                <div class="d-flex gap-4 align-items-start">
                    <img src="{{ $artisan->avatarUrl() }}" class="rounded-circle"
                         width="90" height="90" style="object-fit:cover;border:3px solid #C4622D;flex-shrink:0"
                         alt="Photo de profil de {{ $artisan->name }}, {{ $artisan->artisanProfile->specialty }} à {{ $artisan->city }}">
                    <div class="flex-grow-1">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                            <div>
                                <h1 style="font-family:'Playfair Display',serif;font-size:1.6rem" class="mb-1">{{ $artisan->name }}</h1>
                                <p class="text-muted mb-2">{{ $artisan->artisanProfile->specialty }}</p>
                                <p class="text-muted mb-0" style="font-size:.875rem">📍 {{ $artisan->city }}</p>
                            </div>

                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge px-3 py-2"
                                      style="background:{{ $artisan->artisanProfile->badge()==='Top Artisan' ? '#C4622D' : ($artisan->artisanProfile->badge()==='Vérifié' ? '#2E7D32' : '#D4A853') }};color:#fff">
                                    {{ $artisan->artisanProfile->badge() }}
                                </span>
                                <span class="badge px-3 py-2" style="background:{{ $artisan->artisanProfile->tierColor() }};color:#fff"
                                      title="Palier de confiance : basé sur les commandes terminées, la note moyenne et le taux de litige">
                                    <i class="bi bi-award me-1"></i>{{ $artisan->artisanProfile->tierLabel() }}
                                </span>
                            </div>
                            @if($artisan->artisanProfile->canOfferGuarantee())
                                <p class="mb-0 mt-1 text-muted" style="font-size:.78rem">
                                    <i class="bi bi-shield-check text-clay me-1"></i>Commandes couvertes par la garantie "satisfait ou repris"
                                </p>
                            @endif
                        </div>
                        @if($artisan->artisanProfile->rating > 0)
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <span style="color:#D4A853">
                                    @for($i=1;$i<=5;$i++)<i class="bi bi-star{{ $i<=round($artisan->artisanProfile->rating)?'-fill':'' }}"></i>@endfor
                                </span>
                                <strong>{{ $artisan->artisanProfile->rating }}</strong>
                                <span class="text-muted">({{ $artisan->artisanProfile->reviews_count }} avis)</span>
                            </div>
                        @endif
                        @php $responseStats = $artisan->responseStats(); @endphp
                        <p class="text-muted mt-1 mb-0" style="font-size:.8rem">
                            <i class="bi bi-calendar3 me-1"></i>Sur ArtisanHub depuis {{ $artisan->created_at->locale('fr')->translatedFormat('F Y') }}
                            @if($responseStats['threads'] >= 3 && $responseStats['rate'] !== null)
                                <span class="ms-2"><i class="bi bi-chat-dots me-1"></i>Répond à {{ $responseStats['rate'] }}% des messages
                                @if($responseStats['avg_hours'] !== null)
                                    , en {{ $responseStats['avg_hours'] < 1 ? 'moins d\'1h' : $responseStats['avg_hours'].'h' }} en moyenne
                                @endif
                                </span>
                            @endif
                        </p>
                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <span class="badge" style="background:#F5EFE6;color:#C4622D">
                                {{ config('artisanhub.categories.'.$artisan->artisanProfile->category.'.icon','') }}
                                {{ config('artisanhub.categories.'.$artisan->artisanProfile->category.'.label', $artisan->artisanProfile->category) }}
                            </span>
                            @if($artisan->artisanProfile->hourly_rate)
                                <span class="badge" style="background:#F5EFE6;color:#5C3D1E">
                                    ~{{ number_format($artisan->artisanProfile->hourly_rate,0,',',' ') }} XOF/h
                                </span>
                            @endif
                            <span class="badge" style="background:{{ $artisan->artisanProfile->is_available ? '#D4EDDA' : '#F8D7DA' }};color:{{ $artisan->artisanProfile->is_available ? '#155724' : '#721C24' }}">
                                {{ $artisan->artisanProfile->is_available ? '● Disponible' : '● Occupé' }}
                            </span>
                        </div>
                    </div>
                </div>
                @if($artisan->artisanProfile->bio)
                    <p class="mt-3 mb-0 text-muted">{{ $artisan->artisanProfile->bio }}</p>
                @endif
            </div>

            {{-- Infos pratiques --}}
            @php
                $profile = $artisan->artisanProfile;
                $hasPracticalInfo = $profile->years_experience || $profile->typical_delivery_days
                    || $profile->service_radius_km || $profile->materials || $profile->languages;
            @endphp
            @if($hasPracticalInfo)
            <div class="card p-4 mb-4">
                <h5 class="fw-700 mb-3">Infos pratiques</h5>
                <div class="row g-3">
                    @if($profile->years_experience)
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.75rem">EXPÉRIENCE</div>
                            <div class="fw-700">{{ $profile->years_experience }} an{{ $profile->years_experience > 1 ? 's' : '' }}</div>
                        </div>
                    @endif
                    @if($profile->typical_delivery_days)
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.75rem">DÉLAI HABITUEL</div>
                            <div class="fw-700">{{ $profile->typical_delivery_days }} jour{{ $profile->typical_delivery_days > 1 ? 's' : '' }}</div>
                        </div>
                    @endif
                    @if($profile->service_radius_km)
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.75rem">SE DÉPLACE JUSQU'À</div>
                            <div class="fw-700">{{ $profile->service_radius_km }} km</div>
                        </div>
                    @endif
                    @if($profile->languages)
                        <div class="col-6 col-md-3">
                            <div class="text-muted" style="font-size:.75rem">LANGUES</div>
                            <div class="fw-700">{{ $profile->languages }}</div>
                        </div>
                    @endif
                    @if($profile->materials)
                        <div class="col-12">
                            <div class="text-muted" style="font-size:.75rem">MATÉRIAUX / TECHNIQUES</div>
                            <div class="fw-700">{{ $profile->materials }}</div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- ★ CATALOGUE DE SERVICES --}}
            @php $services = $artisan->artisanProfile->activeServices; @endphp
            @if($services->isNotEmpty())
            <div class="card p-4 mb-4">
                <h5 class="fw-700 mb-1">Services proposés</h5>
                <p class="text-muted mb-3" style="font-size:.85rem">Cliquez sur un service pour commander directement</p>
                <div class="row g-3">
                    @foreach($services as $service)
                    <div class="col-md-6">
                        <div class="border rounded-3 overflow-hidden h-100 d-flex flex-column service-card"
                             style="border-color:#e0d5c5!important;cursor:pointer;transition:box-shadow .2s"
                             onclick="window.location='{{ route('client.orders.create', [$artisan->id, 'service' => $service->id]) }}'">
                            @if($service->image_path)
                                <img src="{{ asset('storage/'.$service->image_path) }}"
                                     style="height:130px;object-fit:cover;width:100%" loading="lazy" alt="{{ $service->title }}">
                            @else
                                <div class="d-flex align-items-center justify-content-center"
                                     style="height:80px;background:#f5efe6">
                                    <i class="bi bi-grid-3x3-gap" style="font-size:1.8rem;color:#c4622d;opacity:.5"></i>
                                </div>
                            @endif
                            <div class="p-3 flex-grow-1 d-flex flex-column">
                                <h6 class="fw-700 mb-1" style="font-size:.9rem">{{ $service->title }}</h6>
                                <p class="text-muted mb-2 flex-grow-1" style="font-size:.8rem;line-height:1.4">
                                    {{ Str::limit($service->description, 90) }}
                                </p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-700" style="color:#C4622D;font-size:1rem">{{ $service->formattedPrice() }}</span>
                                    <span class="text-muted" style="font-size:.78rem">
                                        <i class="bi bi-clock me-1"></i>{{ $service->delayLabel() }}
                                    </span>
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('client.orders.create', [$artisan->id, 'service' => $service->id]) }}"
                                       class="btn btn-clay btn-sm w-100 py-1" style="font-size:.8rem">
                                        <i class="bi bi-cart-plus me-1"></i>Commander ce service
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Portfolio --}}
            @if($artisan->artisanProfile->portfolioItems->isNotEmpty())
                <div class="card p-4 mb-4">
                    <h5 class="fw-700 mb-3">Portfolio ({{ $artisan->artisanProfile->portfolioItems->count() }} réalisations)</h5>
                    <div class="row g-2">
                        @foreach($artisan->artisanProfile->portfolioItems as $item)
                            <div class="col-4 col-md-3">
                                <div style="aspect-ratio:1;overflow:hidden;border-radius:8px;cursor:pointer"
                                     data-bs-toggle="modal" data-bs-target="#imgModal{{ $item->id }}">
                                    <img src="{{ asset('storage/'.$item->image_path) }}"
                                         class="w-100 h-100" style="object-fit:cover" loading="lazy"
                                         alt="{{ $item->title ?: 'Réalisation de '.$artisan->name }}">
                                </div>
                            </div>
                            <div class="modal fade" id="imgModal{{ $item->id }}" tabindex="-1">
                                <div class="modal-dialog modal-dialog-centered modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header border-0 pb-0">
                                            <h6 class="modal-title fw-700">{{ $item->title }}</h6>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body pt-2">
                                            <img src="{{ asset('storage/'.$item->image_path) }}"
                                                 class="w-100 rounded" style="max-height:70vh;object-fit:contain"
                                                 alt="{{ $item->title ?: 'Réalisation de '.$artisan->name }}">
                                            @if($item->description)
                                                <p class="text-muted mt-2 mb-0" style="font-size:.875rem">{{ $item->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Avis --}}
            @if($artisan->reviewsReceived->isNotEmpty())
                <div class="card p-4">
                    <h5 class="fw-700 mb-3">Avis clients ({{ $artisan->reviewsReceived->count() }})</h5>
                    @foreach($artisan->reviewsReceived->take(5) as $review)
                        <div class="py-3 border-bottom" style="border-color:#F5EFE6!important">
                            <div class="d-flex gap-2 align-items-start mb-1">
                                <img src="{{ $review->client->avatarUrl() }}" class="rounded-circle"
                                     width="36" height="36" style="object-fit:cover" loading="lazy"
                                     alt="{{ $review->client->name }}">
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <span class="fw-600" style="font-size:.9rem">{{ $review->client->name }}</span>
                                        <span class="text-muted" style="font-size:.78rem">{{ $review->created_at->format('d/m/Y') }}</span>
                                    </div>
                                    <span style="color:#D4A853;font-size:.85rem">
                                        @for($i=1;$i<=5;$i++)<i class="bi bi-star{{ $i<=$review->rating?'-fill':'' }}"></i>@endfor
                                    </span>
                                </div>
                            </div>
                            @if($review->comment)
                                <p class="mb-1 ms-5" style="font-size:.875rem">{{ $review->comment }}</p>
                            @endif
                            @if($review->artisan_reply)
                                <div class="ms-5 p-2 rounded" style="background:#F5EFE6;font-size:.82rem">
                                    <strong style="color:#C4622D">Réponse de l'artisan :</strong>
                                    {{ $review->artisan_reply }}
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="col-lg-4">
            <div class="card p-4 position-sticky" style="top:80px">
                @auth
                    @if(auth()->user()->isClient())
                        @if($artisan->artisanProfile->is_available)
                            <p class="section-label mb-2" style="color:#C4622D;font-size:.75rem;font-weight:700;letter-spacing:.1em;text-transform:uppercase">COMMANDER</p>
                            <h5 class="fw-700 mb-3">Confier un travail à {{ $artisan->name }}</h5>
                            @if($services->isNotEmpty())
                                <p class="text-muted mb-3" style="font-size:.83rem">
                                    <i class="bi bi-grid-3x3-gap me-1" style="color:#C4622D"></i>
                                    {{ $services->count() }} service(s) disponible(s) — choisissez ci-dessous ou envoyez une demande libre.
                                </p>
                            @endif
                            <a href="{{ route('client.orders.create', $artisan->id) }}"
                               class="btn btn-clay w-100 py-2 mb-3">
                                <i class="bi bi-send me-2"></i>Demande libre
                            </a>
                        @else
                            <div class="p-3 rounded-3 text-center" style="background:#F8D7DA">
                                <i class="bi bi-clock" style="color:#721C24;font-size:1.5rem"></i>
                                <p class="mb-0 mt-2 fw-600" style="color:#721C24">Artisan actuellement occupé</p>
                                <p class="mb-0" style="font-size:.82rem;color:#721C24">Revenez plus tard</p>
                            </div>
                        @endif
                    @endif
                @else
                    <p class="fw-600 mb-3">Connectez-vous pour commander</p>
                    <a href="{{ route('login') }}" class="btn btn-clay w-100 mb-2">Se connecter</a>
                    <a href="{{ route('register') }}" class="btn btn-outline-clay w-100">S'inscrire</a>
                @endauth

                <hr style="border-color:#ECD8C6">
                <div style="font-size:.82rem;color:#9A8070">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Commandes terminées</span>
                        <strong style="color:#2C1A0E">{{ $artisan->artisanProfile->reviews_count }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Note moyenne</span>
                        <strong style="color:#2C1A0E">{{ $artisan->artisanProfile->rating > 0 ? $artisan->artisanProfile->rating.'/5' : 'N/A' }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Membre depuis</span>
                        <strong style="color:#2C1A0E">{{ $artisan->created_at->format('M Y') }}</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.service-card:hover { box-shadow: 0 4px 16px rgba(196,98,45,.15)!important; }
</style>
@endsection
