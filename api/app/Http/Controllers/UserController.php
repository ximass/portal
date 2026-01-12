<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        return response()->json(User::all());
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function search(Request $request)
    {
        $request->validate([
            'search' => 'required|string'
        ]);

        $search = $request->query('search', '');
        
        $users = User::where('name', 'ILIKE', "%{$search}%")
                     ->select('id', 'name')
                     ->limit(10)
                     ->get();

        return response()->json($users);
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'nullable|string|min:6',
            'admin' => 'required|boolean',
            'enabled' => 'required|boolean'
        ]);

        $updateData = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'admin' => $request->input('admin'),
            'enabled' => $request->input('enabled')
        ];

        if ($request->filled('password')) {
            $updateData['password'] = \Hash::make($request->input('password'));
        }

        $user->update($updateData);

        return response()->json($user);
    }

    public function destroy(User $user)
    {
        $user->delete();
        
        return response()->json(['message' => 'User deleted successfully']);
    }

    /**
     * Get the permissions for the authenticated user.
     */
    public function permissions(Request $request)
    {
        $user = $request->user();
        
        if (!$user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        // Se for admin, retorna todas as permissões
        if ($user->admin) {
            return response()->json(\App\Models\Permission::all());
        }

        // Busca as permissões do usuário através dos grupos
        $permissions = \App\Models\Permission::whereHas('groups', function ($query) use ($user) {
            $query->whereHas('users', function ($userQuery) use ($user) {
                $userQuery->where('users.id', $user->id);
            });
        })->get();

        return response()->json($permissions);
    }
}