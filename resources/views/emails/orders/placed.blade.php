@extends('emails.layout')
@section('content')
<div class="title">Nouvelle demande de commande 🔔</div>
<div class="subtitle">Un client souhaite vous confier un travail</div>

<p>Bonjour <strong>{{ $order->artisan->name }}</strong>,</p>
<p>
    <strong>{{ $order->client->name }}</strong> vous a envoyé une demande de commande sur ArtisanHub.
    Vous avez <strong>48 heures</strong> pour accepter ou refuser.
</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Commande</span>
        <span class="info-value">{{ $order->title }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Client</span>
        <span class="info-value">{{ $order->client->name }} · {{ $order->client->city }}</span>
    </div>
    @if($order->budget)
    <div class="info-row">
        <span class="info-label">Budget</span>
        <span class="info-value" style="color:#C4622D">{{ number_format($order->budget,0,',',' ') }} XOF</span>
    </div>
    @endif
    @if($order->deadline)
    <div class="info-row">
        <span class="info-label">Délai souhaité</span>
        <span class="info-value">{{ $order->deadline->format('d/m/Y') }}</span>
    </div>
    @endif
</div>

<p style="font-size:13px;color:#9A8070;background:#F5EFE6;padding:12px 16px;border-radius:8px;border-left:3px solid #C4622D">
    {{ \Str::limit($order->description, 200) }}
</p>

<a href="{{ route('artisan.orders.show', $order) }}" class="btn">
    Voir la commande →
</a>

<hr class="divider">
<p style="font-size:12px;color:#9A8070;text-align:center;margin:0">
    Si vous n'acceptez pas sous 48h, la demande sera automatiquement expirée.
</p>
@endsection
