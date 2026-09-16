@extends('emails.layout')
@section('content')
<div class="title">Commande annulée ❌</div>
<div class="subtitle">Une commande vous concernant a été annulée</div>

<p>Bonjour <strong>{{ $notifiable->name }}</strong>,</p>
<p>La commande suivante a été annulée.</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Commande</span>
        <span class="info-value">{{ $order->title }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Client</span>
        <span class="info-value">{{ $order->client->name }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Artisan</span>
        <span class="info-value">{{ $order->artisan->name }}</span>
    </div>
</div>

<p style="font-size:13px;color:#9A8070">
    Si vous avez des questions, n'hésitez pas à contacter notre support.
</p>
<a href="{{ route('support') }}" class="btn">Contacter le support</a>
@endsection
