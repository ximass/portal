<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PdfController extends Controller
{
    public function generateOrderPdf($id)
    {
        $order = Order::with([
            'customer.state',
            'sets.setParts.material',
            'sets.setParts.sheet',
            'sets.setParts.bar',
            'sets.setParts.component',
            'sets.setParts.ncm'
        ])->findOrFail($id);

        $totalValue = 0;
        $totalIpi = 0;
        $totalIcms = 0;
        
        foreach ($order->sets as $set) {
            foreach ($set->setParts as $part) {
                $totalValue += $part->final_value ?? 0;
                $totalIpi   += $part->total_ipi_value ?? 0;
                $totalIcms  += $part->total_icms_value ?? 0;
            }
        }

        $totalGeral = $totalValue + ($order->delivery_value ?? 0);

        $data = [
            'order' => $order,
            'totalIpi' => $totalIpi,
            'totalIcms' => $totalIcms,
            'totalGeral' => $totalGeral,
            'createdDate' => now()->format('d/m/Y'),
            'orderNumber' => str_pad($order->id, 6, '0', STR_PAD_LEFT)
        ];

        $pdf = Pdf::loadView('pdf.order', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("orcamento-{$data['orderNumber']}.pdf");
    }

    public function generateOrderSetsPdf($id)
    {
        $order = Order::with([
            'customer.state',
            'sets.setParts.material',
            'sets.setParts.sheet',
            'sets.setParts.bar',
            'sets.setParts.component',
            'sets.setParts.ncm'
        ])->findOrFail($id);

        $totalValue = 0;
        
        foreach ($order->sets as $set) {
            foreach ($set->setParts as $part) {
                $totalValue += $part->final_value ?? 0;
            }
        }

        $totalGeral = $totalValue + ($order->delivery_value ?? 0);

        $data = [
            'order' => $order,
            'totalGeral' => $totalGeral,
            'createdDate' => now()->format('d/m/Y'),
            'orderNumber' => str_pad($order->id, 6, '0', STR_PAD_LEFT)
        ];

        $pdf = Pdf::loadView('pdf.order-sets', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("orcamento-conjuntos-{$data['orderNumber']}.pdf");
    }
}
