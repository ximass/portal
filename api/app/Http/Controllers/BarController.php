<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bar;

class BarController extends Controller
{
    public function index()
    {
        $bars = Bar::with('ncm')->get();

        return response()->json($bars);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'weight'         => 'required|numeric',
            'length'         => 'required|numeric',
            'price_kg'       => 'required|numeric',
            'ncm_id'         => 'nullable|exists:mercosur_common_nomenclatures,id'
        ]);

        $bar = Bar::create($data);

        return response()->json($bar->load('ncm'), 201);
    }

    public function show($barId)
    {
        $bar = Bar::with('ncm')->findOrFail($barId);

        return response()->json($bar);
    }

    public function update(Request $request, $barId)
    {
        $bar = Bar::findOrFail($barId);
        $data = $request->validate([
            'name'           => 'sometimes|required|string|max:255',
            'weight'         => 'sometimes|required|numeric',
            'length'         => 'sometimes|required|numeric',
            'price_kg'       => 'sometimes|required|numeric',
            'ncm_id'         => 'nullable|exists:mercosur_common_nomenclatures,id'
        ]);

        $bar->update($data);

        return response()->json($bar->load('ncm'));
    }

    public function destroy($barId)
    {
        $bar = Bar::findOrFail($barId);
        $bar->delete();

        return response()->json(null, 204);
    }
}