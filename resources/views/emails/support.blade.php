@extends('emails.layout')

@section('content')
<div class="title">Nouveau message support</div>
<div class="subtitle">Demande reçue depuis le formulaire de contact</div>

<p>Bonjour,</p>
<p>Une demande a été envoyée depuis le formulaire de support ArtisanHub.</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Nom</span>
        <span class="info-value">{{ $senderName }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Email</span>
        <span class="info-value">{{ $senderEmail }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Sujet</span>
        <span class="info-value">{{ $subject }}</span>
    </div>
    @if(!empty($userId))
        <div class="info-row">
            <span class="info-label">Utilisateur</span>
            <span class="info-value">#{{ $userId }}</span>
        </div>
    @endif
</div>

<p><strong>Message :</strong></p>
<p>{{ $userMessage }}</p>
@endsection
