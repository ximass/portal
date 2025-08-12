<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Set;
use Illuminate\Support\Facades\Storage;
use App\Services\OptimizeFileService;

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
            'content' => 'nullable|string',
        ]);

        $set = Set::create($request->only(['name', 'order_id', 'content']));

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
            'content' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $updateData = $request->only(['name', 'content']);

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image if exists
            if ($set->content && Storage::disk('public')->exists($set->content)) {
                Storage::disk('public')->delete($set->content);
            }

            $file = $request->file('image');
            $path = $file->store('uploads/sets', 'public');

            try {
                $optimizedImagePath = OptimizeFileService::optimize($path);
                if (!empty($optimizedImagePath)) {
                    // Delete original file after optimization
                    Storage::disk('public')->delete($path);
                    $updateData['content'] = $optimizedImagePath;
                } else {
                    $updateData['content'] = $path;
                }
            } catch (\Exception $e) {
                $updateData['content'] = $path;
            }
        }

        $set->update($updateData);

        return response()->json($set);
    }

    public function destroy(Set $set)
    {
        $set->delete();

        return response()->json(['message' => 'Set deleted successfully']);
    }
}