<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
public function handle(Request $request, Closure $next, string $role): Response
{
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    // Map admin requests to packaging role
    if ($role === 'admin' && auth()->user()->role === 'packaging') {
        return $next($request);
    }

    if (auth()->user()->role !== $role) {
        abort(403, 'Access denied. Required role: ' . $role);
    }

    return $next($request);
}
}
