<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Session\TokenMismatchException;

class HandleCsrfErrors
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (TokenMismatchException $e) {
            // Si c'est une requête AJAX, retourner JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'CSRF token mismatch. Please refresh the page.',
                    'error' => 'token_mismatch',
                    'redirect' => $request->url()
                ], 419);
            }

            // Pour les requêtes normales, rediriger vers la page de login avec un message
            return redirect()->route('login')
                ->withErrors(['csrf' => 'Your session has expired. Please login again.'])
                ->with('warning', 'Your session has expired. Please login again.');
        }
    }
}
