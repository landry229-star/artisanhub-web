@extends('layouts.dashboard')
@section('title', 'Modifier un service')
@section('page-title', 'Modifier un service')
@section('sidebar-nav')
    @include('artisan.partials.sidebar')
@endsection
@section('content')
<div class="content-card" style="max-width:720px">
    <form action="{{ route('artisan.services.update', $service) }}" method="POST" enctype="multipart/form-data">
        @csrf @method('PATCH')
        <div class="mb-3">
            <label class="form-label fw-600">Titre</label>
            <input name="title" class="form-control" value="{{ old('title', $service->title) }}" required>
            @error('title')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label fw-600">Description</label>
            <textarea name="description" class="form-control" rows="5" required>{{ old('description', $service->description) }}</textarea>
            @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <label class="form-label fw-600">Prix (XOF)</label>
                <input type="number" name="price" class="form-control" min="500" value="{{ old('price', $service->price) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-600">Délai (jours)</label>
                <input type="number" name="delay_days" class="form-control" min="1" max="90" value="{{ old('delay_days', $service->delay_days) }}" required>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-600">Remplacer la photo</label>
            <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/webp">
        </div>
        <a href="{{ route('artisan.services.index') }}" class="btn btn-outline-secondary me-2">Annuler</a>
        <button class="btn btn-clay">Enregistrer</button>
    </form>
</div>
@endsection
