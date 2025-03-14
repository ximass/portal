<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Set;

class SetController extends Controller
{
    public function index()
    {
        return response()->json(Set::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'order_id' => 'required|integer|exists:orders,id',
        ]);

        $set = Set::create($request->only(['name', 'order_id']));

        return response()->json($set, 201);
    }

    public function show(Set $set)
    {
        return response()->json($set);
    }

    public function update(Request $request, Set $set)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
        ]);

        $set->update($request->only(['name']));

        return response()->json($set);
    }

    public function destroy(Set $set)
    {
        $set->delete();

        return response()->json(['message' => 'Set deleted successfully']);
    }
}