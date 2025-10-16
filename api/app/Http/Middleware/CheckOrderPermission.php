<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Order;
use App\Models\Set;
use App\Models\SetPart;

class CheckOrderPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $action): Response
    {
        $user = $request->user();

        if ($request->is('api/orders/*/update-status')) {
            return $next($request);
        }

        if ($user && $user->admin) {
            return $next($request);
        }

        $order = null;
        $orderType = null;

        // Obtém o pedido da rota (direto ou através de set/set_part)
        if ($request->route('order')) {
            $order = $request->route('order');
            
            if (is_string($order) || is_numeric($order)) {
                $order = Order::find($order);
            }
            
            $orderType = $order ? $order->type : null;
        } elseif ($request->route('set')) {
            $set = $request->route('set');
            
            if (is_string($set) || is_numeric($set)) {
                $set = Set::with('order')->find($set);
            }
            
            if ($set && $set->order) {
                $orderType = $set->order->type;
            } elseif ($request->has('order_id')) {
                $order = Order::find($request->input('order_id'));
                $orderType = $order ? $order->type : null;
            }
        } elseif ($request->route('part') || $request->route()->parameter('setId')) {
            $setId = $request->route()->parameter('setId');
            
            if ($setId) {
                $set = Set::with('order')->find($setId);
                if ($set && $set->order) {
                    $orderType = $set->order->type;
                }
            } else {
                $part = $request->route('part');
                
                if (is_string($part) || is_numeric($part)) {
                    $part = SetPart::with('set.order')->find($part);
                }
                
                if ($part && $part->set && $part->set->order) {
                    $orderType = $part->set->order->type;
                }
            }
        } elseif ($request->input('type')) {
            // Se for criação de order, usa o tipo do request
            $orderType = $request->input('type');
        } elseif ($request->has('order_id')) {
            $order = Order::find($request->input('order_id'));
            $orderType = $order ? $order->type : null;
        }

        if (!$orderType) {
            return response()->json([
                'message' => 'Pedido não encontrado.'
            ], 404);
        }

        // Define a permissão necessária baseada no tipo de pedido e ação
        $permission = null;
        
        if ($action === 'edit') {
            $permission = $orderType === 'order' ? 'edit_orders' : 'edit_pre_orders';
        } elseif ($action === 'delete') {
            $permission = $orderType === 'pre_order' ? 'delete_pre_orders' : 'delete_orders';
        }

        // Verifica se o usuário tem a permissão necessária
        if ($user && $permission && $user->hasPermission($permission)) {
            return $next($request);
        }

        return response()->json([
            'message' => 'Você não tem permissão para realizar esta ação.'
        ], 403);
    }
}
