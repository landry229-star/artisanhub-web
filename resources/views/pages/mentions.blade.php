@extends('layouts.app')
@section('title', 'Mentions légales')
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5" style="max-width:820px">
    <div class="mb-5 text-center">
        <p style="color:#C4622D;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase" class="mb-2">LÉGAL</p>
        <h1 style="font-family:'Playfair Display',serif;font-size:2rem">Mentions légales</h1>
        <p class="text-muted">Conformément aux lois en vigueur au Bénin · Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>

    @php
    $sections = [
        [
            'title'   => '1. Éditeur de la plateforme',
            'content' => '
                <strong>ArtisanHub</strong><br>
                Forme juridique : [Entreprise Individuelle / SARL — à compléter]<br>
                Adresse du siège : [Atlantique, Cotonou, Bénin]<br>
                Téléphone : [+2290161871819]<br>
                Email : artisanhub@gmail.com<br>
                Numéro RCCM : [XX/XXX/XX — à compléter]<br>
                IFU : [XXXXXXXXXX — à compléter]<br><br>
                Directeur de la publication : [Votre nom complet]
            '
        ],
        [
            'title'   => '2. Hébergement',
            'content' => '
                La plateforme ArtisanHub est hébergée par :<br><br>
                <strong>[Nom de votre hébergeur — ex: OVH, Hostinger, DigitalOcean]</strong><br>
                Adresse : [Adresse de l\'hébergeur]<br>
                Site web : [https://www.hebergeur.com]<br>
                Téléphone : [Numéro de l\'hébergeur]
            '
        ],
        [
            'title'   => '3. Propriété intellectuelle',
            'content' => 'L\'ensemble des contenus présents sur ArtisanHub (textes, graphismes, logo, icônes, images, sons, vidéos) sont la propriété exclusive d\'ArtisanHub ou de ses partenaires, et sont protégés par les lois applicables en matière de propriété intellectuelle. Toute reproduction, représentation, modification, publication ou adaptation, totale ou partielle, est interdite sans autorisation écrite préalable d\'ArtisanHub.'
        ],
        [
            'title'   => '4. Données personnelles',
            'content' => 'ArtisanHub collecte et traite des données personnelles dans le cadre de la fourniture de ses services. Ces traitements sont effectués conformément à notre <a href="' . route('privacy') . '" style="color:#C4622D">Politique de confidentialité</a>. Vous disposez d\'un droit d\'accès, de rectification et de suppression de vos données. Pour exercer ces droits, contactez-nous à : contact@artisanhub.bj'
        ],
        [
            'title'   => '5. Cookies',
            'content' => 'ArtisanHub utilise des cookies strictement nécessaires au fonctionnement de la plateforme (session, sécurité CSRF). Aucun cookie publicitaire ou de tracking tiers n\'est utilisé. En continuant à naviguer sur ArtisanHub, vous acceptez l\'utilisation de ces cookies techniques.'
        ],
        [
            'title'   => '6. Limitation de responsabilité',
            'content' => 'ArtisanHub est une plateforme d\'intermédiation qui met en relation des artisans et des clients. ArtisanHub ne peut être tenue responsable de la qualité des prestations fournies par les artisans, des retards ou dommages lors des livraisons, ni de tout préjudice indirect résultant de l\'utilisation de la plateforme. ArtisanHub met tout en œuvre pour assurer la disponibilité de ses services, mais ne peut garantir une disponibilité ininterrompue.'
        ],
        [
            'title'   => '7. Droit applicable et juridiction',
            'content' => 'Les présentes mentions légales sont soumises au droit béninois. En cas de litige relatif à l\'interprétation ou à l\'exécution des présentes, les tribunaux compétents de Cotonou, Bénin, seront seuls compétents, sauf disposition légale contraire.'
        ],
        [
            'title'   => '8. Contact',
            'content' => '
                Pour toute question relative aux présentes mentions légales :<br><br>
                📧 Email : contact@artisanhub.bj<br>
                📞 Téléphone : [+229 XX XX XX XX]<br>
                📍 Adresse : [Votre adresse, Cotonou, Bénin]
            '
        ],
    ];
    @endphp

    <div class="card border-0 shadow-sm p-4 p-md-5">
        @foreach($sections as $section)
        <div class="mb-4">
            <h2 style="font-size:1.1rem;font-weight:700;color:#2C1A0E;border-left:3px solid #C4622D;padding-left:12px;margin-bottom:12px">
                {{ $section['title'] }}
            </h2>
            <div style="color:#5C3D1E;line-height:1.8;font-size:.95rem">
                {!! $section['content'] !!}
            </div>
        </div>
        @if(!$loop->last)
            <hr style="border-color:#ECD8C6;margin:24px 0">
        @endif
        @endforeach
    </div>

    <div class="text-center mt-4">
        <a href="{{ route('cgu') }}" class="btn btn-outline-secondary btn-sm me-2">CGU</a>
        <a href="{{ route('privacy') }}" class="btn btn-outline-secondary btn-sm me-2">Confidentialité</a>
        <a href="{{ route('remboursement') }}" class="btn btn-outline-secondary btn-sm">Remboursement</a>
    </div>
</div>
@endsection
