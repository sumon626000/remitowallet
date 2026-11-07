<?php

namespace Modules\Merchant\Http\Middleware;

use App\Traits\ApiValidation;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class MerchantAccessMiddleware
{
    use ApiValidation;

    protected array $allowedRoutes = [
        'dashboard',
        'confirm.success',
        'payout', 'payout.*','payout.supported.currency',
        'transaction*',
        'profile*',
        'setting*',
        'notification*',
        'kyc*',
        'toggle.layout',
        'twoStep*',
        'twostep.security',
        'securityPin.*',
        'qr.*',
        'verification.*',
        'ticket.*',
        'change.password',
        'push.notification.*',
    ];

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->type === 'merchant') {
            $routeName = $request->route()?->getName();
            if (!$routeName) {
                $message = 'Access denied: You do not have permission to access this route as an merchant.';
                if ($this->isApiRequest()) {
                    return response()->json($this->withErrors($message));
                }
                abort(403, $message);
            }
            $strippedRoute = preg_replace('/^(merchant|user)\./', '', $routeName);
            foreach ($this->allowedRoutes as $allowed) {
                if (Str::is($allowed, $strippedRoute)) {
                    return $next($request);
                }
            }

            $message = 'Access denied: This section is not available for merchant.';
            if ($this->isApiRequest()) {
                return response()->json($this->withErrors($message));
            }
            abort(403, $message);

        }
        return $next($request);
    }
}
