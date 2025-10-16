<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;

class MaterialController extends Controller
{
    public function index(Request $request)
    {
        $materials = Material::with('ncm')->get();

        return response()->json($materials);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'specific_weight' => 'required|numeric',
            'price_kg'        => 'required|numeric',
            'ncm_id'          => 'nullable|exists:mercosur_common_nomenclatures,id'
        ]);

        $material = Material::create($data);

        return response()->json($material->load('ncm'), 201);
    }

    public function show(Material $material)
    {
        return response()->json($material->load(['sheets', 'ncm']));
    }

    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'name'            => 'sometimes|required|string|max:100',
            'specific_weight' => 'sometimes|required|numeric',
            'price_kg'        => 'sometimes|required|numeric',
            'ncm_id'          => 'nullable|exists:mercosur_common_nomenclatures,id'
        ]);

        $material->update($data);

        return response()->json($material->load('ncm'));
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return response()->json(null, 204);
    }
}