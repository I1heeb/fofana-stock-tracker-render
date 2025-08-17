<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Auth;
use Sentry\State\Scope;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            if ($this->shouldReport($e) && app()->bound('sentry')) {
                \Sentry\withScope(function (Scope $scope) use ($e): void {
                    // Add user context
                    if ($user = Auth::user()) {
                        $scope->setUser([
                            'id' => $user->id,
                            'email' => $user->email,
                            'role' => $user->role,
                        ]);
                    }

                    // Add additional context
                    $scope->setExtra('url', request()->fullUrl());
                    $scope->setExtra('input', request()->except($this->dontFlash));
                    
                    if (app()->environment('production')) {
                        $scope->setExtra('server', [
                            'memory_usage' => memory_get_usage(true),
                            'cpu_usage' => sys_getloadavg()[0],
                        ]);
                    }

                    \Sentry\captureException($e);
                });
            }
        });
    }
} 