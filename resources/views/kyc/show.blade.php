@extends('layouts.dashboard')
@section('title', 'Vérification d\'identité')
@section('page-title', 'Vérification d\'identité')

@section('sidebar-nav')
    @if($user->isArtisan())
        @include('artisan.partials.sidebar')
    @elseif($user->isAdmin())
        @include('admin.partials.sidebar')
    @endif
@endsection

@section('content')
<div class="row g-4">
    <div class="col-lg-7">

        {{-- Pièce d'identité --}}
        <div class="content-card mb-4">
            <h5 class="fw-700 mb-1"><i class="bi bi-card-image me-2 text-clay"></i>Pièce d'identité</h5>
            <p class="text-muted mb-3" style="font-size:.85rem">
                CNI, passeport ou permis de conduire. Photo ou scan lisible, format JPG/PNG/PDF, 4 Mo max.
            </p>

            @if($user->id_document_status === 'approved')
                <div class="alert alert-success mb-0">
                    <i class="bi bi-patch-check-fill me-2"></i>Votre pièce d'identité est vérifiée.
                </div>
            @else
                @if($user->id_document_status === 'pending')
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-hourglass-split me-2"></i>Document envoyé, en attente de vérification par un administrateur.
                    </div>
                @elseif($user->id_document_status === 'rejected')
                    <div class="alert alert-danger mb-3">
                        <i class="bi bi-x-circle me-2"></i>Document rejeté — motif : {{ $user->id_document_rejected_reason }}.
                        Vous pouvez en soumettre un nouveau ci-dessous.
                    </div>
                @endif

                <form action="{{ route('kyc.document.upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="form-label fw-600">Type de document</label>
                            <select name="id_document_type" class="form-select" required>
                                <option value="cni">Carte nationale d'identité</option>
                                <option value="passeport">Passeport</option>
                                <option value="permis">Permis de conduire</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label fw-600">Document (photo/scan)</label>
                            <input type="file" name="id_document" class="form-control"
                                   accept="image/jpeg,image/png,application/pdf" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-clay mt-3">
                        <i class="bi bi-upload me-2"></i>Envoyer pour vérification
                    </button>
                </form>
            @endif
        </div>

        {{-- Vérification téléphone --}}
        <div class="content-card">
            <h5 class="fw-700 mb-1"><i class="bi bi-phone me-2 text-clay"></i>Numéro de téléphone</h5>
            <p class="text-muted mb-3" style="font-size:.85rem">
                Confirmez votre numéro par code à 6 chiffres envoyé par WhatsApp/SMS.
            </p>

            @if($user->isPhoneVerified())
                <div class="alert alert-success mb-0">
                    <i class="bi bi-patch-check-fill me-2"></i>Numéro {{ $user->phone }} vérifié.
                </div>
            @elseif(!$user->phone)
                <div class="alert alert-secondary mb-0">
                    Renseignez d'abord un numéro dans votre profil avant de le vérifier.
                </div>
            @else
                <div class="d-flex gap-2 flex-wrap align-items-end">
                    <form action="{{ route('kyc.phone.send') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-clay">
                            <i class="bi bi-send me-2"></i>Envoyer le code à {{ $user->phone }}
                        </button>
                    </form>
                </div>

                <form action="{{ route('kyc.phone.verify') }}" method="POST" class="mt-3 d-flex gap-2">
                    @csrf
                    <input type="text" name="code" class="form-control" style="max-width:160px"
                           maxlength="6" placeholder="Code à 6 chiffres" required>
                    <button type="submit" class="btn btn-clay">Confirmer</button>
                </form>
            @endif
        </div>
    </div>

    <div class="col-lg-5">
        <div class="content-card" style="background:var(--sand)">
            <p class="section-label mb-2">💡 POURQUOI SE VÉRIFIER ?</p>
            <ul style="font-size:.85rem;padding-left:1.2rem" class="mb-0">
                <li class="mb-2">C'est une condition pour proposer la <strong>garantie satisfait/repris</strong> une fois au palier Expert.</li>
                <li class="mb-2">Les clients font davantage confiance aux profils vérifiés.</li>
                <li>Vos informations ne sont utilisées qu'en interne, pour la modération et la résolution de litiges.</li>
            </ul>
        </div>
    </div>
</div>
@endsection
