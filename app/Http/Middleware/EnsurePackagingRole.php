<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePackagingRole
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        // Check if user has packaging_agent role
        if (!auth()->user()->isPackagingAgent()) {
            abort(403, 'Accès refusé. Rôle packaging agent requis.');
        }

        return $next($request);
    }
}

