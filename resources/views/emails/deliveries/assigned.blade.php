@extends('emails.layout')
@section('content')
<div class="title">Nouvelle mission de livraison 🚴</div>
<div class="subtitle">Une livraison vous a été assignée</div>

<p>Bonjour <strong>{{ $notifiable->name }}</strong>,</p>
<p>Une nouvelle mission de livraison vous attend sur ArtisanHub.</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Commande</span>
        <span class="info-value">{{ $delivery->order->title }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Récupérer chez</span>
        <span class="info-value">{{ $delivery->order->artisan->name }} — {{ $delivery->pickup_city }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Livrer à</span>
        <span class="info-value">{{ $delivery->order->client->name }} — {{ $delivery->delivery_city }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Vos frais</span>
        <span class="info-value" style="color:#C4622D;font-size:15px">
            {{ number_format($delivery->fee,0,',',' ') }} XOF
        </span>
    </div>
</div>

<p style="font-size:13px;color:#9A8070">
    ⚠️ Vous avez <strong>2 heures</strong> pour accepter ou refuser cette mission.
    Passé ce délai, elle sera réassignée à un autre livreur.
</p>

<a href="{{ route('livreur.dashboard') }}" class="btn">Voir la mission →</a>
@endsection
