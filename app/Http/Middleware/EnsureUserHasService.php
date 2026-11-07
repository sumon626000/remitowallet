<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserHasService
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure $next
	 * @return mixed
	 */
	public function handle(Request $request, Closure $next, $service)
	{
		if (basicControl()->{$service}) {
			return $next($request);
		}
		return redirect(userRoute('dashboard'))->with('error', 'This service is not available right now.');
	}
}
