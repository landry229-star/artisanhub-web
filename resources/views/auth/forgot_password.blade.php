@extends('layouts.app')
@section('title', 'Mot de passe oublié')
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="text-center mb-4">
                <div style="font-size:2.5rem">🔑</div>
                <h2 class="mt-2" style="font-family:'Playfair Display',serif">Mot de passe oublié</h2>
                <p class="text-muted">Entrez votre email pour recevoir un lien de réinitialisation</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-4">
                    <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card p-4">
                <form action="{{ route('password.send-reset') }}" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="form-label fw-600">
                            Adresse email <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="vous@email.com"
                               autofocus required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-clay w-100 py-2">
                        <i class="bi bi-send me-2"></i>Envoyer le lien de réinitialisation
                    </button>
                </form>
            </div>

            <p class="text-center mt-3 text-muted">
                Vous vous souvenez de votre mot de passe ?
                <a href="{{ route('login') }}" style="color:var(--clay);font-weight:600">
                    Se connecter
                </a>
            </p>
        </div>
    </div>
</div>
@endsection
