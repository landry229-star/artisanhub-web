@extends('emails.layout')
@section('content')
<div class="title">Votre commande est prête 📦</div>
<div class="subtitle">L'artisan a marqué votre commande comme livrée</div>

<p>Bonjour <strong>{{ $order->client->name }}</strong>,</p>
<p>
    <strong>{{ $order->artisan->name }}</strong> vous informe que votre commande est terminée
    et prête à être remise.
</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Commande</span>
        <span class="info-value">{{ $order->title }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Artisan</span>
        <span class="info-value">{{ $order->artisan->name }}</span>
    </div>
    @if($order->budget)
    <div class="amount">{{ number_format($order->budget,0,',',' ') }} XOF</div>
    <div class="amount-label">Montant à régler à la validation</div>
    @endif
</div>

<p>
    ✅ Si vous êtes <strong>satisfait du travail</strong>, validez la livraison pour déclencher le paiement.<br>
    ❌ Si vous avez un <strong>problème</strong>, signalez un litige — notre équipe interviendra sous 48h.
</p>

<a href="{{ route('client.orders.show', $order) }}" class="btn">
    Valider la livraison →
</a>

<hr class="divider">
<p style="font-size:12px;color:#9A8070;text-align:center;margin:0">
    Vous avez 72h pour valider. Passé ce délai, un admin pourra arbitrer automatiquement.
</p>
@endsection
