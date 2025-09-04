<?php

namespace App\Http\Middleware;

use Closure;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class JwtMiddleware
{
    public function handle($request, Closure $next)
    {
        try {
            $user = JWTAuth::parseToken()->authenticate();
            if (!$user) {
                return response()->json(['success' => false, 'message' => 'User not found'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['success' => false, 'message' => 'Token error: ' . $e->getMessage()], 401);
        }

        // attach user to request -> is optionally
        $request->attributes->set('auth_user', $user);

        return $next($request);
    }
}
