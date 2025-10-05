<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Set;
use App\Models\SetPart;
use App\Http\Controllers\SetPartController;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(
            [
                'sets' => function($query) {
                    $query->orderBy('id', 'asc');
                },
                'customer.state'
            ]
        )->orderBy('id', 'desc')->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type'           => 'required|in:pre_order,order',
            'final_value'    => 'nullable|numeric',
            'customer_id'   => 'nullable|integer',
            'markup'        => 'nullable|numeric',
            'delivery_type' => 'nullable|in:CIF,FOB',
            'delivery_value' => 'nullable|numeric',
            'delivery_date' => 'nullable|date',
            'estimated_delivery_date' => 'nullable|string',
            'payment_obs'   => 'nullable|string'
        ]);

        $order = Order::create($validated);

        return response()->json($order, 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load(['sets' => function($query) {
            $query->orderBy('id', 'asc');
        }]));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'type'           => 'sometimes|required|in:pre_order,order',
            'final_value'    => 'sometimes|required|numeric',
            'customer_id'   => 'nullable|integer',
            'markup'        => 'nullable|numeric',
            'delivery_type' => 'nullable|in:CIF,FOB',
            'delivery_value' => 'nullable|numeric',
            'delivery_date' => 'nullable|date',
            'estimated_delivery_date' => 'nullable|string',
            'payment_obs'   => 'nullable|string'
        ]);

        $order->update($validated);

        return response()->json($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    public function onMarkupChange(Request $request, $orderId)
    {
        $request->validate([
            'markup' => 'required|numeric'
        ]);

        DB::transaction(function () use ($request, $orderId) {
            $markup = $request->input('markup');

            $order         = Order::with(['sets.setParts', 'customer.state'])->findOrFail($orderId);
            $order->markup = $markup;
            $order->save();

            foreach ($order->sets as $set) {
                foreach ($set->setParts as $part) {
                    $part = SetPartController::calculateProperties($part);
                    $part->save();
                }
            }
        });

        return response()->json(['message' => 'Partes recalculadas com sucesso']);
    }

    public function duplicate(Order $order)
    {
        $newOrder = DB::transaction(function () use ($order) {
            $originalOrder = Order::with(['sets.setParts.processes'])->findOrFail($order->id);

            $newOrder = Order::create([
                'type'                   => $originalOrder->type,
                'customer_id'            => $originalOrder->customer_id,
                'markup'                 => $originalOrder->markup,
                'final_value'            => $originalOrder->final_value,
                'delivery_type'          => $originalOrder->delivery_type,
                'delivery_value'         => $originalOrder->delivery_value,
                'delivery_date'          => $originalOrder->delivery_date,
                'estimated_delivery_date'=> $originalOrder->estimated_delivery_date,
                'payment_obs'            => $originalOrder->payment_obs,
            ]);

            foreach ($originalOrder->sets as $originalSet) {
                $newSet = Set::create([
                    'order_id'  => $newOrder->id,
                    'name'      => $originalSet->name,
                    'content'   => $originalSet->content,
                    'quantity'  => $originalSet->quantity,
                    'unit'      => $originalSet->unit,
                    'ncm_id'    => $originalSet->ncm_id,
                    'reference' => $originalSet->reference,
                    'obs'       => $originalSet->obs,
                ]);

                foreach ($originalSet->setParts as $originalPart) {
                    $newPart = SetPart::create([
                        'set_id'            => $newSet->id,
                        'type'              => $originalPart->type,
                        'material_id'       => $originalPart->material_id,
                        'sheet_id'          => $originalPart->sheet_id,
                        'bar_id'            => $originalPart->bar_id,
                        'component_id'      => $originalPart->component_id,
                        'ncm_id'            => $originalPart->ncm_id,
                        'title'             => $originalPart->title,
                        'content'           => $originalPart->content,
                        'secondary_content' => $originalPart->secondary_content,
                        'quantity'          => $originalPart->quantity,
                        'unit'              => $originalPart->unit,
                        'unit_net_weight'   => $originalPart->unit_net_weight,
                        'unit_gross_weight' => $originalPart->unit_gross_weight,
                        'net_weight'        => $originalPart->net_weight,
                        'gross_weight'      => $originalPart->gross_weight,
                        'unit_value'        => $originalPart->unit_value,
                        'final_value'       => $originalPart->final_value,
                        'unit_ipi_value'    => $originalPart->unit_ipi_value,
                        'total_ipi_value'   => $originalPart->total_ipi_value,
                        'unit_icms_value'   => $originalPart->unit_icms_value,
                        'total_icms_value'  => $originalPart->total_icms_value,
                        'thickness'         => $originalPart->thickness,
                        'width'             => $originalPart->width,
                        'length'            => $originalPart->length,
                        'loss'              => $originalPart->loss,
                        'markup'            => $originalPart->markup,
                        'obs'               => $originalPart->obs,
                        'reference'         => $originalPart->reference,
                        'locked_values'     => $originalPart->locked_values,
                    ]);

                    if ($originalPart->processes && count($originalPart->processes) > 0) {
                        foreach ($originalPart->processes as $process) {
                            $newPart->processes()->attach($process->id, [
                                'time'        => $process->pivot->time,
                                'final_value' => $process->pivot->final_value,
                            ]);
                        }
                    }
                }
            }

            return $newOrder;
        });

        return response()->json($newOrder->load(['sets.setParts', 'customer.state']), 201);
    }
}