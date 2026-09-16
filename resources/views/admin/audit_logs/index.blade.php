@extends('layouts.dashboard')

@section('title', 'Journal d’audit')
@section('page-title', 'Journal d’audit')

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@section('content')
    <div class="content-card">
        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
            <div>
                <h5 class="fw-700 mb-1">Actions administrateur</h5>
                <div class="text-muted small">Historique des actions sensibles avec utilisateur, cible et métadonnées.</div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm align-middle">
                <thead>
                    <tr>
                        <th>Action</th>
                        <th>Admin</th>
                        <th>Cible</th>
                        <th>Métadonnées</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr>
                            <td><span class="badge bg-secondary">{{ $log->action }}</span></td>
                            <td>{{ $log->admin?->name ?? 'Système' }}</td>
                            <td>
                                @if($log->target_type)
                                    {{ class_basename($log->target_type) }} #{{ $log->target_id }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(!empty($log->meta))
                                    <small class="text-muted">{{ json_encode($log->meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Aucune action enregistrée.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $logs->links() }}</div>
    </div>
@endsection
