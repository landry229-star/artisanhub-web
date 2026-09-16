<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Relevé de gains livreur</title>
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; font-size: 12px; }
        .header { margin-bottom: 20px; }
        h1 { font-size: 22px; margin: 0 0 8px; color: #1b1b1b; }
        .meta { color: #4b5563; line-height: 1.7; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        th { background: #f5efe6; }
        .total { margin-top: 20px; text-align: right; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Relevé de gains livreur</h1>
        <div class="meta">
            <div>Nom : {{ $user->name }}</div>
            <div>Période : {{ now()->startOfMonth()->format('d/m/Y') }} - {{ now()->endOfMonth()->format('d/m/Y') }}</div>
            <div>Email : {{ $user->email }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Commande</th>
                <th>Client</th>
                <th>Date</th>
                <th>Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($deliveries as $delivery)
                <tr>
                    <td>{{ $delivery->order->title }}</td>
                    <td>{{ $delivery->order->client->name }}</td>
                    <td>{{ $delivery->delivered_at?->format('d/m/Y') ?? $delivery->created_at->format('d/m/Y') }}</td>
                    <td>{{ number_format($delivery->fee, 0, ',', ' ') }} XOF</td>
                </tr>
            @empty
                <tr>
                    <td colspan="4">Aucune livraison terminée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="total">
        Total : {{ number_format($deliveries->sum('fee'), 0, ',', ' ') }} XOF
    </div>
</body>
</html>
