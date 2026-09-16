@extends('layouts.app')
@section('title', 'Nouveau mot de passe')
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="text-center mb-4">
                <div style="font-size:2.5rem">🔐</div>
                <h2 class="mt-2" style="font-family:'Playfair Display',serif">Nouveau mot de passe</h2>
                <p class="text-muted">Choisissez un mot de passe sécurisé</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show mb-4">
                    @foreach($errors->all() as $error)
                        <div><i class="bi bi-exclamation-circle me-2"></i>{{ $error }}</div>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card p-4">
                <form action="{{ route('password.reset') }}" method="POST">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="mb-3">
                        <label class="form-label fw-600">Email</label>
                        <input type="email" class="form-control bg-light" value="{{ $email }}" disabled>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">
                            Nouveau mot de passe <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="pwd"
                                   class="form-control @error('password') is-invalid @enderror"
                                   placeholder="Min. 8 caractères" required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('pwd','icon1')">
                                <i class="bi bi-eye" id="icon1"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger mt-1" style="font-size:.85rem">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600">
                            Confirmer le mot de passe <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password_confirmation" id="pwd2"
                                   class="form-control" placeholder="Répétez le mot de passe" required>
                            <button class="btn btn-outline-secondary" type="button"
                                    onclick="togglePwd('pwd2','icon2')">
                                <i class="bi bi-eye" id="icon2"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Indicateur de force --}}
                    <div class="mb-4">
                        <div class="d-flex gap-1" id="strength-bars">
                            @for($i=0; $i<4; $i++)
                                <div class="flex-grow-1 rounded"
                                     style="height:4px;background:#ECD8C6;transition:.3s"
                                     id="bar-{{ $i }}"></div>
                            @endfor
                        </div>
                        <div id="strength-label" class="text-muted mt-1" style="font-size:.78rem"></div>
                    </div>

                    <button type="submit" class="btn btn-clay w-100 py-2">
                        <i class="bi bi-check2-circle me-2"></i>Enregistrer le nouveau mot de passe
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function togglePwd(id, iconId) {
    const f = document.getElementById(id);
    const i = document.getElementById(iconId);
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'text' ? 'bi bi-eye-slash' : 'bi bi-eye';
}

document.getElementById('pwd').addEventListener('input', function() {
    const val = this.value;
    let score = 0;
    if (val.length >= 8)          score++;
    if (/[A-Z]/.test(val))        score++;
    if (/[0-9]/.test(val))        score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors  = ['#dc3545','#fd7e14','#ffc107','#198754'];
    const labels  = ['Trop faible','Faible','Moyen','Fort ✅'];
    const bars    = document.querySelectorAll('[id^="bar-"]');

    bars.forEach((b, i) => {
        b.style.background = i < score ? colors[score-1] : '#ECD8C6';
    });

    const label = document.getElementById('strength-label');
    label.textContent = val.length > 0 ? labels[score-1] || '' : '';
    label.style.color = score > 0 ? colors[score-1] : '';
});
</script>
@endpush
