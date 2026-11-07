<?php

namespace Modules\Merchant\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectToMerchant
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->type === 'merchant') {
            $path = $request->path();

            if (str_starts_with($path, 'user/')) {
                $merchantPath = preg_replace('/^user\//', 'merchant/', $path);

                $routeName = $request->route()?->getName();
                $merchantRouteName = preg_replace('/^user\./', 'merchant.', $routeName);

                if ($routeName && \Route::has($merchantRouteName)) {
                    return redirect()->route($merchantRouteName, $request->route()->parameters());
                }

                return redirect($request->getBaseUrl() . '/' . $merchantPath);
            }
        }

        return $next($request);
    }
}
