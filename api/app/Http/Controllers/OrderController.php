<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

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
            'final_value'   => 'nullable|numeric',
            'customer_id'   => 'nullable|integer',
            'markup'        => 'nullable|numeric',
            'delivery_type' => 'nullable|in:CIF,FOB',
            'delivery_date' => 'nullable|date',
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
            'final_value'   => 'sometimes|required|numeric',
            'customer_id'   => 'nullable|integer',
            'markup'        => 'nullable|numeric',
            'delivery_type' => 'nullable|in:CIF,FOB',
            'delivery_date' => 'nullable|date',
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
}