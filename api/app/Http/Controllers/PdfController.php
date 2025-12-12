<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\SetPart;
use App\Services\OrderService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PdfController extends Controller
{
    protected $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

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

        $calculations = $this->orderService->calculateOrderValues($order);
        $totalGeral = $calculations['grand_total'];

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

        $calculations = $this->orderService->calculateSetValues($order);
        $totalGeral = $calculations['grand_total'];

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

    public function generateOrderPrintPdf($id)
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

        $data = [
            'order' => $order,
            'createdDate' => now()->format('d/m/Y'),
            'orderNumber' => str_pad($order->id, 6, '0', STR_PAD_LEFT)
        ];

        $pdf = Pdf::loadView('pdf.order-print', $data);
        $pdf->setPaper('A4', 'portrait');

        return $pdf->stream("impressao-pedido-{$data['orderNumber']}.pdf");
    }

    public function generateSetPartPdf($id)
    {
        ini_set('memory_limit', '-1');

        $part = SetPart::with([
            'set.order.customer',
            'material',
            'sheet',
            'bar',
            'component',
            'ncm',
            'processes'
        ])->findOrFail($id);

        $order = $part->set->order;
        
        $partTypes = [
            'material' => 'Material',
            'sheet' => 'Chapa',
            'bar' => 'Barra',
            'component' => 'Componente',
            'process' => 'Processo'
        ];

        $part->is_vertical = $this->isImageVertical($part->content);

        $data = [
            'parts' => [$part],
            'partTypes' => $partTypes,
            'customerName' => $order->customer->name ?? 'N/A',
            'deliveryDate' => $order->estimated_delivery_date ?? '-',
            'createdDate' => now()->format('d/m/Y'),
            'orderNumber' => str_pad($order->id, 6, '0', STR_PAD_LEFT)
        ];

        $pdf = Pdf::loadView('pdf.set-parts', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("peca-{$part->id}-pedido-{$data['orderNumber']}.pdf");
    }

    public function generateOrderPartsPdf($id)
    {
        ini_set('memory_limit', '-1');
        
        $order = Order::with([
            'customer',
            'sets.setParts.material',
            'sets.setParts.sheet',
            'sets.setParts.bar',
            'sets.setParts.component',
            'sets.setParts.ncm',
            'sets.setParts.processes'
        ])->findOrFail($id);

        $allParts = [];
        foreach ($order->sets as $set) {
            foreach ($set->setParts as $part) {
                $part->is_vertical = $this->isImageVertical($part->content);
                $allParts[] = $part;
            }
        }

        $partTypes = [
            'material' => 'Material',
            'sheet' => 'Chapa',
            'bar' => 'Barra',
            'component' => 'Componente',
            'process' => 'Processo'
        ];

        $data = [
            'parts' => $allParts,
            'partTypes' => $partTypes,
            'customerName' => $order->customer->name ?? 'N/A',
            'deliveryDate' => $order->estimated_delivery_date ?? '-',
            'createdDate' => now()->format('d/m/Y'),
            'orderNumber' => str_pad($order->id, 6, '0', STR_PAD_LEFT)
        ];

        $pdf = Pdf::loadView('pdf.set-parts', $data);
        $pdf->setPaper('A4', 'landscape');

        return $pdf->stream("pecas-pedido-{$data['orderNumber']}.pdf");
    }

    private function isImageVertical($imagePath)
    {
        if (!$imagePath) {
            return false;
        }

        try {
            $fullPath = public_path($imagePath);
            
            if (!file_exists($fullPath)) {
                return false;
            }

            $imageSize = getimagesize($fullPath);
            
            if ($imageSize === false) {
                return false;
            }

            $width = $imageSize[0];
            $height = $imageSize[1];

            return $height > $width;
        } catch (\Exception $e) {
            Log::error("Erro ao verificar orientação da imagem: " . $e->getMessage());
            return false;
        }
    }
}
