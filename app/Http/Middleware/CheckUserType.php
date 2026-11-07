<?php

namespace App\Http\Middleware;

use App\Traits\ApiValidation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckUserType
{
    use ApiValidation;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */

    public function handle(Request $request, Closure $next, ...$types): Response
    {
        try {
            $user = Auth::user();

            throwIfInvalid(!$user,'Unauthorized user');

            if (!in_array($user->type, $types)) {
                throw new \Exception("Access denied for $user->type");
            }

            return $next($request);

        } catch (\Throwable $e) {
            if ($this->isApiRequest()) {
                return response()->json($this->withErrors($e->getMessage()));
            }
            abort(403, $e->getMessage());
        }
    }

}
