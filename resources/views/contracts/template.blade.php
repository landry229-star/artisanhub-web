<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1a1a1a; line-height: 1.6; }

  /* En-tête */
  .header { background: #2C1A0E; color: #fff; padding: 24px 32px; margin-bottom: 0; }
  .header-flex { display: table; width: 100%; }
  .header-left { display: table-cell; vertical-align: middle; }
  .header-right { display: table-cell; vertical-align: middle; text-align: right; }
  .logo { font-size: 20px; font-weight: bold; color: #fff; letter-spacing: 0.03em; }
  .logo span { color: #E8845A; }
  .logo-sub { font-size: 9px; color: #c4a882; letter-spacing: 0.1em; text-transform: uppercase; margin-top: 2px; }
  .contract-title { font-size: 13px; font-weight: bold; color: #E8845A; }
  .contract-ref { font-size: 10px; color: #c4a882; margin-top: 4px; }

  /* Bannière statut */
  .status-bar { background: #F5EFE6; border-left: 4px solid #C4622D; padding: 10px 32px; font-size: 10px; color: #5C3D1E; }

  /* Corps */
  .body { padding: 24px 32px; }

  /* Section */
  .section { margin-bottom: 20px; }
  .section-title { font-size: 10px; font-weight: bold; color: #C4622D; text-transform: uppercase; letter-spacing: 0.1em; border-bottom: 1px solid #ECD8C6; padding-bottom: 4px; margin-bottom: 12px; }

  /* Tableau infos */
  .info-table { width: 100%; border-collapse: collapse; }
  .info-table td { padding: 5px 8px; vertical-align: top; }
  .info-table .label { color: #9A8070; font-size: 10px; width: 35%; }
  .info-table .value { font-weight: bold; font-size: 11px; color: #1a1a1a; }

  /* Deux colonnes parties */
  .parties { display: table; width: 100%; border-collapse: separate; border-spacing: 12px 0; margin-bottom: 20px; }
  .partie { display: table-cell; width: 50%; background: #F5EFE6; border-radius: 6px; padding: 14px 16px; border: 1px solid #ECD8C6; }
  .partie-role { font-size: 9px; color: #C4622D; font-weight: bold; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px; }
  .partie-name { font-size: 13px; font-weight: bold; color: #2C1A0E; margin-bottom: 4px; }
  .partie-detail { font-size: 10px; color: #5C3D1E; }

  /* Encadré montant */
  .amount-box { background: #2C1A0E; color: #fff; border-radius: 8px; padding: 16px 20px; margin: 16px 0; text-align: center; }
  .amount-label { font-size: 9px; color: #c4a882; text-transform: uppercase; letter-spacing: 0.1em; }
  .amount-value { font-size: 24px; font-weight: bold; color: #E8845A; margin: 4px 0 2px; }
  .amount-sub { font-size: 10px; color: #c4a882; }

  /* Clauses */
  .clause { margin-bottom: 10px; padding-left: 12px; border-left: 2px solid #ECD8C6; }
  .clause-num { font-weight: bold; color: #C4622D; }

  /* Signatures */
  .signatures { display: table; width: 100%; margin-top: 24px; }
  .sig-box { display: table-cell; width: 45%; text-align: center; padding: 0 8px; }
  .sig-spacer { display: table-cell; width: 10%; }
  .sig-line { border-top: 1px solid #1a1a1a; margin-top: 40px; padding-top: 6px; font-size: 9px; color: #9A8070; }
  .sig-name { font-size: 11px; font-weight: bold; color: #2C1A0E; margin-top: 2px; }

  /* Pied de page */
  .footer { position: fixed; bottom: 0; left: 0; right: 0; background: #F5EFE6; padding: 8px 32px; border-top: 1px solid #ECD8C6; font-size: 9px; color: #9A8070; display: table; width: 100%; }
  .footer-left { display: table-cell; }
  .footer-right { display: table-cell; text-align: right; }

  /* Watermark légal */
  .legal-note { background: #FFF9F0; border: 1px solid #ECD8C6; border-radius: 6px; padding: 10px 14px; margin-top: 16px; font-size: 9px; color: #9A8070; line-height: 1.5; }
</style>
</head>
<body>

{{-- En-tête --}}
<div class="header">
  <div class="header-flex">
    <div class="header-left">
      <div class="logo">Artisan<span>Hub</span></div>
      <div class="logo-sub">Plateforme d'artisanat du Bénin</div>
    </div>
    <div class="header-right">
      <div class="contract-title">CONTRAT DE PRESTATION</div>
      <div class="contract-ref">Réf. {{ $reference }} · Généré le {{ $generated_at }}</div>
    </div>
  </div>
</div>

{{-- Statut --}}
<div class="status-bar">
  Ce contrat est généré automatiquement par ArtisanHub lors de l'acceptation de la commande.
  Il constitue l'accord de prestation entre les parties et vaut engagement contractuel.
</div>

<div class="body">

  {{-- Parties --}}
  <div class="section">
    <div class="section-title">Parties au contrat</div>
    <div class="parties">
      <div class="partie">
        <div class="partie-role">🔨 Prestataire (Artisan)</div>
        <div class="partie-name">{{ $order->artisan->name }}</div>
        <div class="partie-detail">
          {{ $order->artisan->artisanProfile->specialty ?? 'Artisan' }}<br>
          📍 {{ $order->artisan->city }}<br>
          📞 {{ $order->artisan->phone ?? 'Non renseigné' }}<br>
          ✉️ {{ $order->artisan->email }}
        </div>
      </div>
      <div class="partie">
        <div class="partie-role">👤 Donneur d'ordre (Client)</div>
        <div class="partie-name">{{ $order->client->name }}</div>
        <div class="partie-detail">
          Client ArtisanHub<br>
          📍 {{ $order->client->city }}<br>
          📞 {{ $order->client->phone ?? 'Non renseigné' }}<br>
          ✉️ {{ $order->client->email }}
        </div>
      </div>
    </div>
  </div>

  {{-- Détails de la prestation --}}
  <div class="section">
    <div class="section-title">Objet de la prestation</div>
    <table class="info-table">
      <tr>
        <td class="label">Titre</td>
        <td class="value">{{ $order->title }}</td>
      </tr>
      <tr>
        <td class="label">Description</td>
        <td class="value">{{ $order->description }}</td>
      </tr>
      @if($order->service)
      <tr>
        <td class="label">Service concerné</td>
        <td class="value">{{ $order->service->name }}</td>
      </tr>
      @endif
      <tr>
        <td class="label">Date de commande</td>
        <td class="value">{{ $order->created_at->format('d/m/Y') }}</td>
      </tr>
      @if($order->deadline)
      <tr>
        <td class="label">Délai d'exécution</td>
        <td class="value">{{ $order->deadline->format('d/m/Y') }}</td>
      </tr>
      @endif
      @if($order->needs_delivery)
      <tr>
        <td class="label">Livraison</td>
        <td class="value">Oui — Ville : {{ $order->delivery_city }}</td>
      </tr>
      @endif
    </table>
  </div>

  {{-- Montant --}}
  @if($order->budget)
  <div class="amount-box">
    <div class="amount-label">Montant convenu</div>
    <div class="amount-value">{{ number_format($order->budget, 0, ',', ' ') }} XOF</div>
    <div class="amount-sub">Paiement sécurisé via FedaPay (MTN Mobile Money / Moov Money)<br>
    Le paiement est déclenché après validation de la livraison par le client.</div>
  </div>
  @endif

  {{-- Clauses --}}
  <div class="section">
    <div class="section-title">Clauses contractuelles</div>

    <div class="clause">
      <span class="clause-num">Art. 1 — Engagement de l'artisan.</span>
      L'artisan s'engage à réaliser la prestation décrite ci-dessus avec soin et professionnalisme,
      dans les délais convenus, et à informer le client de tout retard ou difficulté.
    </div>

    <div class="clause">
      <span class="clause-num">Art. 2 — Engagement du client.</span>
      Le client s'engage à fournir toutes les informations nécessaires à la réalisation de la
      prestation, à valider honnêtement la livraison, et à effectuer le paiement convenu via ArtisanHub.
    </div>

    <div class="clause">
      <span class="clause-num">Art. 3 — Paiement et séquestre.</span>
      Le montant convenu est réglé par le client via la plateforme ArtisanHub après validation
      de la livraison. Les fonds sont versés à l'artisan après déduction de la commission de la
      plateforme (5% à 10% selon le barème en vigueur).
    </div>

    <div class="clause">
      <span class="clause-num">Art. 4 — Litiges.</span>
      En cas de différend, les parties s'engagent à utiliser la procédure de médiation d'ArtisanHub
      avant tout recours judiciaire. ArtisanHub rendra une décision sous 48h ouvrées.
    </div>

    <div class="clause">
      <span class="clause-num">Art. 5 — Droit applicable.</span>
      Le présent contrat est soumis au droit béninois. Tout litige non résolu par médiation
      relèvera de la compétence des tribunaux de Cotonou, Bénin.
    </div>
  </div>

  {{-- Signatures --}}
  <div class="section">
    <div class="section-title">Signatures électroniques</div>
    <div class="signatures">
      <div class="sig-box">
        <div class="sig-line">Signature de l'artisan</div>
        <div class="sig-name">{{ $order->artisan->name }}</div>
        <div style="font-size:9px;color:#9A8070;">Accepté le {{ $order->updated_at->format('d/m/Y') }}</div>
      </div>
      <div class="sig-spacer"></div>
      <div class="sig-box">
        <div class="sig-line">Signature du client</div>
        <div class="sig-name">{{ $order->client->name }}</div>
        <div style="font-size:9px;color:#9A8070;">Par sa demande du {{ $order->created_at->format('d/m/Y') }}</div>
      </div>
    </div>
  </div>

  {{-- Note légale --}}
  <div class="legal-note">
    📋 <strong>Note légale :</strong> Ce document est généré automatiquement par ArtisanHub et constitue
    un accord contractuel entre les parties. La référence {{ $reference }} permet de retrouver ce contrat
    dans votre espace ArtisanHub. ArtisanHub SAS, Cotonou, Bénin — contact@artisanhub.bj
  </div>

</div>

{{-- Pied de page --}}
<div class="footer">
  <div class="footer-left">ArtisanHub · Plateforme d'artisanat béninois · contact@artisanhub.bj</div>
  <div class="footer-right">Réf. {{ $reference }} · Page <span class="pagenum"></span></div>
</div>

</body>
</html>
