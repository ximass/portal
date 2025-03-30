<?php

namespace App\Http\Controllers;

use App\Models\Process;
use Illuminate\Http\Request;

class ProcessController extends Controller
{
    public function index()
    {
        return response()->json(Process::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string',
            'content' => 'required|string',
            'value_per_minute' => 'numeric|nullable',
        ]);
        $process = Process::create($data);

        return response()->json($process, 201);
    }

    public function show(Process $process)
    {
        return response()->json($process);
    }

    public function update(Request $request, Process $process)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string',
            'content' => 'sometimes|required|string',
            'value_per_minute' => 'numeric|nullable',
        ]);

        $process->update($data);

        return response()->json($process);
    }

    public function destroy(Process $process)
    {
        $process->delete();
        
        return response()->json(null, 204);
    }

    /**
     * Calculate the value based on the process and properties.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function calculateProcessValue(Request $request)
    {
        $data = $request->validate([
            'process_id' => 'required|exists:processes,id',
            'time'       => 'required|numeric',
            'quantity'   => 'nullable|numeric',
        ]);

        $process = Process::find($data['process_id']);

        if (!$process) {
            return response()->json(['error' => 'Process not found'], 404);
        }

        $value = $this->calculateValue($process, $data);

        return response()->json(['value' => $value]);
    }


    /**
     * Calculate the value based on the process and properties.
     *
     * @param Process $process
     * @param array $properties
     * @return float
     */
    public function calculateValue(Process $process, $properties)
    {
        if (!isset($properties['quantity'])) {
            $properties['quantity'] = 1;
        }

        $value = ($process->value_per_minute * $properties['time']) * $properties['quantity'];

        return $value;
    }
}