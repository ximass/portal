<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Component;

class ComponentController extends Controller
{
    public function index()
    {
        return response()->json(Component::with('ncm')->orderBy('name', 'asc')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'specification' => 'nullable|string',
            'unit_value'    => 'required|numeric',
            'supplier'      => 'nullable|string',
            'ncm_id'        => 'nullable|exists:mercosur_common_nomenclatures,id'
        ]);

        $component = Component::create($data);

        return response()->json($component->load('ncm'), 201);
    }

    public function show($materialId)
    {
        $component = Component::with('ncm')->findOrFail($materialId);
        
        return response()->json($component);
    }

    public function update(Request $request, $materialId)
    {
        $component = Component::findOrFail($materialId);
        $data = $request->validate([
            'name'          => 'sometimes|required|string|max:100',
            'specification' => 'sometimes|nullable|string',
            'unit_value'    => 'sometimes|required|numeric',
            'supplier'      => 'sometimes|nullable|string',
            'ncm_id'        => 'nullable|exists:mercosur_common_nomenclatures,id'
        ]);

        $component->update($data);

        return response()->json($component->load('ncm'));
    }

    public function destroy($materialId)
    {
        $component = Component::findOrFail($materialId);
        $component->delete();

        return response()->json(null, 204);
    }
}