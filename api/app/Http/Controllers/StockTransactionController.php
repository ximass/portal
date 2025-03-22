<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockTransaction;
use App\Models\StockBalance;

class StockTransactionController extends Controller
{
    public function index()
    {
        $transactions = StockTransaction::with('material')->get();
        return response()->json($transactions);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'operation' => 'required|in:E,S',
            'quantity' => 'required|numeric',
            'obs' => 'nullable|string',
        ]);

        $transaction = StockTransaction::create($validated);

        $balance = StockBalance::firstOrCreate(
            ['material_id' => $validated['material_id']],
            ['balance' => 0]
        );

        if ($validated['operation'] === 'E') {
            $balance->balance += $validated['quantity'];
        } else {
            $balance->balance -= $validated['quantity'];
        }

        $balance->save();

        return response()->json($transaction, 201);
    }

    public function show($id)
    {
        $transaction = StockTransaction::with('material')->findOrFail($id);
        
        return response()->json($transaction);
    }

    public function update(Request $request, $id)
    {
        // Updating a transaction is not allowed because it affects stock consistency.
        return response()->json(['message' => 'Update not supported'], 403);
    }

    public function destroy($id)
    {
        // Deleting a transaction may jeopardize the stock balance, so it is not permitted.
        return response()->json(['message' => 'Delete not supported'], 403);
    }
}