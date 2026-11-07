<?php

namespace Modules\Agent\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AgentMiddleware
{

    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && Auth::user()->type === 'agent') {
            return $next($request);
        }

        abort(403);
    }
}
