<?php

namespace App\Http\Controllers;

use App\Models\MercosurCommonNomenclature;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class MercosurCommonNomenclatureController extends Controller
{
    public function index(): JsonResponse
    {
        $nomenclatures = MercosurCommonNomenclature::orderBy('code')->get();
        
        return response()->json($nomenclatures);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:20|unique:mercosur_common_nomenclatures,code',
                'ipi' => 'required|numeric|min:0|max:100',
            ]);

            $nomenclature = MercosurCommonNomenclature::create($validated);

            return response()->json($nomenclature, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(MercosurCommonNomenclature $mercosurCommonNomenclature): JsonResponse
    {
        return response()->json($mercosurCommonNomenclature);
    }

    public function update(Request $request, MercosurCommonNomenclature $mercosurCommonNomenclature): JsonResponse
    {
        try {
            $validated = $request->validate([
                'code' => 'required|string|max:20|unique:mercosur_common_nomenclatures,code,' . $mercosurCommonNomenclature->id,
                'ipi' => 'required|numeric|min:0|max:100',
            ]);

            $mercosurCommonNomenclature->update($validated);

            return response()->json($mercosurCommonNomenclature);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(MercosurCommonNomenclature $mercosurCommonNomenclature): JsonResponse
    {
        try {
            $mercosurCommonNomenclature->delete();
            
            return response()->json(['message' => 'NCM deleted successfully']);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting NCM',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
