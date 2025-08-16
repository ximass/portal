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

        $totalGeral = 0;
        
        foreach ($order->sets as $set) {
            foreach ($set->setParts as $part) {
                $unitValue  = $part->unit_value ?? 0;
                $unitValue -= $part->total_ipi_value ?? 0;

                $totalValue = $part->final_value ?? 0;

                $part->calculated_unit_value = $unitValue;
                $part->calculated_total_value = $totalValue;

                $totalGeral += $totalValue;
            }
        }

        $totalGeral += $order->delivery_value ?? 0;

        $data = [
            'order' => $order,
            'totalGeral' => $totalGeral,
            'createdDate' => now()->format('d/m/Y'),
            'orderNumber' => str_pad($order->id, 6, '0', STR_PAD_LEFT)
        ];

        $pdf = Pdf::loadView('pdf.order-set-parts', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("orcamento-pecas-{$data['orderNumber']}.pdf");
    }

    public function generateOrderSetsPdf($id)
    {
        $order = Order::with([
            'customer.state',
            'sets.setParts.material',
            'sets.setParts.sheet',
            'sets.setParts.bar',
            'sets.setParts.component',
            'sets.setParts.ncm',
            'sets.ncm'
        ])->findOrFail($id);

        $totalGeral = 0;
        
        foreach ($order->sets as $set) {
            $setUnitValue  = 0;
            $setIpiValue   = 0;
            $setTotalValue = 0;
            
            foreach ($set->setParts as $part) {
                $setUnitValue += $part->final_value ?? 0;
                $setIpiValue  += $part->total_ipi_value ?? 0;
            }

            $setTotalValue = $setUnitValue * ($set->quantity ?? 1);
            $setUnitValue -= $setIpiValue;

            $set->calculated_unit_value = $setUnitValue;
            $set->calculated_total_value = $setTotalValue;

            $totalGeral += $setTotalValue;
        }

        $totalGeral += $order->delivery_value ?? 0;

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
