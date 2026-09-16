@extends('emails.layout')
@section('content')
<div class="title">Commande terminée avec succès 🎉</div>
<div class="subtitle">Paiement confirmé — merci d'avoir utilisé ArtisanHub</div>

@if($notifiable->isArtisan())
    <p>Bonjour <strong>{{ $order->artisan->name }}</strong>,</p>
    <p>
        Le client a validé la livraison et le paiement a été effectué.
        Votre virement sera traité sous <strong>24 à 48h ouvrées</strong>.
    </p>
    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Commande</span>
            <span class="info-value">{{ $order->title }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Montant total</span>
            <span class="info-value">{{ number_format($order->budget,0,',',' ') }} XOF</span>
        </div>
        <div class="info-row">
            <span class="info-label">Commission ArtisanHub (10%)</span>
            <span class="info-value" style="color:#721C24">- {{ number_format($order->payment->commission,0,',',' ') }} XOF</span>
        </div>
        <div class="info-row" style="border-top:1px solid #ECD8C6;padding-top:8px;margin-top:4px">
            <span class="info-label"><strong>Net à recevoir</strong></span>
            <span class="info-value" style="color:#C4622D;font-size:16px">{{ number_format($order->payment->net_amount,0,',',' ') }} XOF</span>
        </div>
    </div>
    <p>N'oubliez pas de <strong>demander un avis</strong> à votre client — cela améliore votre visibilité !</p>
    <a href="{{ route('artisan.dashboard') }}" class="btn">Voir mon tableau de bord →</a>

@else
    <p>Bonjour <strong>{{ $order->client->name }}</strong>,</p>
    <p>
        Votre paiement a bien été reçu. Merci d'avoir fait confiance à
        <strong>{{ $order->artisan->name }}</strong> via ArtisanHub !
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
        <div class="amount">{{ number_format($order->payment->amount,0,',',' ') }} XOF</div>
        <div class="amount-label">Paiement confirmé ✅</div>
    </div>
    <p>Prenez un moment pour <strong>laisser un avis</strong> — cela aide les autres clients à choisir !</p>
    <a href="{{ route('client.orders.show', $order) }}" class="btn">Laisser un avis →</a>
@endif
@endsection
