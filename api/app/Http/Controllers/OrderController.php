<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(Order::with('sets')->get());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'final_value' => 'required|numeric',
            'customer_id' => 'nullable|integer'
        ]);

        $order = Order::create($validated);

        return response()->json($order, 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load('sets'));
    }

    public function update(Request $request, Order $order)
    {
        $validated = $request->validate([
            'final_value' => 'sometimes|required|numeric',
            'customer_id' => 'nullable|integer'
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