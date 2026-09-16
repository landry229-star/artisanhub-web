@extends('layouts.dashboard')
@section('title', 'Modifier mon profil')
@section('page-title', 'Mon profil')

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-8">
        <div class="content-card">
            <h5 class="fw-700 mb-4">Informations personnelles</h5>

            {{-- id="form-profile" cible artisanhub.js --}}
            <form action="{{ route('artisan.profile.update') }}" method="POST"
                  enctype="multipart/form-data" id="form-profile">
                @csrf @method('PUT')

                {{-- Avatar --}}
                <div class="mb-4 d-flex align-items-center gap-4">
                    <img src="{{ $user->avatarUrl() }}" class="rounded-circle"
                         id="avatar-preview"
                         width="80" height="80"
                         style="object-fit:cover;border:3px solid var(--clay)" alt="{{ $user->name }}">
                    <div>
                        <label class="btn btn-outline-clay btn-sm">
                            <i class="bi bi-camera me-1"></i>Changer la photo
                            <input type="file" name="avatar" class="d-none"
                                   accept="image/jpeg,image/png,image/webp"
                                   onchange="previewAvatar(this)">
                        </label>
                        <p class="text-muted mb-0 mt-1" style="font-size:.78rem">
                            JPG, PNG, WebP · max 2 Mo
                        </p>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            Nom complet <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $user->name) }}" required>
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Téléphone</label>
                        <input type="tel" name="phone" class="form-control"
                               value="{{ old('phone', $user->phone) }}"
                               placeholder="+229 97 XX XX XX">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            Ville <span class="text-danger">*</span>
                        </label>
                        <select name="city"
                                class="form-select @error('city') is-invalid @enderror" required>
                            <option value="">— Choisir une ville —</option>
                            @foreach(config('artisanhub.cities_by_dept') as $dept => $villes)
                                <optgroup label="{{ $dept }}">
                                    @foreach($villes as $ville)
                                        <option value="{{ $ville }}"
                                                {{ old('city', $user->city) === $ville ? 'selected' : '' }}>
                                            {{ $ville }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Quartier</label>
                        <div class="input-group">
                            <input type="text" name="quartier" class="form-control"
                                   value="{{ old('quartier', $user->quartier) }}"
                                   placeholder="Ex: Fidjrossè, Cadjèhoun...">
                            <button type="button" class="btn btn-outline-clay" onclick="captureLocation()"
                                    title="Utiliser ma position actuelle">
                                <i class="bi bi-geo-alt"></i>
                            </button>
                        </div>
                        <input type="hidden" name="latitude" id="input-latitude" value="{{ old('latitude', $user->latitude) }}">
                        <input type="hidden" name="longitude" id="input-longitude" value="{{ old('longitude', $user->longitude) }}">
                        <p class="text-muted mb-0 mt-1" style="font-size:.78rem" id="geoloc-status">
                            @if($user->latitude)
                                <i class="bi bi-check-circle text-success"></i> Position enregistrée — améliore votre visibilité dans "Autour de moi"
                            @else
                                Aide les clients à vous trouver via "Autour de moi"
                            @endif
                        </p>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Langue préférée (messagerie)</label>
                        <select name="preferred_language" class="form-select @error('preferred_language') is-invalid @enderror">
                            @foreach(\App\Models\User::availableLanguages() as $code => $label)
                                <option value="{{ $code }}" {{ old('preferred_language', $user->preferred_language) === $code ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('preferred_language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        <div class="form-text">Les messages reçus dans une autre langue seront traduits automatiquement.</div>
                    </div>

                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="whatsapp_opt_in" value="1"
                                   id="whatsapp_opt_in" {{ old('whatsapp_opt_in', $user->whatsapp_opt_in) ? 'checked' : '' }}>
                            <label class="form-check-label" for="whatsapp_opt_in">
                                <i class="bi bi-whatsapp text-success me-1"></i>
                                Recevoir les alertes importantes (nouvelle commande, litige...) par WhatsApp/SMS en plus de l'email
                            </label>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Tarif horaire (XOF)</label>
                        <input type="number" name="hourly_rate" class="form-control"
                               value="{{ old('hourly_rate', $profile->hourly_rate) }}"
                               placeholder="Ex: 5000" min="0">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            Spécialité <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="specialty"
                               class="form-control @error('specialty') is-invalid @enderror"
                               value="{{ old('specialty', $profile->specialty) }}"
                               placeholder="Ex: Menuisier ébéniste" required>
                        @error('specialty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">
                            Catégorie <span class="text-danger">*</span>
                        </label>
                        <select name="category"
                                class="form-select @error('category') is-invalid @enderror" required>
                            @foreach(config('artisanhub.categories') as $key => $cat)
                                @if($key !== 'livraison')
                                    <option value="{{ $key }}"
                                            {{ old('category', $profile->category) === $key ? 'selected' : '' }}>
                                        {{ $cat['icon'] }} {{ $cat['label'] }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                        @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-600">Biographie</label>
                        <textarea name="bio" class="form-control" rows="4"
                                  placeholder="Présentez-vous, votre expérience, vos spécialités...">{{ old('bio', $profile->bio) }}</textarea>
                        {{-- charCounter() sera appliqué par artisanhub.js --}}
                    </div>

                    <div class="col-12"><hr class="my-2"></div>
                    <p class="text-muted mb-0" style="font-size:.85rem">
                        Ces informations aident les clients à mieux évaluer votre profil avant de commander.
                    </p>

                    <div class="col-md-4">
                        <label class="form-label fw-600">Années d'expérience</label>
                        <input type="number" name="years_experience" class="form-control"
                               value="{{ old('years_experience', $profile->years_experience) }}"
                               placeholder="Ex: 8" min="0" max="80">
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-600">Délai habituel (jours)</label>
                        <input type="number" name="typical_delivery_days" class="form-control"
                               value="{{ old('typical_delivery_days', $profile->typical_delivery_days) }}"
                               placeholder="Ex: 5" min="0" max="365">
                        <div class="form-text">Pour une commande type</div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-600">Rayon de déplacement (km)</label>
                        <input type="number" name="service_radius_km" class="form-control"
                               value="{{ old('service_radius_km', $profile->service_radius_km) }}"
                               placeholder="Ex: 15" min="0" max="1000">
                        <div class="form-text">Si vous intervenez chez le client</div>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Matériaux / techniques</label>
                        <input type="text" name="materials" class="form-control"
                               value="{{ old('materials', $profile->materials) }}"
                               placeholder="Ex: Argile locale, bois d'iroko, tissage manuel" maxlength="255">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-600">Langues parlées</label>
                        <input type="text" name="languages" class="form-control"
                               value="{{ old('languages', $profile->languages) }}"
                               placeholder="Ex: Français, Fon, Yoruba" maxlength="255">
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2 flex-wrap profile-actions">
                    <button type="submit" class="btn btn-clay px-4">
                        <i class="bi bi-check2 me-2"></i>Enregistrer
                    </button>
                    <a href="{{ route('artisan.dashboard') }}"
                       class="btn btn-outline-secondary">Annuler</a>
                </div>
            </form>
        </div>

        @include('partials.password-form', ['passwordRoute' => route('artisan.profile.password')])
    </div>

    {{-- Sidebar aperçu --}}
    <div class="col-lg-4">
        <div class="content-card text-center mb-3">
            <p class="section-label mb-2">APERÇU</p>
            <img src="{{ $user->avatarUrl() }}" class="rounded-circle mb-2"
                 id="avatar-preview-2"
                 width="64" height="64"
                 style="object-fit:cover;border:2px solid var(--clay)" alt="{{ $user->name }}">
            <h6 class="fw-700 mb-0">{{ $user->name }}</h6>
            <p class="text-muted mb-1" style="font-size:.85rem">{{ $profile->specialty }}</p>
            <div class="d-flex gap-1 justify-content-center flex-wrap">
                <span class="badge px-2 py-1"
                      style="background:{{ $profile->badge()==='Top Artisan' ? 'var(--clay)' : ($profile->badge()==='Vérifié' ? '#2E7D32' : 'var(--gold)') }};
                             color:#fff;font-size:.75rem">
                    {{ $profile->badge() }}
                </span>
                <span class="badge px-2 py-1" style="background:{{ $profile->tierColor() }};color:#fff;font-size:.75rem">
                    <i class="bi bi-award me-1"></i>{{ $profile->tierLabel() }}
                </span>
            </div>
        </div>

        <div class="content-card mb-3">
            <p class="section-label mb-2">🛡️ VÉRIFICATION D'IDENTITÉ</p>
            @if($user->isFullyVerified())
                <p class="mb-0" style="font-size:.85rem;color:#2E7D32">
                    <i class="bi bi-patch-check-fill me-1"></i>Identité vérifiée — vous pouvez proposer la garantie satisfait/repris si vous êtes Expert.
                </p>
            @else
                <p class="text-muted mb-2" style="font-size:.82rem">
                    Requis pour atteindre le palier Expert et proposer la garantie satisfait/repris.
                </p>
                <ul class="mb-2" style="font-size:.82rem;padding-left:1.2rem">
                    <li>Pièce d'identité : <strong>{{ match($user->id_document_status){'approved'=>'✅ Approuvée','pending'=>'⏳ En cours de vérification','rejected'=>'❌ Rejetée','none'=>'À envoyer', default=>'À envoyer'} }}</strong></li>
                    <li>Téléphone : <strong>{{ $user->isPhoneVerified() ? '✅ Vérifié' : 'À vérifier' }}</strong></li>
                </ul>
                <a href="{{ route('kyc.show') }}" class="btn btn-outline-clay btn-sm w-100">
                    <i class="bi bi-shield-check me-1"></i>Compléter la vérification
                </a>
            @endif
        </div>

        <div class="content-card" style="background:var(--sand)">
            <p class="section-label mb-2">💡 CONSEILS</p>
            <ul style="font-size:.82rem;padding-left:1.2rem" class="mb-0">
                <li class="mb-1">Photo professionnelle = +40% de clics</li>
                <li class="mb-1">Bio détaillée rassure les clients</li>
                <li class="mb-1">Tarif indicatif filtre les mauvaises demandes</li>
                <li>Mettez à jour votre disponibilité</li>
            </ul>
        </div>
    </div>
</div>

@push('styles')
<style>
    @media (max-width: 400px) {
        .profile-actions .btn { width: 100%; }
    }
</style>
@endpush
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('avatar-preview').src   = e.target.result;
            document.getElementById('avatar-preview-2').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function captureLocation() {
    if (!navigator.geolocation) {
        alert("La géolocalisation n'est pas disponible sur ce navigateur.");
        return;
    }
    const status = document.getElementById('geoloc-status');
    status.innerHTML = '<i class="bi bi-hourglass-split"></i> Localisation en cours...';
    navigator.geolocation.getCurrentPosition(
        pos => {
            document.getElementById('input-latitude').value  = pos.coords.latitude;
            document.getElementById('input-longitude').value = pos.coords.longitude;
            status.innerHTML = '<i class="bi bi-check-circle text-success"></i> Position capturée — enregistrez le profil pour la sauvegarder.';
        },
        () => { status.innerHTML = 'Impossible de récupérer votre position.'; },
        { enableHighAccuracy: true, timeout: 8000 }
    );
}
</script>
@endpush
