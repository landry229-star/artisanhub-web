<!doctype html>
<html lang="fr"><head><meta charset="utf-8"><style>
body{font-family:DejaVu Sans,sans-serif;color:#2C1A0E;font-size:12px}h1{color:#C4622D}.box{border:1px solid #ECD8C6;padding:16px;margin-top:20px}.row{padding:8px 0;border-bottom:1px solid #F5EFE6}.muted{color:#765F51}
</style></head><body>
<h1>ArtisanHub</h1><h2>Facture / justificatif de paiement</h2><p class="muted">Document n° {{ str_pad((string)$payment->id,8,'0',STR_PAD_LEFT) }}</p>
<div class="box"><div class="row"><span class="muted">Client</span><br>{{ $client->name }}</div>
<div class="row"><span class="muted">Artisan</span><br>{{ $payment->order->artisan->name }}</div>
<div class="row"><span class="muted">Commande</span><br>{{ $payment->order->title }} (#{{ $payment->order_id }})</div>
<div class="row"><span class="muted">Date</span><br>{{ $payment->paid_at?->format('d/m/Y à H:i') ?? '—' }}</div>
<div class="row"><strong>Montant payé</strong><br><strong>{{ number_format($payment->amount,0,',',' ') }} XOF</strong></div>
<div class="row"><span class="muted">Statut</span><br>{{ $payment->status === 'refunded' ? 'Remboursé' : 'Paiement confirmé' }}</div></div>
</body></html>
