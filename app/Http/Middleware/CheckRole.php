<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{

    public function handle(Request $request, Closure $next, ...$roles)
    {

        if (!$request->user()) {
            return response()->json(['message' => 'Unauthorized'], 401); // Unauthorized
        }

        if (in_array($request->user()->role, $roles)) {
            return $next($request);
        }

        return response()->json(['message' => 'Forbidden: Insufficient permissions'], 403); // Forbidden
    }
}
