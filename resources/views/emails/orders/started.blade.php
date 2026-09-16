@extends('emails.layout')
@section('content')
<div class="title">L'artisan a démarré votre commande 🔨</div>
<div class="subtitle">Le travail est en cours</div>

<p>Bonjour <strong>{{ $order->client->name }}</strong>,</p>
<p>
    <strong>{{ $order->artisan->name }}</strong> a démarré le travail sur votre commande.
    Vous pouvez suivre l'avancement et lui envoyer des messages directement sur ArtisanHub.
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
    @if($order->deadline)
    <div class="info-row">
        <span class="info-label">Date de livraison prévue</span>
        <span class="info-value">{{ $order->deadline->format('d/m/Y') }}</span>
    </div>
    @endif
</div>

<p style="font-size:13px;color:#9A8070">
    Vous serez notifié par email dès que l'artisan marquera la commande comme livrée.
</p>

<a href="{{ route('client.orders.show', $order) }}" class="btn">
    Suivre ma commande →
</a>
@endsection
