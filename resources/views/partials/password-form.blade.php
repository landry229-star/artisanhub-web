{{--
    Partial réutilisable : formulaire de changement de mot de passe.
    Variable attendue : $passwordRoute (URL du endpoint PUT ...password)
    Utilise le error bag "updatePassword" pour ne pas entrer en conflit
    avec le formulaire "infos personnelles" présent sur la même page.
--}}
<div class="content-card mt-4">
    <h5 class="fw-700 mb-4"><i class="bi bi-shield-lock me-2"></i>Sécurité — Mot de passe</h5>

    <form action="{{ $passwordRoute }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-600">Mot de passe actuel <span class="text-danger">*</span></label>
                <input type="password" name="current_password"
                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror"
                       required autocomplete="current-password">
                @error('current_password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="col-md-4">
                <label class="form-label fw-600">Nouveau mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password"
                       class="form-control @error('password', 'updatePassword') is-invalid @enderror"
                       required minlength="8" autocomplete="new-password">
                @error('password', 'updatePassword')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <div class="form-text">8 caractères minimum.</div>
            </div>

            <div class="col-md-4">
                <label class="form-label fw-600">Confirmer le nouveau mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password_confirmation"
                       class="form-control" required minlength="8" autocomplete="new-password">
            </div>
        </div>

        <div class="mt-4">
            <button type="submit" class="btn btn-clay px-4">
                <i class="bi bi-key me-2"></i>Modifier le mot de passe
            </button>
        </div>
    </form>
</div>
