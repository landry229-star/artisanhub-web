@extends('layouts.dashboard')
@section('title', 'Mon support')
@section('page-title', 'Support et réclamations')
@section('sidebar-nav')
    <div class="sidebar-section-title">Principal</div>
    <a href="{{ route('client.dashboard') }}" class="sidebar-link"><i class="bi bi-speedometer2"></i> Tableau de bord</a>
    <a href="{{ route('client.orders.index') }}" class="sidebar-link"><i class="bi bi-bag"></i> Mes commandes</a>
    <a href="{{ route('client.support.index') }}" class="sidebar-link active"><i class="bi bi-life-preserver"></i> Support</a>
    <a href="{{ route('support') }}" class="sidebar-link"><i class="bi bi-plus-circle"></i> Nouvelle demande</a>
@endsection
@section('content')
<div class="row g-4">
    <div class="col-lg-7">
        <div class="content-card">
            <h5 class="mb-3 fw-700">Mes tickets support</h5>
            @forelse($tickets as $ticket)
                <div class="py-3 border-bottom">
                    <div class="d-flex justify-content-between gap-2">
                        <strong>{{ $ticket->number }}</strong>
                        <span class="badge bg-secondary">{{ ['open'=>'Ouvert','in_progress'=>'En cours','resolved'=>'Résolu','closed'=>'Fermé'][$ticket->status] }}</span>
                    </div>
                    <div class="mt-1">{{ $ticket->subject }}</div>
                    <div class="small text-muted">{{ $ticket->created_at->format('d/m/Y à H:i') }}</div>
                    @if($ticket->admin_reply)
                        <div class="p-2 mt-2 rounded small" style="background:#F5EFE6"><strong>Réponse équipe :</strong> {{ $ticket->admin_reply }}</div>
                    @endif
                    @if($ticket->resolved_at)<div class="mt-1 small text-success">Résolu le {{ $ticket->resolved_at->format('d/m/Y à H:i') }}</div>@endif
                </div>
            @empty <p class="mb-0 text-muted">Aucun ticket support.</p>@endforelse
            {{ $tickets->links() }}
        </div>
    </div>
    <div class="col-lg-5">
        <div class="content-card">
            <h5 class="mb-3 fw-700">Mes réclamations</h5>
            @forelse($claims as $claim)
                <div class="py-3 border-bottom">
                    <strong>Réclamation #{{ $claim->id }}</strong>
                    <span class="badge bg-secondary">{{ $claim->status }}</span>
                    <div class="small text-muted">Commande #{{ $claim->order_id }} · {{ $claim->created_at->format('d/m/Y') }}</div>
                    @if($claim->admin_note)<div class="mt-1 small"><strong>Réponse :</strong> {{ $claim->admin_note }}</div>@endif
                    @if($claim->resolved_at)<div class="small text-success">Traitée le {{ $claim->resolved_at->format('d/m/Y à H:i') }}</div>@endif
                    @if($claim->refund_status)<div class="small">Remboursement : {{ $claim->refund_status }}{{ $claim->fedapay_payout_id ? ' · '.$claim->fedapay_payout_id : '' }}</div>@endif
                </div>
            @empty <p class="mb-0 text-muted">Aucune réclamation.</p>@endforelse
            {{ $claims->links() }}
        </div>
    </div>
</div>
@endsection
