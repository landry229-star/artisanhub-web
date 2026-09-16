@extends('layouts.dashboard')
@section('title', 'Mon profil')
@section('page-title', 'Mon profil')

@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('client.dashboard') }}" class="sidebar-link"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
    <a href="{{ route('client.orders.index') }}" class="sidebar-link"><i class="bi bi-bag"></i> Mes commandes</a>
    <a href="{{ route('client.wallet.index') }}" class="sidebar-link"><i class="bi bi-receipt"></i> Mes paiements</a>
    <a href="{{ route('contacts.index') }}" class="sidebar-link">
        <i class="bi bi-people"></i> Mes contacts
    </a>
    <div class="sidebar-section-title mt-2">Mon compte</div>
    <a href="{{ route('client.profile.edit') }}" class="sidebar-link active"><i class="bi bi-person-gear"></i> Mon profil</a>
    <a href="{{ route('client.favorites.index') }}" class="sidebar-link"><i class="bi bi-heart"></i> Mes favoris</a>
    <a href="{{ route('artisans.index') }}" class="sidebar-link"><i class="bi bi-search"></i> Explorer</a>
@endsection

@section('content')
<div class="row justify-content-center">
<div class="col-lg-7">
    <div class="content-card">
        <h5 class="fw-700 mb-4">Mes informations</h5>

        <form action="{{ route('client.profile.update') }}" method="POST" enctype="multipart/form-data" id="form-client-profile">
            @csrf @method('PUT')

            {{-- Avatar --}}
            <div class="mb-4 d-flex align-items-center gap-4">
                <img src="{{ $user->avatarUrl() }}" class="rounded-circle" id="avatar-preview"
                     width="80" height="80" style="object-fit:cover;border:3px solid #C4622D">
                <div>
                    <label class="btn btn-outline-clay btn-sm">
                        <i class="bi bi-camera me-1"></i>Changer la photo
                        <input type="file" name="avatar" class="d-none" accept="image/*"
                               onchange="previewAvatar(this)">
                    </label>
                    <p class="text-muted mb-0 mt-1" style="font-size:.78rem">JPG, PNG ou WebP · max 2 Mo</p>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-600">Nom complet <span class="text-danger">*</span></label>
                    <input type="text" name="name"
                           class="form-control @error('name') is-invalid @enderror"
                           value="{{ old('name', $user->name) }}" required maxlength="100">
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-600">Téléphone</label>
                    <input type="tel" name="phone" class="form-control"
                           value="{{ old('phone', $user->phone) }}" placeholder="+229 97 XX XX XX">
                </div>
                <div class="col-12">
                    <label class="form-label fw-600">Email</label>
                    <input type="email" class="form-control" value="{{ $user->email }}" disabled>
                    <div class="form-text">L'email ne peut pas être modifié.</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-600">Ville <span class="text-danger">*</span></label>
                    <select name="city" class="form-select @error('city') is-invalid @enderror" required>
                        <option value="">— Choisir une ville —</option>
                        @foreach(config('artisanhub.cities_by_dept') as $dept => $villes)
                            <optgroup label="{{ $dept }}">
                                @foreach($villes as $ville)
                                    <option value="{{ $ville }}" {{ old('city', $user->city) === $ville ? 'selected' : '' }}>
                                        {{ $ville }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    <div class="form-text">Votre ville est utilisée pour calculer les frais de livraison.</div>
                </div>
                <div class="col-12">
                    <label class="form-label fw-600">Adresse de livraison habituelle</label>
                    <textarea name="delivery_address" class="form-control" rows="2" maxlength="500"
                              placeholder="Quartier, rue, maison, repère...">{{ old('delivery_address', $user->delivery_address) }}</textarea>
                    <div class="form-text">Cette adresse aide le livreur à vous retrouver.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-600">Quartier</label>
                    <input type="text" name="quartier" class="form-control" maxlength="120"
                           value="{{ old('quartier', $user->quartier) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-600">Latitude</label>
                    <input type="number" name="latitude" class="form-control" step="any"
                           value="{{ old('latitude', $user->latitude) }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-600">Longitude</label>
                    <input type="number" name="longitude" class="form-control" step="any"
                           value="{{ old('longitude', $user->longitude) }}">
                </div>
                <div class="col-12">
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
                    <p class="mb-2 form-label fw-600">Préférences de notifications</p>
                    @foreach([
                        'order_notifications' => 'Recevoir les notifications de commandes',
                        'email_notifications' => 'Recevoir les emails',
                        'whatsapp_opt_in' => 'Recevoir les messages WhatsApp/SMS',
                    ] as $field => $label)
                        <div class="form-check">
                            <input type="hidden" name="{{ $field }}" value="0">
                            <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1"
                                   id="{{ $field }}" {{ old($field, $user->{$field}) ? 'checked' : '' }}>
                            <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                        </div>
                    @endforeach
                    <div class="form-text">Les alertes de sécurité et obligations réglementaires peuvent rester actives.</div>
                </div>
            </div>
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-clay px-4">
                    <i class="bi bi-check-lg me-2"></i>Enregistrer
                </button>
                <a href="{{ route('client.dashboard') }}" class="btn btn-outline-secondary">Annuler</a>
            </div>
        </form>
        @if($user->delivery_address || $user->quartier || $user->latitude || $user->longitude)
            <form action="{{ route('client.profile.delivery-address.clear') }}" method="POST" class="mt-3">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-sm btn-outline-danger"
                        onclick="return confirm('Supprimer cette adresse de livraison ?')">
                    <i class="bi bi-trash me-1"></i>Supprimer l'adresse de livraison
                </button>
            </form>
        @endif
    </div>

    @include('partials.password-form', ['passwordRoute' => route('client.profile.password')])
</div>
</div>
@endsection

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => document.getElementById('avatar-preview').src = e.target.result;
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
