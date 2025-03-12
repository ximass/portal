<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        return response()->json(Order::with('orderParts')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'final_value' => 'required|numeric',
        ]);

        $order = Order::create($request->only('final_value'));

        return response()->json($order, 201);
    }

    public function show(Order $order)
    {
        return response()->json($order->load('orderParts'));
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'final_value' => 'sometimes|required|numeric',
        ]);

        $order->update($request->only('final_value'));

        return response()->json($order);
    }

    public function destroy(Order $order)
    {
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }
}