@extends('emails.layout')
@section('content')
<div class="title">Vérifiez votre adresse email 📧</div>
<div class="subtitle">Une dernière étape avant de commencer</div>

<p>Bonjour <strong>{{ $user->name }}</strong>,</p>
<p>
    Merci de rejoindre ArtisanHub ! Cliquez sur le bouton ci-dessous
    pour confirmer votre adresse email et activer votre compte.
</p>

<div class="info-box">
    <div class="info-row">
        <span class="info-label">Compte</span>
        <span class="info-value">{{ $user->email }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Rôle</span>
        <span class="info-value">{{ ucfirst($user->role) }}</span>
    </div>
    <div class="info-row">
        <span class="info-label">Validité du lien</span>
        <span class="info-value">24 heures</span>
    </div>
</div>

<a href="{{ $url }}" class="btn">
    ✅ Vérifier mon adresse email →
</a>

<hr class="divider">
<p style="font-size:12px;color:#9A8070;text-align:center">
    Si vous n'avez pas créé de compte sur ArtisanHub, ignorez cet email.<br>
    Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :
</p>
<p style="font-size:11px;color:#C4622D;text-align:center;word-break:break-all">
    {{ $url }}
</p>
@endsection
