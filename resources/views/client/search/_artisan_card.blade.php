@php $artisan = $profile->user @endphp
<div class="col-md-6 col-lg-4">
    <div class="card h-100 p-3">
        <div class="d-flex gap-3 align-items-start mb-3">
            <img src="{{ $artisan->avatarUrl() }}" class="rounded-circle flex-shrink-0"
                 width="56" height="56" loading="lazy"
                 style="object-fit:cover;border:2px solid #C4622D"
                 alt="{{ $artisan->name }}, {{ $profile->specialty }}">
            <div class="flex-grow-1 min-w-0">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="fw-700">{{ $artisan->name }}</div>
                        <div class="text-muted" style="font-size:.82rem">
                            {{ $profile->specialty }}
                        </div>
                    </div>
                    <div class="d-flex flex-column gap-1 align-items-end flex-shrink-0">
                        <span class="badge ms-1 px-2 py-1"
                              style="background:{{ $profile->badge()==='Top Artisan'?'#C4622D':($profile->badge()==='Vérifié'?'#2E7D32':'#D4A853') }};
                                     color:#fff;font-size:.7rem">
                            {{ $profile->badge() }}
                        </span>
                        @if($profile->tier !== \App\Models\ArtisanProfile::TIER_DEBUTANT)
                            <span class="badge ms-1 px-2 py-1" style="background:{{ $profile->tierColor() }};color:#fff;font-size:.65rem">
                                <i class="bi bi-award me-1"></i>{{ $profile->tierLabel() }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        @if($profile->bio)
            <p class="text-muted mb-3" style="font-size:.85rem;line-height:1.5">
                {{ Str::limit($profile->bio, 80) }}
            </p>
        @endif

        @if($profile->years_experience)
            <p class="text-muted mb-2" style="font-size:.78rem">
                <i class="bi bi-briefcase me-1"></i>{{ $profile->years_experience }} an{{ $profile->years_experience > 1 ? 's' : '' }} d'expérience
            </p>
        @endif

        <div class="mt-auto">
            <div class="d-flex justify-content-between align-items-center mb-2">
                @if($profile->rating > 0)
                    <div>
                        <span style="color:#D4A853;font-size:.85rem">
                            @for($i=1;$i<=5;$i++)
                                <i class="bi bi-star{{ $i<=round($profile->rating)?'-fill':'' }}"></i>
                            @endfor
                        </span>
                        <span class="text-muted ms-1" style="font-size:.8rem">
                            {{ $profile->rating }} ({{ $profile->reviews_count }})
                        </span>
                    </div>
                @else
                    <span class="text-muted" style="font-size:.8rem">Nouveau</span>
                @endif
                @if($profile->hourly_rate)
                    <span class="text-clay fw-700" style="font-size:.85rem">
                        {{ number_format($profile->hourly_rate,0,',',' ') }} XOF/h
                    </span>
                @endif
            </div>
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted" style="font-size:.78rem">
                        📍 {{ $artisan->city }}
                    @if(isset($profile->distance_km) && $profile->distance_km !== null)
                        <span class="ms-1" style="color:#C4622D">· {{ number_format($profile->distance_km, 1) }} km</span>
                    @endif
                    </span>
                    @if($profile->is_available)
                        <span class="ms-2" style="background:#D4EDDA;color:#155724;
                              border-radius:100px;padding:1px 8px;font-size:.72rem;font-weight:600">
                            ● Disponible
                        </span>
                    @endif
                </div>
                <a href="{{ route('artisans.show', $artisan->routeSlug()) }}"
                   class="btn btn-clay btn-sm">Voir</a>
            </div>
        </div>
    </div>
</div>
