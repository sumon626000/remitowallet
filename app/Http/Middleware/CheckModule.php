<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;

class CheckModule
{
	/**
	 * Handle an incoming request.
	 *
	 * @param \Illuminate\Http\Request $request
	 * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
	 * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
	 */
	public function handle(Request $request, Closure $next, $role)
	{
		$loginUser = auth()->user();

		if ($loginUser->status != 1) {
			abort(403);
		}
		if ($loginUser->role_id == null) {
			return $next($request);
		}

		$checkAllow = Role::where('status', 1)->findOrFail($loginUser->role_id);

		if (in_array($role, $checkAllow->permission)) {
			return $next($request);
		} else {
			abort(403);
		}
	}
}
