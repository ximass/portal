<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $materials = Material::all();

        return response()->json($materials);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'specific_weight' => 'required|numeric',
            'price_kg'        => 'required|numeric'
        ]);

        $material = Material::create($data);

        return response()->json($material, 201);
    }

    public function show(Material $material)
    {
        return response()->json($material->load(['sheets']));
    }

    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'name'            => 'sometimes|required|string|max:100',
            'specific_weight' => 'sometimes|required|numeric',
            'price_kg'        => 'sometimes|required|numeric'
        ]);

        $material->update($data);

        return response()->json($material);
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return response()->json(null, 204);
    }
}