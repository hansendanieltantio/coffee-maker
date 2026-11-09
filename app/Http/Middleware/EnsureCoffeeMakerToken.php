<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCoffeeMakerToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('order_nodes.device_token');
        $providedToken = $request->bearerToken();

        if (
            ! is_string($token)
            || $token === ''
            || ! is_string($providedToken)
            || ! hash_equals($token, $providedToken)
        ) {
            return response()->json(['message' => 'Invalid device credentials.'], 401);
        }

        return $next($request);
    }
}
