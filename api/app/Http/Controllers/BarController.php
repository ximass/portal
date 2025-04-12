<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Bar;

class BarController extends Controller
{
    public function index()
    {
        $bars = Bar::all();

        return response()->json($bars);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'weight'         => 'required|numeric',
            'length'         => 'required|numeric',
            'price_kg'       => 'required|numeric',
        ]);

        $bar = Bar::create($data);

        return response()->json($bar, 201);
    }

    public function show($barId)
    {
        $bar = Bar::findOrFail($barId);

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
        ]);

        $bar->update($data);

        return response()->json($bar);
    }

    public function destroy($barId)
    {
        $bar = Bar::findOrFail($barId);
        $bar->delete();

        return response()->json(null, 204);
    }
}