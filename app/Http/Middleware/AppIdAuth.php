<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AppIdAuth
{
    public function handle(Request $request, Closure $next)
    {
        $appId = $request->header('X-App-ID'); // Espera o App-ID no cabeçalho da requisição

        if (!$appId || !User::where('app_id', $appId)->exists()) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $user = User::where('app_id', $appId)->first();
        Auth::login($user); // Autentica o usuário

        return $next($request);
    }
}
