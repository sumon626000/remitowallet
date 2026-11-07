<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Stevebauman\Purify\Facades\Purify;

class ValidateRequestData
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        foreach ($request->all() as $key => $req) {
            unset($request['filepond']);
            if (!$request->hasFile($key) && $key != 'email_template' && $key != 'email_description') {
                $request[$key] = isset($req) ? Purify::clean($req) : null;
            }
        }
        return $next($request);
    }
}
