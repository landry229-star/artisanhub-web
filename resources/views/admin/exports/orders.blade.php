<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans;font-size:10px}h1{color:#9E4A1E}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:5px;text-align:left}th{background:#F5EFE6}</style></head><body>
<h1>Commandes ArtisanHub</h1><table><thead><tr><th>ID</th><th>Titre</th><th>Client</th><th>Artisan</th><th>Budget</th><th>Statut</th><th>Date</th></tr></thead><tbody>
@foreach($rows as $o)<tr><td>{{ $o->id }}</td><td>{{ $o->title }}</td><td>{{ $o->client?->name }}</td><td>{{ $o->artisan?->name }}</td><td>{{ $o->budget }}</td><td>{{ $o->statusLabel() }}</td><td>{{ $o->created_at?->format('d/m/Y') }}</td></tr>@endforeach
</tbody></table></body></html>
