@extends('emails.layout')

@section('content')
<div class="title">Votre demande a bien été reçue</div>
<div class="subtitle">Nous vous répondrons sous 24h ouvrées</div>

<p>Bonjour <strong>{{ $name }}</strong>,</p>
<p>Nous avons bien reçu votre message concernant : <strong>{{ $subject }}</strong>.</p>
<p>Notre équipe de support va étudier votre demande et vous répondre dans les meilleurs délais.</p>

<a href="{{ route('support') }}" class="btn">Retour au support</a>
@endsection
