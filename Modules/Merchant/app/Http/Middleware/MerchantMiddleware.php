<?php

namespace Modules\Merchant\Http\Middleware;

use App\Traits\ApiValidation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class MerchantMiddleware
{
    use ApiValidation;

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->type === 'merchant') {
            return $next($request);
        }
        $message = 'Access denied for merchant.';
        if ($this->isApiRequest()) {
            return response()->json($this->withErrors($message));
        }
        abort(403,$message);
    }
}
