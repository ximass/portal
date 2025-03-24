<?php

namespace App\Http\Controllers;

use App\Models\SetPart;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SetPartController extends Controller
{
    public function index($setId)
    {
        return response()->json(
            SetPart::where('set_id', $setId)
            ->with('processes')
            ->get()
        );
    }

    public function store(Request $request, $setId)
    {
        $request->validate([
            'title'            => 'required|string|max:255',
            'content'          => 'required|string',
            'material_id'      => 'sometimes|nullable|integer|exists:materials,id',
            'quantity'         => 'sometimes|nullable|integer',
            'loss'             => 'sometimes|nullable|numeric',
            'unit_net_weight'  => 'sometimes|nullable|numeric',
            'net_weight'       => 'sometimes|nullable|numeric',
            'unit_gross_weight'=> 'sometimes|nullable|numeric',
            'gross_weight'     => 'sometimes|nullable|numeric',
            'unit_value'       => 'sometimes|nullable|numeric',
            'final_value'      => 'sometimes|nullable|numeric',
            'markup'           => 'sometimes|nullable|numeric',
        ]);

        $setPart = SetPart::create([
            'title'            => $request->title,
            'content'          => $request->content,
            'set_id'           => $setId,
            'material_id'      => $request->input('material_id'),
            'quantity'         => $request->input('quantity'),
            'loss'             => $request->input('loss'),
            'unit_net_weight'  => $request->input('unit_net_weight'),
            'net_weight'       => $request->input('net_weight'),
            'unit_gross_weight'=> $request->input('unit_gross_weight'),
            'gross_weight'     => $request->input('gross_weight'),
            'unit_value'       => $request->input('unit_value'),
            'final_value'      => $request->input('final_value'),
            'markup'           => $request->input('markup'),
        ]);

        if ($request->has('processes')) {
            $processes = $request->input('processes');
            $syncData = [];

            foreach ($processes as $process) {
                if (isset($process['id'])) {
                    $syncData[$process['id']] = [
                        'time' => $process['time'] ?? 0,
                        'quantity' => intval($process['quantity']) ?? 0,
                    ];
                }
            }

            $setPart->processes()->sync($syncData);
        }

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
            'title'            => 'sometimes|required|string|max:255',
            'content'          => 'sometimes|required|string',
            'material_id'      => 'sometimes|nullable|integer|exists:materials,id',
            'quantity'         => 'sometimes|nullable|integer',
            'loss'             => 'sometimes|nullable|numeric',
            'unit_net_weight'  => 'sometimes|nullable|numeric',
            'net_weight'       => 'sometimes|nullable|numeric',
            'unit_gross_weight'=> 'sometimes|nullable|numeric',
            'gross_weight'     => 'sometimes|nullable|numeric',
            'unit_value'       => 'sometimes|nullable|numeric',
            'final_value'      => 'sometimes|nullable|numeric',
            'markup'           => 'sometimes|nullable|numeric',
        ]);

        $setPart = SetPart::where('set_id', $setId)->findOrFail($id);
        $setPart->update($request->only(
            'title',
            'content',
            'material_id',
            'quantity',
            'loss',
            'unit_net_weight',
            'net_weight',
            'unit_gross_weight',
            'gross_weight',
            'unit_value',
            'final_value',
            'markup'
        ));

        if ($request->has('processes')) {
            $processes = $request->input('processes');
            $syncData = [];

            foreach ($processes as $process) {
                if (isset($process['id'])) {
                    $syncData[$process['id']] = [
                        'time' => $process['time'] ?? 0,
                        'quantity' => intval($process['quantity']) ?? 0,
                    ];
                }
            }

            $setPart->processes()->sync($syncData);
        }


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
            'file'   => 'required|file|mimes:jpg,jpeg,png,pdf,webp',
            'set_id' => 'required|integer'
        ]);

        $path = $file->store('uploads/order-parts', 'public');

        $setPart = SetPart::create([
            'title'   => $file->getClientOriginalName(),
            'content' => Storage::url($path),
            'set_id'  => $request->input('set_id'),
        ]);

        return response()->json($setPart, 201);
    }
}