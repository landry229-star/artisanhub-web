@extends('layouts.dashboard')
@section('title', 'Modération messages')
@section('page-title', 'Modération des conversations')

@section('sidebar-nav')
    @include('admin.partials.sidebar')
@endsection

@push('styles')
    @include('admin.partials.mobile-styles')
@endpush

@section('content')

{{-- KPIs --}}
<div class="row g-3 mb-4">
    @php $kpis = [
        ['label'=>'Conversations actives', 'value'=>$stats['total_convs'],  'icon'=>'bi-chat-dots',       'bg'=>'#CCE5FF','ic'=>'#004085'],
        ['label'=>'Messages signalés',     'value'=>$stats['flagged_msgs'], 'icon'=>'bi-flag-fill',       'bg'=>'#F8D7DA','ic'=>'#721c24'],
        ['label'=>'Commandes alertées',    'value'=>$stats['alerted_orders'],'icon'=>'bi-exclamation-triangle-fill','bg'=>'#FFF3CD','ic'=>'#856404'],
        ['label'=>'Messages aujourd\'hui', 'value'=>$stats['today_messages'],'icon'=>'bi-clock',          'bg'=>'#D4EDDA','ic'=>'#155724'],
    ]; @endphp
    @foreach($kpis as $k)
    <div class="col-6 col-lg-3">
        <div class="stat-card">
            <div class="stat-icon mb-2" style="background:{{ $k['bg'] }}">
                <i class="bi {{ $k['icon'] }}" style="color:{{ $k['ic'] }}"></i>
            </div>
            <div class="stat-value">{{ $k['value'] }}</div>
            <div class="stat-label">{{ $k['label'] }}</div>
        </div>
    </div>
    @endforeach
</div>

{{-- Filtres --}}
<div class="content-card mb-4 p-3">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
            <input type="text" name="artisan" class="form-control form-control-sm"
                   placeholder="🔍 Nom artisan..." value="{{ request('artisan') }}">
        </div>
        <div class="col-6 col-md-auto">
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" name="flagged" value="1"
                       id="cb_flagged" {{ request('flagged') ? 'checked':'' }} onchange="this.form.submit()">
                <label class="form-check-label" for="cb_flagged" style="font-size:.85rem">
                    <i class="bi bi-flag-fill text-danger me-1"></i>Suspectes seulement
                </label>
            </div>
        </div>
        <div class="col-6 col-md-auto">
            <div class="form-check form-check-inline mb-0">
                <input class="form-check-input" type="checkbox" name="alerted" value="1"
                       id="cb_alerted" {{ request('alerted') ? 'checked':'' }} onchange="this.form.submit()">
                <label class="form-check-label" for="cb_alerted" style="font-size:.85rem">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i>Avec alerte
                </label>
            </div>
        </div>
        <div class="col-12 col-md-auto">
            <button type="submit" class="btn btn-clay btn-sm">Filtrer</button>
            <a href="{{ route('admin.messages.index') }}" class="btn btn-outline-secondary btn-sm ms-1">Reset</a>
        </div>
    </form>
</div>

{{-- Liste des conversations --}}
<div class="content-card">

    {{-- Tableau (desktop / tablette) --}}
    <div class="table-responsive desktop-only-table">
        <table class="table align-middle" style="font-size:.875rem">
            <thead>
                <tr style="border-bottom:2px solid #ECD8C6">
                    <th>Commande</th>
                    <th>Client</th>
                    <th>Artisan</th>
                    <th>Messages</th>
                    <th>Statut</th>
                    <th>Signalements</th>
                    <th>Alerte</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($orders as $order)
            @php
                $flaggedCount = $order->flagged_messages_count;
                $totalMsgs    = $order->messages_count;
            @endphp
            <tr style="border-bottom:1px solid #f0e8de;vertical-align:middle;
                       {{ $flaggedCount > 0 ? 'background:#fff9f5' : '' }}">
                <td>
                    <div class="fw-600">{{ Str::limit($order->title, 28) }}</div>
                    <div class="text-muted" style="font-size:.75rem">#{{ $order->id }}</div>
                </td>
                <td>{{ $order->client->name }}</td>
                <td>{{ $order->artisan->name }}</td>
                <td class="text-center">
                    <span class="badge" style="background:#ECD8C6;color:#5C3D1E">{{ $totalMsgs }}</span>
                </td>
                <td>
                    <span class="badge bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
                </td>
                <td class="text-center">
                    @if($flaggedCount > 0)
                        <span class="badge" style="background:#f8d7da;color:#721c24">
                            <i class="bi bi-flag-fill me-1"></i>{{ $flaggedCount }}
                        </span>
                    @else
                        <span class="text-muted" style="font-size:.8rem">—</span>
                    @endif
                </td>
                <td>
                    @if($order->has_alert)
                        <span class="badge" style="background:#fff3cd;color:#856404;font-size:.72rem">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i>Active
                        </span>
                    @else
                        <span class="text-muted" style="font-size:.8rem">—</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('admin.messages.show', $order) }}"
                       class="btn btn-sm btn-clay py-1 px-2" style="font-size:.78rem">
                        <i class="bi bi-eye me-1"></i>Lire
                    </a>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="text-center py-4 text-muted">Aucune conversation trouvée</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    {{-- Cartes (mobile) --}}
    <div class="mobile-cards">
        @forelse($orders as $order)
        @php
            $flaggedCount = $order->flagged_messages_count;
            $totalMsgs    = $order->messages_count;
        @endphp
        <div class="mcard" style="{{ $flaggedCount > 0 ? 'background:#fff9f5;border-color:#f5c6cb' : '' }}">
            <div class="d-flex justify-content-between align-items-start mb-1">
                <div class="fw-700" style="font-size:.9rem">{{ Str::limit($order->title, 28) }}</div>
                <span class="text-muted" style="font-size:.75rem">#{{ $order->id }}</span>
            </div>
            <div class="mcard-row"><span class="label">Client</span><span>{{ $order->client->name }}</span></div>
            <div class="mcard-row"><span class="label">Artisan</span><span>{{ $order->artisan->name }}</span></div>
            <div class="mcard-row">
                <span class="label">Messages</span>
                <span class="badge" style="background:#ECD8C6;color:#5C3D1E">{{ $totalMsgs }}</span>
            </div>
            <div class="mcard-row">
                <span class="label">Statut</span>
                <span class="badge bg-{{ $order->statusColor() }}">{{ $order->statusLabel() }}</span>
            </div>
            <div class="mcard-row">
                <span class="label">Signalements</span>
                @if($flaggedCount > 0)
                    <span class="badge" style="background:#f8d7da;color:#721c24"><i class="bi bi-flag-fill me-1"></i>{{ $flaggedCount }}</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>
            <div class="mcard-row">
                <span class="label">Alerte</span>
                @if($order->has_alert)
                    <span class="badge" style="background:#fff3cd;color:#856404"><i class="bi bi-exclamation-triangle-fill me-1"></i>Active</span>
                @else
                    <span class="text-muted">—</span>
                @endif
            </div>
            <div class="mcard-actions">
                <a href="{{ route('admin.messages.show', $order) }}" class="btn btn-sm btn-clay">
                    <i class="bi bi-eye me-1"></i>Lire la conversation
                </a>
            </div>
        </div>
        @empty
        <p class="text-center text-muted py-4">Aucune conversation trouvée</p>
        @endforelse
    </div>

    <div class="mt-3">{{ $orders->links() }}</div>
</div>
@endsection
