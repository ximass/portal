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
                $ipi = ($part->ncm->ipi ?? 0) / 100;

                $unitValueWithIPI = $part->unit_value ?? 0;
                $baseUnitValue = $ipi !== 0 ? ($unitValueWithIPI / (1 + $ipi)) : $unitValueWithIPI;

                $totalValue = $part->final_value ?? 0;

                $part->calculated_unit_value = $baseUnitValue;
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
            $setUnitWithIpi = 0;
            $setUnitBase = 0;
            $setTotalValue = 0;

            foreach ($set->setParts as $part) {
                $partFinalWithIpi = $part->final_value ?? 0;
                $ipi = ($part->ncm->ipi ?? 0) / 100;

                $partBaseFinal = $ipi !== 0 ? ($partFinalWithIpi / (1 + $ipi)) : $partFinalWithIpi;

                $setUnitWithIpi += $partFinalWithIpi;
                $setUnitBase += $partBaseFinal;
            }

            $setTotalValue = $setUnitWithIpi * ($set->quantity ?? 1);

            $set->calculated_unit_value = $setUnitBase;
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
