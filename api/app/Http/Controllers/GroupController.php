<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Group;

class GroupController extends Controller
{
    public function index()
    {
        return response()->json(Group::with(['users', 'permissions'])->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $group = Group::create($request->only('name'));
        
        if ($request->has('user_ids')) {
            $group->users()->attach($request->user_ids);
        }

        if ($request->has('permission_ids')) {
            $group->permissions()->attach($request->permission_ids);
        }

        return response()->json($group->load(['users', 'permissions']), 201);
    }

    public function show(Group $group)
    {
        return response()->json($group->load(['users', 'permissions']));
    }

    public function update(Request $request, Group $group)
    {
        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'user_ids' => 'array',
            'user_ids.*' => 'exists:users,id',
            'permission_ids' => 'array',
            'permission_ids.*' => 'exists:permissions,id',
        ]);

        $group->update($request->only('name'));

        if ($request->has('user_ids')) {
            $group->users()->sync($request->user_ids);
        }

        if ($request->has('permission_ids')) {
            $group->permissions()->sync($request->permission_ids);
        }

        return response()->json($group->load(['users', 'permissions']));
    }

    public function destroy(Group $group)
    {
        $group->delete();

        return response()->json(['message' => 'Group deleted successfully']);
    }
}