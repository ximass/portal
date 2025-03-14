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
            'fixed_value' => 'numeric|nullable',
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
            'fixed_value' => 'numeric|nullable',
        ]);

        $process->update($data);

        return response()->json($process);
    }

    public function destroy(Process $process)
    {
        $process->delete();
        
        return response()->json(null, 204);
    }
}