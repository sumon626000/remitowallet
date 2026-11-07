<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset requests
    | and uses a simple trait to include this behavior. You're free to
    | explore this trait and override any methods you wish to tweak.
    |
    */
    use ResetsPasswords;

    protected $maxAttempts = 3; // Change this to 4 if you want 4 tries
    protected $decayMinutes = 5; // Change this according to your


    protected $redirectTo = 'admin/dashboard';

    public function showResetForm(Request $request, $token = null)
    {
        return view('admin.auth.passwords.reset')->with(
            ['token' => $token, 'email' => $request->email]
        );
    }

    public function broker()
    {
        return Password::broker('admins');
    }

    protected function guard()
    {
        return Auth::guard('admin');
    }

    public function reset(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|confirmed|min:5',
            'token' => 'required',
        ]);
        $token = rawurldecode($request->token);

        $passwordReset = DB::table('password_reset_tokens')->where('email', $request->email)->first();
        if (!$passwordReset || $passwordReset->token != $token) {
            return back()->withErrors(['email' => 'This password reset token is invalid.']);
        }

        $user = Admin::where('email', $request->email)->first();
        $user->password = bcrypt($request->password);
        $user->save();
        //DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return redirect()->route('admin.login')->with('success', 'Your password has been reset!');
    }

}
