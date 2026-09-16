@extends('layouts.app')
@section('title', 'Nouvelle commande')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">

            {{-- En-tête artisan --}}
            <div class="d-flex gap-3 align-items-center mb-4">
                <img src="{{ $artisan->avatarUrl() }}" class="rounded-circle"
                     width="56" height="56" style="object-fit:cover;border:2px solid #C4622D">
                <div>
                    <p style="color:#C4622D;font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.1em" class="mb-0">Nouvelle commande pour</p>
                    <h2 style="font-family:'Playfair Display',serif" class="mb-0">{{ $artisan->name }}</h2>
                    <span class="text-muted" style="font-size:.85rem">{{ $artisan->artisanProfile->specialty }}</span>
                </div>
            </div>

            {{-- Carte service sélectionné --}}
            @if($service)
            <div class="card p-3 mb-4 d-flex flex-row gap-3 align-items-center"
                 style="border:2px solid #C4622D;background:#fdf8f4">
                @if($service->image_path)
                    <img src="{{ asset('storage/'.$service->image_path) }}"
                         style="width:70px;height:70px;object-fit:cover;border-radius:8px;flex-shrink:0">
                @endif
                <div class="flex-grow-1">
                    <p class="mb-0" style="font-size:.72rem;color:#C4622D;font-weight:700;text-transform:uppercase;letter-spacing:.08em">Service sélectionné</p>
                    <p class="fw-700 mb-0">{{ $service->title }}</p>
                    <p class="mb-0 text-muted" style="font-size:.82rem">{{ $service->formattedPrice() }} — {{ $service->delayLabel() }}</p>
                </div>
                <a href="{{ route('artisans.show', $artisan->routeSlug()) }}" class="text-muted" style="font-size:.8rem">
                    <i class="bi bi-x-circle"></i> Changer
                </a>
            </div>
            @endif

            <div class="card p-4">
                <form action="{{ route('client.orders.store') }}" method="POST" enctype="multipart/form-data" id="order-form">
                    @csrf
                    <input type="hidden" name="artisan_id" value="{{ $artisan->id }}">
                    <input type="hidden" name="service_id" value="{{ $service->id ?? '' }}">

                    <div class="mb-3">
                        <label class="form-label fw-600">Titre de votre commande <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="order-title"
                               class="form-control @error('title') is-invalid @enderror"
                               value="{{ old('title', $repeatOrder->title ?? $service->title ?? '') }}"
                               placeholder="Ex: Fabrication d'un buffet en bois d'iroko"
                               maxlength="150" required>
                        <div class="d-flex justify-content-end mt-1">
                            <small class="text-muted" id="title-count">0/150</small>
                        </div>
                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-600">Description détaillée <span class="text-danger">*</span></label>
                        <textarea name="description" id="order-desc" rows="5"
                                  class="form-control @error('description') is-invalid @enderror"
                                  placeholder="Décrivez précisément votre commande : dimensions, matériaux, style, délai souhaité, lieu de livraison..."
                                  maxlength="2000" required>{{ old('description', $repeatOrder->description ?? ($service ? $service->description : '')) }}</textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <span class="form-text">Plus votre description est précise, meilleure sera la réponse.</span>
                            <small class="text-muted" id="desc-count">0/2000</small>
                        </div>
                        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Budget (XOF) <span class="text-danger">*</span></label>
                            <input type="number" name="budget" id="order-budget"
                                   class="form-control @error('budget') is-invalid @enderror"
                                   value="{{ old('budget', $repeatOrder->budget ?? $service->price ?? '') }}"
                                   placeholder="Ex: 50000" min="500" required>
                            @error('budget')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @if($service)
                                <div class="form-text text-success">
                                    <i class="bi bi-check-circle me-1"></i>Prix du service : {{ $service->formattedPrice() }}
                                </div>

                            @else
                                <div class="form-text">Donne un ordre d'idée à l'artisan</div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Date limite souhaitée</label>
                            <input type="date" name="deadline"
                                   class="form-control @error('deadline') is-invalid @enderror"
                                   value="{{ old('deadline', $repeatOrder?->deadline?->format('Y-m-d') ?? ($service ? now()->addDays($service->delay_days)->format('Y-m-d') : '')) }}"
                                   min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                            @error('deadline')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-600">Photos de votre projet <span class="text-muted fw-normal">(facultatif, 1 à 5)</span></label>
                        <input type="file" name="images[]" multiple accept="image/jpeg,image/png,image/webp"
                               class="form-control @error('images') is-invalid @enderror @error('images.*') is-invalid @enderror">
                        <div class="form-text">JPG, PNG ou WEBP — 5 Mo maximum par image. Elles resteront visibles uniquement par vous et l’artisan.</div>
                        @error('images')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        @error('images.*')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="p-3 rounded-3 mb-4" style="background:#F5EFE6;border:1px solid #ECD8C6">
                        {{-- Option livraison --}}
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="needs_delivery"
                                   value="1" id="needs_delivery"
                                   {{ old('needs_delivery') ? 'checked' : '' }}
                                   onchange="toggleDelivery(this.checked)">
                            <label class="form-check-label fw-600" for="needs_delivery">
                                🚴 Je souhaite une livraison à domicile
                            </label>
                        </div>
                        <div id="delivery-city-block" style="display:{{ old('needs_delivery') ? 'block' : 'none' }}">
                            <label class="form-label fw-600 mt-2">Ville de livraison <span class="text-danger">*</span></label>
                            <select name="delivery_city" id="delivery_city"
                                    class="form-select @error('delivery_city') is-invalid @enderror">
                                <option value="">— Choisir votre ville —</option>
                                @foreach(config('artisanhub.cities_by_dept') as $dept => $villes)
                                    <optgroup label="{{ $dept }}">
                                        @foreach($villes as $ville)
                                            <option value="{{ $ville }}" {{ old('delivery_city', auth()->user()->city) === $ville ? 'selected' : '' }}>
                                                {{ $ville }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('delivery_city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <p class="text-muted mt-1 mb-0" style="font-size:.78rem">
                                Les frais de livraison seront calculés selon la distance. Ils seront à régler directement au livreur.
                            </p>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 mb-4" style="background:#F5EFE6;border:1px solid #ECD8C6">
                        <p class="mb-1 fw-600" style="font-size:.875rem">📋 Ce qui se passe ensuite :</p>
                        <ol class="mb-0" style="font-size:.82rem;color:#5C3D1E;padding-left:1.2rem">
                            <li>L'artisan reçoit votre demande et peut l'accepter ou refuser</li>
                            <li>Si acceptée, un contrat numérique est généré automatiquement</li>
                            <li>Vous communiquez via la messagerie intégrée</li>
                            <li>Le paiement est déclenché uniquement après votre validation</li>
                        </ol>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-clay flex-grow-1 py-2" id="submit-btn">
                            <i class="bi bi-send me-2"></i>Envoyer la commande
                        </button>
                        <a href="{{ route('artisans.show', $artisan->routeSlug()) }}" class="btn btn-outline-secondary">Annuler</a>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
// Toggle bloc livraison
function toggleDelivery(checked) {
    const block = document.getElementById('delivery-city-block');
    const select = document.getElementById('delivery_city');
    block.style.display = checked ? 'block' : 'none';
    select.required = checked;
}

// Compteurs caractères
function bindCounter(inputId, counterId) {
    const el = document.getElementById(inputId);
    const counter = document.getElementById(counterId);
    if (!el || !counter) return;
    const max = el.maxLength;
    const update = () => {
        const len = el.value.length;
        counter.textContent = `${len}/${max}`;
        counter.style.color = len > max * 0.9 ? '#dc3545' : '#6c757d';
    };
    el.addEventListener('input', update);
    update();
}
bindCounter('order-title', 'title-count');
bindCounter('order-desc', 'desc-count');

// Validation JS avant soumission
document.getElementById('order-form').addEventListener('submit', function(e) {
    const title  = document.getElementById('order-title').value.trim();
    const desc   = document.getElementById('order-desc').value.trim();
    const budget = parseInt(document.getElementById('order-budget').value);
    const btn    = document.getElementById('submit-btn');
    let errors   = [];

    if (!title)       errors.push('Le titre de la commande est obligatoire.');
    if (!desc)        errors.push('La description est obligatoire.');
    if (budget < 500) errors.push('Le budget minimum est de 500 XOF.');

    if (errors.length) {
        e.preventDefault();
        alert(errors.join('\n'));
        return;
    }

    // Anti double-clic
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Envoi en cours...';
});
</script>
@endpush
@endsection
