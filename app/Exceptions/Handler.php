<?php

namespace App\Exceptions;

use App\Traits\ApiValidation;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

use Illuminate\Auth\AuthenticationException;

class Handler extends ExceptionHandler
{
    use ApiValidation;
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
            //
        });
    }


    protected function unauthenticated($request, AuthenticationException $exception)
    {
        $message = "Unauthenticated. Please login first";
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json($this->withErrors($message), 401);
        }

        $guard = data_get($exception->guards(), 0);
        $login = match ($guard) {
            'admin' => route('admin.login'),
            default => route('login'),
        };
        return redirect()->guest($login)->with('error', $message);
    }

}
