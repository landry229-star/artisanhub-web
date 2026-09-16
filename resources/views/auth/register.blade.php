@extends('layouts.app')
@section('title', 'Inscription — Devenez artisan ou trouvez un artisan')
@section('meta_description', "Créez votre compte ArtisanHub gratuitement : artisan pour trouver des clients au Bénin, ou client pour commander sur mesure. Inscription rapide, sans frais.")

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-7">

            <div class="text-center mb-4">
                <div style="font-size:2.5rem">🏺</div>
                <h2 class="mt-2" style="font-family:'Playfair Display',serif">Créer un compte</h2>
                <p class="text-muted">Rejoignez la communauté ArtisanHub</p>
            </div>

            {{-- Choix rôle --}}
            <div class="row g-3 mb-4" id="role-selector">
    <div class="col-4">
        <div class="card p-3 text-center role-card {{ old('role')=='artisan' ? 'selected' : '' }}"
             onclick="selectRole('artisan', this)" style="cursor:pointer;transition:.2s">
            <div style="font-size:2rem">🔨</div>
            <div class="fw-700 mt-1" style="font-size:.9rem">Je suis artisan</div>
            <div class="fw-700 mt-1" style="font-size:.9rem">Je veux trouver des clients</div>
        </div>
    </div>
    <div class="col-4">
        <div class="card p-3 text-center role-card {{ old('role')=='client' ? 'selected' : '' }}"
             onclick="selectRole('client', this)" style="cursor:pointer;transition:.2s">
            <div style="font-size:2rem">🛍️</div>
            <div class="fw-700 mt-1" style="font-size:.9rem">Je suis client</div>
            <div class="fw-700 mt-1" style="font-size:.9rem">Je cherche un artisan   </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card p-3 text-center role-card {{ old('role')=='livreur' ? 'selected' : '' }}"
             onclick="selectRole('livreur', this)" style="cursor:pointer;transition:.2s">
            <div style="font-size:2rem">🚴</div>
            <div class="fw-700 mt-1" style="font-size:.9rem">Je suis livreur</div>
            <div class="fw-700 mt-1" style="font-size:.9rem">Je livre des colis</div>
        </div>
    </div>
</div>

            <div class="card p-4">
                <form action="{{ route('register') }}" method="POST" id="register-form">
                    @csrf
                    <input type="hidden" name="role" id="role-input" value="{{ old('role', '') }}">

                    @if($errors->any())
                    <div class="alert alert-danger py-2 mb-3" style="font-size:.85rem">
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Nom complet <span class="text-danger">*</span></label>
                            <input type="text" name="name"
                                   class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name') }}" placeholder="Kouamé Diallo" required maxlength="100">
                            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Téléphone</label>
                            <input type="tel" name="phone" class="form-control"
                                   value="{{ old('phone') }}" placeholder="+229 97 XX XX XX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Email <span class="text-danger">*</span></label>
                            <input type="email" name="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" placeholder="vous@email.com" required>
                            @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Select ville — toutes les communes du Bénin par département --}}
                        <div class="col-md-6">
                            <label class="form-label fw-600">Ville <span class="text-danger">*</span></label>
                            <select name="city" class="form-select @error('city') is-invalid @enderror" required>
                                <option value="">— Choisir une ville —</option>
                                @foreach(config('artisanhub.cities_by_dept') as $dept => $villes)
                                    <optgroup label="{{ $dept }}">
                                        @foreach($villes as $ville)
                                            <option value="{{ $ville }}" {{ old('city') === $ville ? 'selected' : '' }}>
                                                {{ $ville }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Champs artisan uniquement --}}
                        <div class="col-md-6 artisan-only" style="display:none">
                            <label class="form-label fw-600">Spécialité <span class="text-danger">*</span></label>
                            <input type="text" name="specialty"
                                   class="form-control @error('specialty') is-invalid @enderror"
                                   value="{{ old('specialty') }}" placeholder="Ex: Menuisier ébéniste" maxlength="100">
                            @error('specialty')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6 artisan-only" style="display:none">
                            <label class="form-label fw-600">Catégorie <span class="text-danger">*</span></label>
                            <select name="category" class="form-select @error('category') is-invalid @enderror">
                                <option value="">Choisir</option>
                                @foreach(config('artisanhub.categories') as $key => $cat)
                                    @if($key !== 'livraison') {{-- livraison réservée aux livreurs --}}
                                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>
                                        {{ $cat['icon'] }} {{ $cat['label'] }}
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('category')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="pwd"
                                       class="form-control @error('password') is-invalid @enderror"
                                       placeholder="Min. 8 caractères" required>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePwd('pwd','eyePwd')">
                                    <i class="bi bi-eye" id="eyePwd"></i>
                                </button>
                            </div>
                            @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Confirmer le mot de passe <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="pwd2"
                                       class="form-control" placeholder="••••••••" required>
                                <button type="button" class="btn btn-outline-secondary"
                                        onclick="togglePwd('pwd2','eyePwd2')">
                                    <i class="bi bi-eye" id="eyePwd2"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4">
                        <button type="submit" class="btn btn-clay w-100 py-2" id="submit-btn" disabled>
                            <i class="bi bi-person-plus me-2"></i>Créer mon compte
                        </button>
                        <p class="text-center mt-3 text-muted mb-0" style="font-size:.85rem">
                            Déjà un compte ?
                            <a href="{{ route('login') }}" style="color:var(--clay);font-weight:600">Se connecter</a>
                        </p>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .role-card.selected { border:2px solid var(--clay)!important; background:var(--sand)!important; }
    .role-card.selected .fw-700 { color:var(--clay); }
    .role-card:hover { box-shadow:0 2px 8px rgba(196,98,45,.15); }
</style>
@endpush

@push('scripts')
<script>
function selectRole(role) {
    document.getElementById('role-input').value = role;
    document.querySelectorAll('.role-card').forEach(c => c.classList.remove('selected'));
    event.currentTarget.closest('.col-4').querySelector('.role-card').classList.add('selected');
    document.getElementById('submit-btn').disabled = false;

    // Champs artisan : visible uniquement pour role=artisan
    document.querySelectorAll('.artisan-only').forEach(f => {
        f.style.display = role === 'artisan' ? 'block' : 'none';
    });
}

// Afficher/cacher le mot de passe
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye-slash';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye';
    }
}

// Validation JS avant soumission
document.getElementById('register-form').addEventListener('submit', function(e) {
    const role = document.getElementById('role-input').value;
    const pwd  = document.getElementById('pwd').value;
    const pwd2 = document.getElementById('pwd2').value;
    let errors = [];

    if (!role)         errors.push('Veuillez choisir votre rôle.');
    if (pwd.length < 8) errors.push('Le mot de passe doit contenir au moins 8 caractères.');
    if (pwd !== pwd2)   errors.push('Les mots de passe ne correspondent pas.');

    if (errors.length) {
        e.preventDefault();
        alert(errors.join('\n'));
    }
});

// Si old('role') existe après erreur serveur
@if(old('role'))
    document.getElementById('submit-btn').disabled = false;
    @if(old('role') === 'artisan')
        document.querySelectorAll('.artisan-only').forEach(f => f.style.display = 'block');
    @endif
@endif
</script>
@endpush
