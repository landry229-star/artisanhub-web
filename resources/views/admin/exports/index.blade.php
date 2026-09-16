@extends('layouts.dashboard')

@section('title', 'Historique des exports')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h1 class="h3 mb-1">Historique des exports</h1>
            <p class="text-muted mb-0">Fichiers générés pour les échanges et la traçabilité admin.</p>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Ressource</th>
                        <th>Format</th>
                        <th>Nom du fichier</th>
                        <th>Filtre</th>
                        <th>Par</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td>{{ $log->resource }}</td>
                            <td><span class="badge bg-secondary text-uppercase">{{ $log->format }}</span></td>
                            <td>{{ $log->filename }}</td>
                            <td>{{ $log->filters ? collect($log->filters)->map(fn($value, $key) => $key . '=' . $value)->join(', ') : '—' }}</td>
                            <td>{{ $log->user?->name ?? '—' }}</td>
                            <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Aucun export enregistré pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $logs->links() }}
    </div>
</div>
@endsection
