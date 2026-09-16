@extends('layouts.app')
@section('title', 'Politique de remboursement')
@section('robots', 'noindex, follow')

@section('content')
<div class="container py-5" style="max-width:820px">
    <div class="mb-5 text-center">
        <p style="color:#C4622D;font-size:.75rem;font-weight:700;letter-spacing:.12em;text-transform:uppercase" class="mb-2">LÉGAL</p>
        <h1 style="font-family:'Playfair Display',serif;font-size:2rem">Politique de remboursement</h1>
        <p class="text-muted">En vigueur au Bénin · Dernière mise à jour : {{ date('d/m/Y') }}</p>
    </div>

    {{-- Résumé visuel --}}
    <div class="row g-3 mb-5">
        @php
        $cards = [
            ['icon'=>'✅', 'color'=>'#D4EDDA', 'text_color'=>'#155724', 'title'=>'Remboursement possible', 'desc'=>'Si l\'artisan n\'a pas commencé le travail'],
            ['icon'=>'⚖️', 'color'=>'#FFF3CD', 'text_color'=>'#856404', 'title'=>'Médiation gratuite', 'desc'=>'En cas de litige, sous 48h ouvrées'],
            ['icon'=>'❌', 'color'=>'#F8D7DA', 'text_color'=>'#721C24', 'title'=>'Non remboursable', 'desc'=>'Après validation de la livraison par le client'],
        ];
        @endphp
        @foreach($cards as $card)
        <div class="col-md-4">
            <div class="p-3 rounded-3 h-100 text-center" style="background:{{ $card['color'] }}">
                <div style="font-size:2rem">{{ $card['icon'] }}</div>
                <div style="font-weight:700;color:{{ $card['text_color'] }};margin:8px 0 4px">{{ $card['title'] }}</div>
                <div style="font-size:.85rem;color:{{ $card['text_color'] }};opacity:.85">{{ $card['desc'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="card border-0 shadow-sm p-4 p-md-5">

        @php
        $sections = [
            [
                'title'   => '1. Principe général',
                'content' => 'ArtisanHub utilise un système de paiement à la validation : le client ne paie qu\'après avoir confirmé que la prestation a été livrée conformément à sa demande. Ce mécanisme protège les deux parties : le client ne paie que ce qu\'il a reçu, l\'artisan est assuré d\'être payé dès validation.'
            ],
            [
                'title'   => '2. Cas de remboursement total (100%)',
                'content' => '
                    Un remboursement complet est accordé dans les cas suivants :<br><br>
                    <ul style="padding-left:20px;margin-bottom:0">
                        <li>L\'artisan refuse ou annule la commande avant d\'avoir commencé le travail</li>
                        <li>L\'artisan ne donne aucune réponse sous 48h après acceptation</li>
                        <li>La prestation n\'a pas été réalisée du tout (artisan introuvable, abandon)</li>
                        <li>Erreur technique avérée sur le paiement (double débit, montant incorrect)</li>
                    </ul>
                '
            ],
            [
                'title'   => '3. Cas de remboursement partiel',
                'content' => '
                    Un remboursement partiel peut être accordé si :<br><br>
                    <ul style="padding-left:20px;margin-bottom:0">
                        <li>La prestation a été partiellement réalisée mais présente des défauts majeurs non corrigés</li>
                        <li>La médiation ArtisanHub conclut à un partage de responsabilité</li>
                        <li>Un accord amiable est trouvé entre le client et l\'artisan</li>
                    </ul><br>
                    Le montant remboursé est déterminé par l\'équipe de médiation ArtisanHub après examen des preuves.
                '
            ],
            [
                'title'   => '4. Cas de non-remboursement',
                'content' => '
                    Aucun remboursement n\'est accordé si :<br><br>
                    <ul style="padding-left:20px;margin-bottom:0">
                        <li>Le client a déjà validé la livraison (la validation vaut acceptation définitive)</li>
                        <li>Le client a changé d\'avis sur la prestation sans motif lié à sa qualité</li>
                        <li>Le litige est signalé plus de 7 jours après la livraison sans raison valable</li>
                        <li>Les preuves fournies montrent que la prestation a été correctement réalisée</li>
                    </ul>
                '
            ],
            [
                'title'   => '5. Procédure de demande de remboursement',
                'content' => '
                    Pour demander un remboursement :<br><br>
                    <ol style="padding-left:20px;margin-bottom:0">
                        <li><strong>Étape 1</strong> : Utilisez le bouton "Signaler un litige" sur la page de votre commande</li>
                        <li><strong>Étape 2</strong> : Décrivez le problème et joignez des preuves (photos, messages)</li>
                        <li><strong>Étape 3</strong> : L\'équipe ArtisanHub examine le dossier sous 48h ouvrées</li>
                        <li><strong>Étape 4</strong> : Une décision est rendue et communiquée par email aux deux parties</li>
                        <li><strong>Étape 5</strong> : Si remboursement accordé, il est effectué sous 3 à 5 jours ouvrés via Mobile Money</li>
                    </ol>
                '
            ],
            [
                'title'   => '6. Délais de remboursement',
                'content' => 'Une fois le remboursement accordé par ArtisanHub, les fonds sont retournés sur le compte Mobile Money utilisé lors du paiement sous <strong>3 à 5 jours ouvrés</strong>. Les délais peuvent varier selon l\'opérateur (MTN ou Moov). ArtisanHub ne peut être tenu responsable des délais propres aux opérateurs de téléphonie.'
            ],
            [
                'title'   => '7. Commission non remboursable',
                'content' => 'En cas de remboursement partiel suite à une médiation, la commission prélevée par ArtisanHub (5% à 10%) n\'est pas remboursée, car elle couvre les coûts de la mise en relation et du service de médiation.'
            ],
            [
                'title'   => '8. Contact pour les remboursements',
                'content' => '
                    Pour toute question relative à un remboursement :<br><br>
                    📧 Email : remboursement@artisanhub.bj<br>
                    💬 Via le formulaire de contact : <a href="' . route('support') . '" style="color:#C4622D">artisanhub.bj/support</a><br>
                    ⏰ Délai de réponse : sous 24h ouvrées
                '
            ],
        ];
        @endphp

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
        <a href="{{ route('mentions') }}" class="btn btn-outline-secondary btn-sm me-2">Mentions légales</a>
        <a href="{{ route('support') }}" class="btn btn-outline-secondary btn-sm">Support</a>
    </div>
</div>
@endsection
