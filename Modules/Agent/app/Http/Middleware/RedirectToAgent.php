<?php

namespace Modules\Agent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectToAgent
{

    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();

        if ($user && $user->type === 'agent') {
            $path = $request->path();

            if (str_starts_with($path, 'user/')) {
                $agentPath = preg_replace('/^user\//', 'agent/', $path);

                $routeName = $request->route()?->getName();
                $agentRouteName = preg_replace('/^user\./', 'agent.', $routeName);

                if ($routeName && \Route::has($agentRouteName)) {
                    return redirect()->route($agentRouteName, $request->route()->parameters());
                }

                return redirect($request->getBaseUrl() . '/' . $agentPath);
            }
        }

        return $next($request);
    }
}
