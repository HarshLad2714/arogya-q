<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        $current = $user?->role?->value;

        if (! $current || ! in_array($current, $roles, true)) {
            abort(403);
        }

        return $next($request);
    }
}
