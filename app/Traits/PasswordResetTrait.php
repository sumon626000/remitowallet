<?php

namespace App\Traits;

use App\Mail\SendMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

trait PasswordResetTrait
{

    public function resetLinkEmail(Request $request, $isApi = false)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
        ]);

        try {
            $token = Str::random(64);
            DB::table('password_reset_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now()
                ]
            );

            $userEmail = $request->email;
            $resetUrl = url('password/reset', $token) . '?email=' . $userEmail;
            $message = 'Your Password Recovery Link: <a href="' . $resetUrl . '" target="_blank">Click To Reset Password</a>';

            $basic = basicControl();
            $email_from = $basic->sender_email;
            Mail::to($request->email)->send(new SendMail($email_from, "Password Recovery", $message));

            return $this->sendSuccessResponse($isApi);
        } catch (\Exception $e) {
            return $this->sendErrorResponse($e->getMessage(), $isApi);
        }
    }

    protected function sendSuccessResponse($isApi)
    {
        if ($isApi) {
            return response()->json($this->withSuccess('We have emailed your password reset link!'));
        } else {
            return back()->with('success', 'We have emailed your password reset link!');
        }
    }

    protected function sendErrorResponse($message, $isApi)
    {
        if ($isApi) {
            return response()->json($this->withError($message), 500);
        } else {
            return back()->with('error', $message);
        }
    }

    public function withSuccess($msg): array
    {
        return [
            'status' => 'success',
            'message' => $msg
        ];
    }

    public function withError($msg): array
    {
        return [
            'status' => 'error',
            'message' => $msg
        ];
    }


}
