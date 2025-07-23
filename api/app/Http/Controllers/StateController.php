<?php

namespace App\Http\Controllers;

use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class StateController extends Controller
{
    public function index(): JsonResponse
    {
        $states = State::orderBy('name')->get();
        
        return response()->json($states);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'abbreviation' => 'required|string|size:2|unique:states,abbreviation',
                'icms' => 'required|numeric|min:0|max:100',
            ]);

            $state = State::create($validated);

            return response()->json($state, 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function show(State $state): JsonResponse
    {
        return response()->json($state);
    }

    public function update(Request $request, State $state): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:100',
                'abbreviation' => 'required|string|size:2|unique:states,abbreviation,' . $state->id,
                'icms' => 'required|numeric|min:0|max:100',
            ]);

            $state->update($validated);

            return response()->json($state);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        }
    }

    public function destroy(State $state): JsonResponse
    {
        try {
            $state->delete();
            
            return response()->json([
                'message' => 'State deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error deleting state',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
