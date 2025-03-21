<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Component;

class ComponentController extends Controller
{
    public function index()
    {
        return response()->json(Component::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'material_id'   => 'required|exists:materials,id',
            'specification' => 'nullable|string',
            'unit_value'    => 'required|numeric',
        ]);

        $component = Component::create($data);

        return response()->json($component, 201);
    }

    public function show($materialId)
    {
        $component = Component::findOrFail($materialId);
        
        return response()->json($component);
    }

    public function update(Request $request, $materialId)
    {
        $component = Component::findOrFail($materialId);
        $data = $request->validate([
            'specification' => 'sometimes|nullable|string',
            'unit_value'    => 'sometimes|required|numeric',
        ]);

        $component->update($data);

        return response()->json($component);
    }

    public function destroy($materialId)
    {
        $component = Component::findOrFail($materialId);
        $component->delete();

        return response()->json(null, 204);
    }
}