<?php

namespace App\Http\Controllers;

use App\Models\SetPart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetPartController extends Controller
{
    public function index($setId)
    {
        return response()->json(SetPart::where('set_id', $setId)->get());
    }

    public function store(Request $request, $setId)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $setPart = SetPart::create([
            'title' => $request->title,
            'content' => $request->content,
            'set_id' => $setId,
        ]);

        return response()->json($setPart, 201);
    }

    public function show($setId, $id)
    {
        $setPart = SetPart::where('set_id', $setId)->findOrFail($id);

        return response()->json($setPart);
    }

    public function update(Request $request, $setId, $id)
    {
        $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'content' => 'sometimes|required|string',
        ]);

        $setPart = SetPart::where('set_id', $setId)->findOrFail($id);
        $setPart->update($request->only('title', 'content'));

        return response()->json($setPart);
    }

    public function destroy($setId, $id)
    {
        $setPart = SetPart::where('set_id', $setId)->findOrFail($id);
        $setPart->delete();

        return response()->json(['message' => 'Order part deleted successfully']);
    }

    public function upload(Request $request)
    {
        if (!$request->hasFile('file')) {
            return response()->json(['error' => 'No file uploaded'], 400);
        }

        $file = $request->file('file');

        $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,pdf',
            'set_id' => 'required|integer'
        ]);

        $path = $file->store('uploads/order-parts', 'public');

        $setPart = SetPart::create([
            'title' => $file->getClientOriginalName(),
            'content' => Storage::url($path),
            'set_id' => $request->input('set_id'),
        ]);

        return response()->json($setPart, 201);
    }
}