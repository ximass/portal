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
            'name'          => 'required|string|max:100',
            'specification' => 'nullable|string',
            'unit_value'    => 'required|numeric',
            'supplier'      => 'nullable|string',
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
            'name'          => 'sometimes|required|string|max:100',
            'specification' => 'sometimes|nullable|string',
            'unit_value'    => 'sometimes|required|numeric',
            'supplier'      => 'sometimes|nullable|string',
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