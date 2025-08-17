<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckApiToken
{
    
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('X-API-TOKEN');
        if (!$token || $token !== config('api.fixed_token')) {
            return response()->json(['message' => 'Token inválido ou não fornecido'], 401);
        }

        return $next($request);
    }
}
