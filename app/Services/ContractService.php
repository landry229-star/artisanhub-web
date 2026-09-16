<?php
// ══════════════════════════════════════════════════════════════════════════════
// app/Services/ContractService.php — Contrats en PDF (remplace HTML)
// ══════════════════════════════════════════════════════════════════════════════
//
// INSTALLATION :
//   composer require barryvdh/laravel-dompdf
//   php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
//
// ══════════════════════════════════════════════════════════════════════════════

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ContractService
{
    public function generate(Order $order): string
    {
        $order->load('client', 'artisan.artisanProfile', 'service');

        $pdf = Pdf::loadView('contracts.template', [
            'order'       => $order,
            'generated_at' => now()->format('d/m/Y à H:i'),
            'reference'   => 'AH-' . str_pad($order->id, 6, '0', STR_PAD_LEFT),
        ]);

        // Format A4, portrait
        $pdf->setPaper('a4', 'portrait');

        // Options de rendu
        $pdf->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled'      => false, // sécurité : pas de ressources externes
            'defaultFont'          => 'DejaVu Sans',
            'dpi'                  => 150,
        ]);

        $filename = "contracts/contrat-commande-{$order->id}.pdf";
        Storage::disk('local')->put($filename, $pdf->output());

        return $filename;
    }
}
