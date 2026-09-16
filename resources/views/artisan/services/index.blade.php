@extends('layouts.dashboard')
@section('title', 'Mes services')
@section('page-title', 'Mes services')

@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection

@section('content')
<div class="row g-4">

    {{-- Formulaire ajout service --}}
    <div class="col-lg-4">
        <div class="content-card">
            <h5 class="fw-700 mb-3">Ajouter un service</h5>

            {{-- id="form-service" cible artisanhub.js --}}
            <form action="{{ route('artisan.services.store') }}" method="POST"
                  enctype="multipart/form-data" id="form-service">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-600">Titre <span class="text-danger">*</span></label>
                    <input type="text" name="title"
                           class="form-control @error('title') is-invalid @enderror"
                           value="{{ old('title') }}"
                           placeholder="Ex: Fabrication d'un meuble sur mesure">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Description <span class="text-danger">*</span></label>
                    <textarea name="description" rows="3"
                              class="form-control @error('description') is-invalid @enderror"
                              placeholder="Ce que vous proposez, les matériaux, le processus...">{{ old('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Prix (XOF) <span class="text-danger">*</span></label>
                    <input type="number" name="price"
                           class="form-control @error('price') is-invalid @enderror"
                           value="{{ old('price') }}"
                           placeholder="Ex: 25000" min="500">
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    {{-- Le preview net s'injecte ici par artisanhub.js --}}
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Délai de livraison (jours)</label>
                    <input type="number" name="delay_days" class="form-control"
                           value="{{ old('delay_days') }}"
                           placeholder="Ex: 7" min="1">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-600">Photo du service</label>
                    <input type="file" name="image" class="form-control"
                           accept="image/jpeg,image/png,image/webp"
                           onchange="previewServiceImg(this)">
                    <div id="service-img-preview" class="mt-2 d-none">
                        <img id="service-img" class="img-fluid rounded"
                             style="max-height:120px;object-fit:cover;width:100%">
                    </div>
                </div>

                <button type="submit" class="btn btn-clay w-100">
                    <i class="bi bi-plus-circle me-2"></i>Publier le service
                </button>
            </form>
        </div>

        <div class="content-card mt-3" style="background:var(--sand)">
            <p class="section-label mb-2">💡 ASTUCE</p>
            <p style="font-size:.82rem;color:#5C3D1E;margin-bottom:0">
                Les services permettent aux clients de commander directement avec un prix et un délai définis. Créez des services précis pour éviter les négociations.
            </p>
        </div>
    </div>

    {{-- Liste services --}}
    <div class="col-lg-8">
        <div class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-700 mb-0">Mes services publiés</h5>
                <span class="text-muted" style="font-size:.85rem">
                    {{ $services->count() }} / 10
                </span>
            </div>

            @if($services->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-grid-3x3-gap" style="font-size:3rem;opacity:.25;display:block;margin-bottom:1rem"></i>
                    <p class="mb-0">Aucun service publié</p>
                    <p style="font-size:.85rem">Créez vos premiers services pour que les clients puissent commander directement</p>
                </div>
            @else
                <div class="row g-3">
                    @foreach($services as $service)
                        <div class="col-md-6">
                            <div class="border rounded-3 p-3 h-100"
                                 style="border-color:{{ $service->is_active ? '#ECD8C6' : '#dee2e6' }}!important;
                                        opacity:{{ $service->is_active ? '1' : '0.6' }}">

                                @if($service->image_path)
                                    <img src="{{ asset('storage/'.$service->image_path) }}"
                                         class="w-100 rounded-2 mb-2"
                                         style="height:100px;object-fit:cover" loading="lazy">
                                @endif

                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-700" style="font-size:.9rem">{{ $service->title }}</div>
                                    <span class="badge ms-1 px-2 py-1"
                                          style="background:{{ $service->is_active ? '#D4EDDA' : '#F8D7DA' }};
                                                 color:{{ $service->is_active ? '#155724' : '#721C24' }};
                                                 font-size:.72rem;white-space:nowrap">
                                        {{ $service->is_active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </div>

                                <p class="text-muted mb-2" style="font-size:.8rem;line-height:1.5">
                                    {{ Str::limit($service->description, 70) }}
                                </p>

                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-700 text-clay">
                                        {{ number_format($service->price, 0, ',', ' ') }} XOF
                                    </span>
                                    @if($service->delay_days)
                                        <span class="text-muted" style="font-size:.78rem">
                                            <i class="bi bi-clock me-1"></i>{{ $service->delay_days }} j.
                                        </span>
                                    @endif
                                </div>

                                <div class="d-flex gap-1">
                                    <a href="{{ route('artisan.services.edit', $service) }}"
                                       class="btn btn-sm btn-outline-clay" style="font-size:.75rem">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="{{ route('artisan.services.toggle', $service) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm {{ $service->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                style="font-size:.75rem">
                                            {{ $service->is_active ? 'Désactiver' : 'Activer' }}
                                        </button>
                                    </form>
                                    <form action="{{ route('artisan.services.destroy', $service) }}"
                                          method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger"
                                                style="font-size:.75rem"
                                                data-confirm="Supprimer ce service définitivement ?">
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

@push('scripts')
<script>
function previewServiceImg(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('service-img').src = e.target.result;
            document.getElementById('service-img-preview').classList.remove('d-none');
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
