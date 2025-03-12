<?php

namespace App\Http\Controllers;

use App\Models\OrderPart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrderPartController extends Controller
{
    public function index($orderId)
    {
        return response()->json(OrderPart::where('order_id', $orderId)->get());
    }

    public function store(Request $request, $orderId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $orderPart = OrderPart::create([
            'title' => $request->title,
            'content' => $request->content,
            'order_id' => $orderId,
        ]);

        return response()->json($orderPart, 201);
    }

    public function show($orderId, $id)
    {
        $orderPart = OrderPart::where('order_id', $orderId)->findOrFail($id);

        return response()->json($orderPart);
    }

    public function update(Request $request, $orderId, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $orderPart = OrderPart::where('order_id', $orderId)->findOrFail($id);
        $orderPart->update($request->only('title', 'content'));

        return response()->json($orderPart);
    }

    public function destroy($orderId, $id)
    {
        $orderPart = OrderPart::where('order_id', $orderId)->findOrFail($id);
        $orderPart->delete();

        return response()->json(['message' => 'Order part deleted successfully']);
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        $file = $request->file('file');

        //TODO: validar tamanho
        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf',
        ]);

        $path = $file->store('uploads/order-parts', 'public');

        return response()->json([
            'file_path' => Storage::url($path)
        ], 200);
    }
}