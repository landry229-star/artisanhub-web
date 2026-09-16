@extends('layouts.app')
@section('title', 'Politique de confidentialité')
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5" style="max-width:820px">
    <div class="mb-5 text-center">
        <p style="color:#C4622D;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase" class="mb-2">LÉGAL</p>
        <h1 style="font-family:'Playfair Display',serif;font-size:2rem">Politique de confidentialité</h1>
        <p class="text-muted">Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>

    @php
    $sections = [
        ['title'=>'1. Données collectées',
         'content'=>'ArtisanHub collecte les données suivantes : informations d\'identité (nom, email, téléphone, ville), données de profil (spécialité, photos, bio pour les artisans), données de transactions (montants, statuts de commandes), et données de navigation (pages visitées, actions effectuées). Nous ne collectons pas de données bancaires.'],

        ['title'=>'2. Utilisation des données',
         'content'=>'Vos données sont utilisées pour : créer et gérer votre compte, faciliter la mise en relation artisans-clients, traiter les paiements via FedaPay, envoyer des notifications et emails transactionnels, améliorer nos services, et détecter les fraudes.'],

        ['title'=>'3. Partage des données',
         'content'=>'Nous ne vendons jamais vos données. Nous partageons uniquement les informations nécessaires avec : FedaPay (pour le traitement des paiements), Mailgun (pour l\'envoi d\'emails transactionnels). Ces partenaires sont contractuellement tenus de protéger vos données.'],

        ['title'=>'4. Conservation des données',
         'content'=>'Vos données sont conservées pendant la durée de votre compte, plus 3 ans après sa suppression (obligations légales). Les messages sont conservés 1 an. Les données de paiement sont conservées 10 ans (obligation fiscale).'],

        ['title'=>'5. Vos droits',
         'content'=>'Vous disposez du droit d\'accès, de rectification et de suppression de vos données. Pour exercer ces droits, contactez-nous à privacy@artisanhub.bj. Nous répondons sous 30 jours. Vous pouvez supprimer votre compte à tout moment depuis les paramètres.'],

        ['title'=>'6. Cookies',
         'content'=>'ArtisanHub utilise uniquement des cookies essentiels au fonctionnement : cookie de session (connexion), cookie CSRF (sécurité). Nous n\'utilisons pas de cookies publicitaires ou de tracking tiers.'],

        ['title'=>'7. Sécurité',
         'content'=>'Nous protégeons vos données via : chiffrement HTTPS sur toutes les pages, hachage bcrypt des mots de passe, protection CSRF sur tous les formulaires, accès aux données limité aux employés autorisés, sauvegardes quotidiennes chiffrées.'],

        ['title'=>'8. Contact',
         'content'=>'Délégué à la Protection des Données : privacy@artisanhub.bj · ArtisanHub, Cotonou, Bénin.'],
    ];
    @endphp

    @foreach($sections as $section)
        <div class="mb-4 p-4 rounded-3" style="background:#fff;border:1px solid #ECD8C6">
            <h5 class="fw-700 mb-2" style="color:#C4622D">{{ $section['title'] }}</h5>
            <p class="mb-0 text-muted" style="line-height:1.8;font-size:.95rem">
                {{ $section['content'] }}
            </p>
        </div>
    @endforeach

    <div class="text-center mt-5">
        <a href="{{ route('home') }}" class="btn btn-clay px-4">
            <i class="bi bi-house me-2"></i>Retour à l'accueil
        </a>
    </div>
</div>
@endsection
