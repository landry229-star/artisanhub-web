@extends('emails.layout')
@section('content')
<div class="title">Réinitialisez votre mot de passe 🔑</div>
<div class="subtitle">Vous avez demandé une réinitialisation</div>

<p>Bonjour <strong>{{ $user->name }}</strong>,</p>
<p>
    Vous avez demandé à réinitialiser votre mot de passe sur ArtisanHub.
    Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe.
</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Compte</span>
        <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Validité</span>
        <span class="info-value">60 minutes</span>
    </div>
</div>

<a href="{{ $url }}" class="btn">Réinitialiser mon mot de passe →</a>

<hr class="divider">
<p style="font-size:12px;color:#9A8070;text-align:center">
    Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.<br>
    Votre mot de passe ne sera pas modifié.
</p>
<p style="font-size:11px;color:#9A8070;text-align:center;word-break:break-all">
    Lien : <span style="color:#C4622D">{{ $url }}</span>
</p>
@endsection
