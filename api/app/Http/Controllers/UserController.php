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
            'admin' => 'required|boolean'
        ]);

        $user->update([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'admin' => $request->input('admin')
        ]);

        return response()->json($user);
    }
}