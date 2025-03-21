<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Material;

class MaterialController extends Controller
{
    public function index()
    {
        return response()->json(Material::all());
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
}