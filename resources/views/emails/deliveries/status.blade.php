@extends('emails.layout')
@section('content')
@php
    $titles = [
        'accepted'  => ['Votre livraison est prise en charge 🚴', 'Le livreur est en route vers l\'artisan.'],
        'picked_up' => ['Votre objet est en route 📦', 'Le livreur a récupéré l\'objet et se dirige vers vous.'],
        'delivered' => ['Livraison effectuée ✅', 'Votre commande a été livrée avec succès.'],
        'failed'    => ['Problème de livraison ⚠️', 'Le livreur a signalé un problème. Un autre livreur va être assigné.'],
    ];
    $info = $titles[$event] ?? ['Mise à jour livraison', ''];
@endphp

<div class="title">{{ $info[0] }}</div>
<div class="subtitle">{{ $info[1] }}</div>

<p>Bonjour <strong>{{ $notifiable->name }}</strong>,</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Commande</span>
        <span class="info-value">{{ $delivery->order->title }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Livreur</span>
        <span class="info-value">{{ $delivery->livreur?->name ?? '—' }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Trajet</span>
        <span class="info-value">{{ $delivery->pickup_city }} → {{ $delivery->delivery_city }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Statut</span>
        <span class="info-value">{{ $delivery->statusLabel() }}</span>
    </div>
</div>

@if($event === 'delivered')
    <p>Le client doit maintenant valider la livraison pour déclencher le paiement.</p>
@endif

<a href="{{ route(auth()->user()?->isClient() ? 'client.orders.show' : 'artisan.orders.show', $delivery->order) }}"
   class="btn">Voir la commande →</a>
@endsection
