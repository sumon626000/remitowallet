<?php

namespace Modules\Agent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AgentAccessMiddleware
{
    protected array $allowedRoutes = [
        'dashboard',
        'confirm.success',
        'fund.*',
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

        if ($user && $user->type === 'agent') {
            $routeName = $request->route()?->getName();
            if (!$routeName) {
                abort(403, 'Access denied: You do not have permission to access this route as an agent.');
            }
            $strippedRoute = preg_replace('/^(agent|user)\./', '', $routeName);
            foreach ($this->allowedRoutes as $allowed) {
                if (Str::is($allowed, $strippedRoute)) {
                    return $next($request);
                }
            }
            abort(403, 'Access denied: This section is not available for agent.');
        }
        return $next($request);
    }



}
