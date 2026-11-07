<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\Admin;
use App\Traits\Notify;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;

class ForgotPasswordController extends Controller
{
    use SendsPasswordResetEmails,Notify;

    public function showLinkRequestForm()
    {
        return view('admin.auth.passwords.email');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $this->validateEmail($request);
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);

    }

    protected function sendResetLinkResponse(Request $request, $response)
    {
        $userEmail = $request->get('email');
        $passwordReset = DB::table('password_reset_tokens')->where('email', $userEmail)->first();
        if (!$passwordReset) {
            return back()->with('error','Password reset token not found. Please try again.');
        }
        $token = $passwordReset->token;
        $resetUrl = url(basicControl()->admin_prefix.'/password/reset', rawurlencode($token)) . '?email='.$userEmail;
        $message = 'Your Password Recovery Link: <a href="' . $resetUrl . '" target="_blank">Click To Reset Password</a>';
        $emailFrom = basicControl()->sender_email;

        try {
            $user = Admin::where('email', $userEmail)->first();
            $params = [
                'message' => $message
            ];
            //$this->mail($user, 'PASSWORD_RESET', $params);

            Mail::to($userEmail)->send(new SendMail($emailFrom, 'Password Recovery', $message));
        } catch (\Exception $e) {
            return back()->with('error','Failed to send the password reset email. Please try again later.');
        }

        return back()->with('success','Password reset email successfully sent.');
    }


    public function broker()
    {
        return Password::broker('admins');
    }
}
