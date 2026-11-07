<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class VerifyUser
{
	public function handle($request, Closure $next)
	{
		$user = Auth::user();
		if($user->status && $user->email_verification && $user->sms_verification && $user->two_fa_verify){
			return $next($request);
		}
		return redirect(route('user.check'));
	}
}
