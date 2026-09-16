<!doctype html><html lang="fr"><head><meta charset="utf-8"><style>body{font-family:DejaVu Sans;font-size:10px}h1{color:#9E4A1E}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:5px;text-align:left}th{background:#F5EFE6}</style></head><body>
<h1>Utilisateurs ArtisanHub</h1><table><thead><tr><th>ID</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Ville</th><th>Vérifié</th><th>Actif</th><th>Inscrit le</th></tr></thead><tbody>
@foreach($rows as $u)<tr><td>{{ $u->id }}</td><td>{{ $u->name }}</td><td>{{ $u->email }}</td><td>{{ $u->role }}</td><td>{{ $u->city }}</td><td>{{ $u->is_verified ? 'Oui' : 'Non' }}</td><td>{{ $u->is_active ? 'Oui' : 'Non' }}</td><td>{{ $u->created_at?->format('d/m/Y') }}</td></tr>@endforeach
</tbody></table></body></html>
