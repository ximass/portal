<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sheet;

class SheetController extends Controller
{
    public function index(Request $request)
    {
        $query = Sheet::with('material.ncm');
        
        if ($request->has('material_id') && $request->material_id) {
            $query->where('material_id', $request->material_id);
        }
        
        return response()->json($query->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'material_id'     => 'required|exists:materials,id',
            'name'            => 'required|string|max:255',
            'thickness'       => 'required|numeric',
            'width'           => 'required|numeric',
            'length'          => 'required|numeric',
        ]);

        $sheet = Sheet::create($data);

        return response()->json($sheet->load('material.ncm'), 201);
    }

    public function show($materialId)
    {
        $sheet = Sheet::with('material.ncm')->findOrFail($materialId);

        return response()->json($sheet);
    }

    public function update(Request $request, $materialId)
    {
        $sheet = Sheet::findOrFail($materialId);
        $data = $request->validate([
            'material_id'     => 'sometimes|required|exists:materials,id',
            'name'            => 'sometimes|required|string|max:255',
            'thickness'       => 'sometimes|required|numeric',
            'width'           => 'sometimes|required|numeric',
            'length'          => 'sometimes|required|numeric',
        ]);

        $sheet->update($data);

        return response()->json($sheet->load('material.ncm'));
    }

    public function destroy($materialId)
    {
        $sheet = Sheet::findOrFail($materialId);
        $sheet->delete();

        return response()->json(null, 204);
    }
}