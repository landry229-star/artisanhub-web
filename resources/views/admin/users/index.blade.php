@extends('layouts.dashboard')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
    @include('admin.partials.mobile-styles')
@endpush

@section('topbar-actions')
    <a href="{{ route('admin.users.export') }}" class="btn btn-sm btn-outline-clay">
        <i class="bi bi-download me-1"></i>Exporter CSV
    </a>
@endsection

@section('content')
<div class="content-card">
    {{-- Filtres --}}
    <form method="GET" class="row g-2 mb-4">
        <div class="col-12 col-md-3">
            <select name="role" class="form-select" onchange="this.form.submit()">
                <option value="">Tous les rôles</option>
                <option value="artisan" {{ request('role')==='artisan' ? 'selected':'' }}>Artisans</option>
                <option value="client"  {{ request('role')==='client'  ? 'selected':'' }}>Clients</option>
            </select>
        </div>
        <div class="col-12 col-md-3">
            <select name="city" class="form-select" onchange="this.form.submit()">
                <option value="">Toutes les villes</option>
                @foreach(config('artisanhub.cities') as $city)
                    <option value="{{ $city }}" {{ request('city')===$city ? 'selected':'' }}>{{ $city }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-md-4">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Rechercher nom / email..."
                       value="{{ request('search') }}">
                <button class="btn btn-clay" type="submit"><i class="bi bi-search"></i></button>
            </div>
        </div>
    </form>

    {{-- Tableau (desktop / tablette) --}}
    <div class="table-responsive desktop-only-table">
        <table class="table align-middle">
            <thead>
                <tr>
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Ville</th>
                    <th>Statut</th>
                    <th>Inscrit le</th>
                    <th>KYC</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>
                            <div class="d-flex gap-2 align-items-center">
                                <img src="{{ $user->avatarUrl() }}" class="rounded-circle"
                                     width="38" height="38" style="object-fit:cover">
                                <div>
                                    <div class="fw-600" style="font-size:.9rem">{{ $user->name }}</div>
                                    <div class="text-muted" style="font-size:.78rem">{{ $user->email }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge"
                                  style="background:{{ $user->role==='artisan' ? '#F5EFE6' : '#CCE5FF' }};
                                         color:{{ $user->role==='artisan' ? '#C4622D' : '#004085' }};
                                         font-size:.78rem">
                                {{ ucfirst($user->role) }}
                            </span>
                        </td>
                        <td><span class="text-muted" style="font-size:.875rem">{{ $user->city }}</span></td>
                        <td>
                            @if(!$user->is_active)
                                <span class="badge-status" style="background:#F8D7DA;color:#721C24">Suspendu</span>
                            @elseif($user->role === 'artisan' && !$user->is_verified)
                                <span class="badge-status" style="background:#FFF3CD;color:#856404">À vérifier</span>
                            @else
                                <span class="badge-status" style="background:#D4EDDA;color:#155724">Actif</span>
                            @endif
                        </td>
                        <td><span class="text-muted" style="font-size:.82rem">{{ $user->created_at->format('d/m/Y') }}</span></td>
                        <td>
                            @if($user->id_document_status === 'none')
                                <span class="badge-status" style="background:#F0F0F0;color:#666">—</span>
                            @elseif($user->id_document_status === 'approved')
                                <span class="badge-status" style="background:#D4EDDA;color:#155724">
                                    <i class="bi bi-patch-check-fill"></i> Vérifié
                                </span>
                            @elseif($user->id_document_status === 'rejected')
                                <span class="badge-status" style="background:#F8D7DA;color:#721C24">Rejeté</span>
                            @else
                                <div class="d-flex align-items-center gap-1 flex-wrap">
                                    <a href="{{ route('kyc.document.view', $user) }}" target="_blank"
                                       class="btn btn-sm btn-outline-secondary" style="font-size:.72rem" title="Voir le document">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <form action="{{ route('admin.users.document.approve', $user) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm" style="background:#D4EDDA;color:#155724;font-size:.72rem" title="Approuver">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" style="font-size:.72rem"
                                            title="Rejeter" data-bs-toggle="modal" data-bs-target="#rejectKyc{{ $user->id }}">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                    <div class="modal fade" id="rejectKyc{{ $user->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content">
                                                <form action="{{ route('admin.users.document.reject', $user) }}" method="POST">
                                                    @csrf @method('PATCH')
                                                    <div class="modal-header"><h6 class="modal-title">Rejeter la pièce de {{ $user->name }}</h6></div>
                                                    <div class="modal-body">
                                                        <label class="form-label fw-600">Motif</label>
                                                        <input type="text" name="reason" class="form-control" required>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="submit" class="btn btn-danger btn-sm">Rejeter</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                @if($user->role === 'artisan' && !$user->is_verified)
                                    <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                                        @csrf @method('PATCH')
                                        <button class="btn btn-sm" style="background:#D4EDDA;color:#155724;font-size:.75rem"
                                                title="Vérifier l'artisan">
                                            <i class="bi bi-shield-check"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-sm btn-outline-warning" style="font-size:.75rem"
                                            title="{{ $user->is_active ? 'Suspendre' : 'Réactiver' }}"
                                            onclick="return confirm('{{ $user->is_active ? 'Suspendre' : 'Réactiver' }} cet utilisateur ?')">
                                        <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}-circle"></i>
                                    </button>
                                </form>
                                <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" style="font-size:.75rem"
                                            title="Supprimer"
                                            onclick="return confirm('Supprimer définitivement cet utilisateur ?')">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center py-4 text-muted">Aucun utilisateur trouvé</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cartes (mobile) --}}
    <div class="mobile-cards">
        @forelse($users as $user)
            <div class="mcard">
                <div class="d-flex gap-2 align-items-center mb-2">
                    <img src="{{ $user->avatarUrl() }}" class="rounded-circle"
                         width="42" height="42" style="object-fit:cover;flex-shrink:0">
                    <div class="flex-grow-1" style="min-width:0">
                        <div class="fw-700" style="font-size:.92rem">{{ $user->name }}</div>
                        <div class="text-muted text-truncate" style="font-size:.75rem">{{ $user->email }}</div>
                    </div>
                </div>
                <div class="mcard-row">
                    <span class="label">Rôle</span>
                    <span class="badge"
                          style="background:{{ $user->role==='artisan' ? '#F5EFE6' : '#CCE5FF' }};
                                 color:{{ $user->role==='artisan' ? '#C4622D' : '#004085' }};font-size:.75rem">
                        {{ ucfirst($user->role) }}
                    </span>
                </div>
                <div class="mcard-row">
                    <span class="label">Ville</span><span>{{ $user->city }}</span>
                </div>
                <div class="mcard-row">
                    <span class="label">Statut</span>
                    @if(!$user->is_active)
                        <span class="badge-status" style="background:#F8D7DA;color:#721C24">Suspendu</span>
                    @elseif($user->role === 'artisan' && !$user->is_verified)
                        <span class="badge-status" style="background:#FFF3CD;color:#856404">À vérifier</span>
                    @else
                        <span class="badge-status" style="background:#D4EDDA;color:#155724">Actif</span>
                    @endif
                </div>
                <div class="mcard-row">
                    <span class="label">Inscrit le</span><span>{{ $user->created_at->format('d/m/Y') }}</span>
                </div>

                <div class="mcard-actions">
                    @if($user->role === 'artisan' && !$user->is_verified)
                        <form action="{{ route('admin.users.verify', $user) }}" method="POST">
                            @csrf @method('PATCH')
                            <button class="btn btn-sm" style="background:#D4EDDA;color:#155724">
                                <i class="bi bi-shield-check me-1"></i>Vérifier
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('admin.users.suspend', $user) }}" method="POST">
                        @csrf @method('PATCH')
                        <button class="btn btn-sm btn-outline-warning"
                                onclick="return confirm('{{ $user->is_active ? 'Suspendre' : 'Réactiver' }} cet utilisateur ?')">
                            <i class="bi bi-{{ $user->is_active ? 'pause' : 'play' }}-circle me-1"></i>
                            {{ $user->is_active ? 'Suspendre' : 'Réactiver' }}
                        </button>
                    </form>
                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Supprimer définitivement cet utilisateur ?')">
                            <i class="bi bi-trash3 me-1"></i>Supprimer
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <p class="text-center text-muted py-4">Aucun utilisateur trouvé</p>
        @endforelse
    </div>

    <div class="mt-3">{{ $users->links() }}</div>
</div>
@endsection
