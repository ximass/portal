<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bar;

class BarController extends Controller
{
    public function index()
    {
        return response()->json(Bar::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'material_id'    => 'required|exists:materials,id',
            'diameter'       => 'required|numeric',
            'length'         => 'required|numeric',
            'specific_weight' => 'required|numeric',
            'price_per_gram' => 'required|numeric',
        ]);

        $bar = Bar::create($data);

        return response()->json($bar, 201);
    }

    public function show($materialId)
    {
        $bar = Bar::findOrFail($materialId);

        return response()->json($bar);
    }

    public function update(Request $request, $materialId)
    {
        $bar = Bar::findOrFail($materialId);
        $data = $request->validate([
            'diameter'       => 'sometimes|required|numeric',
            'length'         => 'sometimes|required|numeric',
            'specific_weight' => 'sometimes|required|numeric',
            'price_per_gram' => 'sometimes|required|numeric',
        ]);

        $bar->update($data);

        return response()->json($bar);
    }

    public function destroy($materialId)
    {
        $bar = Bar::findOrFail($materialId);
        $bar->delete();

        return response()->json(null, 204);
    }
}