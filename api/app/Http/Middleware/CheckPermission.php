<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $user = $request->user();

        // Admin tem todas as permissões
        if ($user && $user->admin) {
            return $next($request);
        }

        // Verifica se o usuário tem alguma das permissões necessárias
        if ($user && $user->hasAnyPermission($permissions)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Você não tem permissão para realizar esta ação.'
        ], 403);
    }
}
