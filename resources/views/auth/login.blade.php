@extends('layouts.app')
@section('title', 'Connexion')
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="text-center mb-4">
                <div style="font-size:2.5rem">🏺</div>
                <h2 class="mt-2" style="font-family:'Playfair Display',serif">Connexion</h2>
                <p class="text-muted">Bon retour sur ArtisanHub</p>
            </div>

            <div class="card p-4">
                {{-- id="form-login" permet à artisanhub.js de cibler ce formulaire --}}
                <form action="{{ route('login') }}" method="POST" id="form-login">
                    @csrf

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show">
                            {{ $errors->first() }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label fw-600">Adresse email</label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="vous@email.com" autocomplete="email" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Mot de passe</label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwd"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="••••••••" required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd()" tabindex="-1">
                                <i class="bi bi-eye" id="pwd-icon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox"
                                   name="remember" id="remember">
                            <label class="form-check-label" for="remember">
                                Se souvenir de moi
                            </label>
                        </div>
                        <a href="{{ route('password.forgot') }}" style="color:var(--clay);font-size:.875rem">Mot de passe oublié ?</a>
                    </div>

                    <button type="submit" class="btn btn-clay w-100 py-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Se connecter
                    </button>
                </form>
            </div>

            <p class="text-center mt-3 text-muted">
                Pas encore de compte ?
                <a href="{{ route('register') }}" style="color:var(--clay);font-weight:600">
                    S'inscrire
                </a>
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePwd() {
    const p = document.getElementById('pwd');
    const i = document.getElementById('pwd-icon');
    p.type = p.type === 'password' ? 'text' : 'password';
    i.className = p.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}
</script>
@endpush
