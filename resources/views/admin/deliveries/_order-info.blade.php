<p class="section-label mb-3">Informations commande</p>
<div style="font-size:.85rem">
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted">Client</span>
        <strong>{{ $delivery->order->client->name }}</strong>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted">Artisan</span>
        <strong>{{ $delivery->order->artisan->name }}</strong>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted">Budget commande</span>
        <strong>{{ number_format($delivery->order->budget,0,',',' ') }} XOF</strong>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <span class="text-muted">Frais livraison</span>
        <strong style="color:#C4622D">{{ number_format($delivery->fee,0,',',' ') }} XOF</strong>
    </div>
    <div class="d-flex justify-content-between mb-3">
        <span class="text-muted">Statut commande</span>
        <span class="badge bg-{{ $delivery->order->statusColor() }}">{{ $delivery->order->statusLabel() }}</span>
    </div>
</div>
<a href="{{ route('admin.deliveries.index') }}" class="btn btn-outline-secondary w-100 btn-sm">
    ← Retour aux livraisons
</a>
