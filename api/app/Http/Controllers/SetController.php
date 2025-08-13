<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Set;
use Illuminate\Support\Facades\Storage;
use App\Services\OptimizeFileService;
use App\Services\PdfToWebpService;

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
            'image' => 'nullable|file|mimes:jpeg,png,jpg,gif,webp,pdf|max:2048',
        ]);

        $updateData = $request->only(['name', 'content']);

        if ($request->hasFile('image')) {
            if ($set->content && Storage::disk('public')->exists($set->content)) {
                Storage::disk('public')->delete($set->content);
            }

            $file = $request->file('image');
            $path = $file->store('uploads/sets', 'public');

            if ($file->getClientOriginalExtension() === 'pdf') {
                $pdfService = new PdfToWebpService();
                $webpPaths = $pdfService->convert($path, 'uploads/sets');

                // Use the first page of the converted PDF
                if (!empty($webpPaths)) {
                    // Delete the original PDF file
                    Storage::disk('public')->delete($path);
                    $updateData['content'] = Storage::url($webpPaths[0]);
                } else {
                    $updateData['content'] = Storage::url($path);
                }
            } else {
                try {
                    $optimizedImagePath = OptimizeFileService::optimize($path);
                    if (!empty($optimizedImagePath)) {
                        // Delete original file after optimization
                        Storage::disk('public')->delete($path);
                        $updateData['content'] = Storage::url($optimizedImagePath);
                    } else {
                        $updateData['content'] = Storage::url($path);
                    }
                } catch (\Exception $e) {
                    $updateData['content'] = Storage::url($path);
                }
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