@extends('layouts.app')
@section('title', 'Conditions Générales d\'Utilisation')
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5" style="max-width:820px">
    <div class="mb-5 text-center">
        <p style="color:#C4622D;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase" class="mb-2">LÉGAL</p>
        <h1 style="font-family:'Playfair Display',serif;font-size:2rem">Conditions Générales d'Utilisation</h1>
        <p class="text-muted">Dernière mise à jour : {{ date('d/m/Y') }} · En vigueur au Bénin 🇧🇯</p>
    </div>

    @php
    $sections = [
        ['title'=>'1. Présentation d\'ArtisanHub',
         'content'=>'ArtisanHub est une plateforme numérique de mise en relation entre artisans béninois et clients, éditée et exploitée depuis le Bénin. La plateforme permet aux clients de trouver des artisans qualifiés, de passer des commandes sur mesure et de payer de façon sécurisée via Mobile Money (FedaPay).'],

        ['title'=>'2. Acceptation des CGU',
         'content'=>'L\'utilisation d\'ArtisanHub implique l\'acceptation pleine et entière des présentes conditions. Si vous n\'acceptez pas ces conditions, vous ne devez pas utiliser la plateforme. ArtisanHub se réserve le droit de modifier les CGU à tout moment, avec notification par email.'],

        ['title'=>'3. Inscription et comptes',
         'content'=>'L\'inscription est gratuite et ouverte à toute personne physique résidant au Bénin, âgée d\'au moins 18 ans. Vous êtes responsable de la confidentialité de vos identifiants. Toute activité sous votre compte vous est imputable. ArtisanHub peut suspendre ou supprimer tout compte en cas de violation des présentes CGU.'],

        ['title'=>'4. Rôles et responsabilités',
         'content'=>'Artisan : Il s\'engage à réaliser les prestations commandées avec soin, dans les délais convenus et conformément à la description publiée. Client : Il s\'engage à fournir des informations exactes, à valider honnêtement les livraisons et à payer les prestations validées. Livreur : Il s\'engage à assurer les livraisons dans les délais, à manipuler les objets avec soin et à signaler tout problème immédiatement.'],

        ['title'=>'5. Commissions et paiements',
         'content'=>'ArtisanHub prélève une commission sur chaque commande complétée, selon le barème progressif suivant : 5% pour un chiffre d\'affaires mensuel de 0 à 49 999 XOF, 8% de 50 000 à 199 999 XOF, et 10% au-delà de 200 000 XOF. Les paiements sont traités via FedaPay (MTN Mobile Money, Moov Money). ArtisanHub ne conserve aucun numéro de carte bancaire.'],

        ['title'=>'6. Litiges et arbitrage',
         'content'=>'En cas de litige entre un client et un artisan, ArtisanHub propose un service de médiation. L\'équipe ArtisanHub examine les preuves fournies par les deux parties et rend une décision sous 48 heures ouvrées. La décision d\'arbitrage est définitive. ArtisanHub peut décider de rembourser le client ou de payer l\'artisan selon les faits établis.'],

        ['title'=>'7. Comportements interdits',
         'content'=>'Il est formellement interdit de : publier de fausses informations sur son profil, contacter des utilisateurs en dehors de la plateforme pour éviter les commissions, utiliser ArtisanHub à des fins illicites, harceler ou menacer d\'autres utilisateurs, créer plusieurs comptes, ou tenter de pirater la plateforme.'],

        ['title'=>'8. Propriété intellectuelle',
         'content'=>'Le contenu publié sur ArtisanHub (photos de portfolio, descriptions de services) reste la propriété de son auteur. En le publiant, vous accordez à ArtisanHub une licence non exclusive pour l\'afficher sur la plateforme à des fins promotionnelles.'],

        ['title'=>'9. Limitation de responsabilité',
         'content'=>'ArtisanHub est une plateforme d\'intermédiation. Elle ne peut être tenue responsable de la qualité des prestations fournies par les artisans, des retards de livraison, ou des dommages indirects. ArtisanHub s\'engage à maintenir la plateforme disponible 99% du temps, hors maintenance planifiée.'],

        ['title'=>'10. Droit applicable',
         'content'=>'Les présentes CGU sont régies par le droit béninois. En cas de litige non résolu par la médiation ArtisanHub, les tribunaux compétents de Cotonou (Bénin) seront saisis.'],

        ['title'=>'11. Contact',
         'content'=>'Pour toute question relative aux présentes CGU, contactez-nous à : legal@artisanhub.bj ou via le formulaire de contact sur notre site.'],
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
