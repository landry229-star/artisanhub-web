@extends('layouts.dashboard')
@section('title', 'Mon Portfolio')
@section('page-title', 'Mon Portfolio')

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('content')
<div class="row g-4">

    {{-- Formulaire ajout --}}
    <div class="col-lg-4">
        <div class="content-card">
            <h5 class="fw-700 mb-3">Ajouter une photo</h5>

            {{-- id="form-portfolio" cible artisanhub.js --}}
            <form action="{{ route('artisan.portfolio.store') }}" method="POST"
                  enctype="multipart/form-data" id="form-portfolio">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-600">
                        Photo <span class="text-danger">*</span>
                    </label>
                    <input type="file" name="image"
                           class="form-control @error('image') is-invalid @enderror"
                           accept="image/jpeg,image/png,image/webp"
                           id="portfolio-image-input"
                           onchange="previewPortfolioImage(this)">
                    @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    <div id="portfolio-preview-wrapper" class="mt-2 d-none">
                        <img id="portfolio-preview-img" class="img-fluid rounded"
                             style="max-height:150px;object-fit:cover;width:100%">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">
                        Titre <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           placeholder="Ex: Buffet en teck massif">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Description</label>
                    <textarea name="description" class="form-control" rows="2"
                              placeholder="Détails sur cette réalisation...">{{ old('description') }}</textarea>
                </div>

                <button type="submit" class="btn btn-clay w-100">
                    <i class="bi bi-cloud-upload me-2"></i>Ajouter au portfolio
                </button>
            </form>
        </div>

        {{-- Compteur --}}
        <div class="content-card mt-3" style="background:var(--sand)">
            <div class="d-flex justify-content-between align-items-center">
                <p class="section-label mb-0">📷 PHOTOS</p>
                <span class="fw-700 text-clay">{{ $items->count() }} / 20</span>
            </div>
            <div class="progress mt-2" style="height:6px">
                <div class="progress-bar" style="background:var(--clay);width:{{ ($items->count()/20)*100 }}%"></div>
            </div>
            <p class="text-muted mt-2 mb-0" style="font-size:.8rem">
                Photos de haute qualité recommandées (min. 800px)
            </p>
        </div>
    </div>

    {{-- Grille portfolio --}}
    <div class="col-lg-8">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-700 mb-0">Mes réalisations</h5>
                <span class="text-muted" style="font-size:.85rem">{{ $items->count() }} photo(s)</span>
            </div>

            @if($items->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-images" style="font-size:3rem;opacity:.25;display:block;margin-bottom:1rem"></i>
                    <p class="mb-0">Aucune photo dans votre portfolio</p>
                    <p style="font-size:.85rem">Ajoutez vos meilleures réalisations pour attirer des clients</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($items as $item)
                        <div class="col-6 col-md-4">
                            <div class="position-relative portfolio-item rounded-3 overflow-hidden"
                                 style="aspect-ratio:1;background:#f0ebe3">
                                <img src="{{ asset('storage/'.$item->image_path) }}"
                                     class="w-100 h-100" style="object-fit:cover"
                                     alt="{{ $item->title }}" loading="lazy">
                                <div class="portfolio-overlay position-absolute d-flex flex-column
                                            justify-content-end p-2"
                                     style="inset:0;background:linear-gradient(transparent,rgba(44,26,14,.85));
                                            opacity:0;transition:.2s">
                                    <div class="text-white fw-600" style="font-size:.8rem">
                                        {{ $item->title }}
                                    </div>
                                    <form action="{{ route('artisan.portfolio.destroy', $item) }}"
                                          method="POST" class="mt-1">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger py-0 px-2"
                                                data-confirm="Supprimer cette photo définitivement ?"
                                                style="font-size:.75rem">
                                            <i class="bi bi-trash3"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .portfolio-item:hover .portfolio-overlay { opacity: 1 !important; }

    /*
       Bug corrigé : l'overlay contenant le bouton "Supprimer" n'apparaissait
       qu'au survol de la souris (:hover). Sur téléphone/tablette, il n'y a
       pas de survol : le bouton restait invisible et donc totalement
       inaccessible au toucher. On l'affiche en permanence, de façon plus
       discrète, sur les écrans tactiles (max-width 991px = pas de hover fiable).
    */
    @media (max-width: 991.98px), (hover: none) {
        .portfolio-overlay {
            opacity: 1 !important;
            background: linear-gradient(transparent 40%, rgba(44,26,14,.85)) !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
function previewPortfolioImage(input) {
    const wrapper = document.getElementById('portfolio-preview-wrapper');
    const img     = document.getElementById('portfolio-preview-img');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            img.src = e.target.result;
            wrapper.classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
