<?php
/****** Another website produced by The Lochlite & Lochpay Company
___
|   |
|   |             _______         ______       __      __
|   |            /       \       /  ____|     |  |    |  |
|   |______     /         \     /  /          |  |----|  |
|          |    \         /     \  \____      |  |----|  |
|__________|     \_______/       \______|     |__|    |__| Lite ®


Long live Lochlite! ******/

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Closure;

class IsAdmin 
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth()->User();

        if (!$user->type == 'admin') {
            return response()->json(['message' => 'Conta invalida para essa solicitação.', 'errors' => []], 403);
        }

        return $next($request);
    }
}
