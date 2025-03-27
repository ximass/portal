<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;

class MaterialController extends Controller
{
    const TYPES = [
        ['name' => 'Chapa', 'value' => 'sheet'],
        ['name' => 'Barra', 'value' => 'bar'],
        ['name' => 'Componente', 'value' => 'component'],
    ];

    public function index(Request $request)
    {
        if ($request->has('type')) {
            $materials = Material::where('type', $request->input('type'))
                ->with(['sheet', 'bar', 'component'])
                ->get();
        } else {
            $materials = Material::with(['sheet', 'bar', 'component'])->get();
        }

        return response()->json($materials);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'type' => 'required|in:sheet,bar,component',
        ]);

        $material = Material::create($data);

        return response()->json($material, 201);
    }

    public function show(Material $material)
    {
        return response()->json($material->load(['sheet', 'bar', 'component']));
    }

    public function update(Request $request, Material $material)
    {
        $data = $request->validate([
            'name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|in:sheet,bar,component',
        ]);

        $material->update($data);

        return response()->json($material);
    }

    public function destroy(Material $material)
    {
        $material->delete();

        return response()->json(null, 204);
    }

    public function getMaterialsType()
    {
        return response()->json(self::TYPES);
    }
}