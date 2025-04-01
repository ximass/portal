<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sheet;

class SheetController extends Controller
{
    public function index()
    {
        return response()->json(Sheet::with('material')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'material_id'     => 'required|exists:materials,id',
            'name'            => 'required|string|max:255',
            'width'           => 'required|numeric',
            'length'          => 'required|numeric',
        ]);

        $sheet = Sheet::create($data);

        return response()->json($sheet, 201);
    }

    public function show($materialId)
    {
        $sheet = Sheet::with('material')->findOrFail($materialId);

        return response()->json($sheet);
    }

    public function update(Request $request, $materialId)
    {
        $sheet = Sheet::findOrFail($materialId);
        $data = $request->validate([
            'material_id'     => 'sometimes|required|exists:materials,id',
            'name'            => 'sometimes|required|string|max:255',
            'width'           => 'sometimes|required|numeric',
            'length'          => 'sometimes|required|numeric',
        ]);

        $sheet->update($data);

        return response()->json($sheet);
    }

    public function destroy($materialId)
    {
        $sheet = Sheet::findOrFail($materialId);
        $sheet->delete();

        return response()->json(null, 204);
    }
}