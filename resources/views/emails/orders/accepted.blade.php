@extends('emails.layout')
@section('content')
<div class="title">Votre commande a été acceptée ✅</div>
<div class="subtitle">L'artisan est prêt à travailler pour vous</div>

<p>Bonjour <strong>{{ $order->client->name }}</strong>,</p>
<p>
    Bonne nouvelle ! <strong>{{ $order->artisan->name }}</strong> a accepté votre commande.
    Un <strong>contrat numérique</strong> a été généré automatiquement.
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
    <div class="info-row">
        <span class="info-label">Spécialité</span>
        <span class="info-value">{{ $order->artisan->artisanProfile->specialty }}</span>
    </div>
    @if($order->deadline)
    <div class="info-row">
        <span class="info-label">Date de livraison</span>
        <span class="info-value">{{ $order->deadline->format('d/m/Y') }}</span>
    </div>
    @endif
    @if($order->budget)
    <div class="info-row">
        <span class="info-label">Montant à payer</span>
        <span class="info-value" style="color:#C4622D;font-size:15px">{{ number_format($order->budget,0,',',' ') }} XOF</span>
    </div>
    @endif
</div>

<p>Vous pouvez maintenant <strong>communiquer directement</strong> avec l'artisan via la messagerie intégrée.</p>

<a href="{{ route('client.orders.show', $order) }}" class="btn">
    Voir ma commande →
</a>

<hr class="divider">
<p style="font-size:12px;color:#9A8070;text-align:center;margin:0">
    💡 Le paiement sera déclenché uniquement après votre validation de la livraison.
</p>
@endsection
