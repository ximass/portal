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
                'customer'
            ]
        )->get();

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

            $order         = Order::with(['sets.setParts'])->findOrFail($orderId);
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
}