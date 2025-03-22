<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockBalance;

class StockBalanceController extends Controller
{
    public function index()
    {
        $balances = StockBalance::with('material')->get();

        return response()->json($balances);
    }

    public function show($material_id)
    {
        $balance = StockBalance::with('material')->where('material_id', $material_id)->firstOrFail();

        return response()->json($balance);
    }

    public function update(Request $request, $material_id)
    {
        $balance = StockBalance::where('material_id', $material_id)->firstOrFail();
        
        $validated = $request->validate([
            'balance' => 'required|numeric',
        ]);

        $balance->balance = $validated['balance'];
        $balance->save();

        return response()->json($balance);
    }
}