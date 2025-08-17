<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureServiceClientRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has service_client role
        if (auth()->user()->role !== 'service_client') {
            abort(403, 'Accès refusé. Rôle service client requis.');
        }

        return $next($request);
    }
}
