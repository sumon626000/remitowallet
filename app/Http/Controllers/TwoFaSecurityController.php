<?php

namespace App\Http\Controllers;

use App\Helpers\UserSystemInfo;
use App\Models\SecurityQuestion;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Traits\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;
use Stevebauman\Purify\Facades\Purify;

class TwoFaSecurityController extends Controller
{
    use Notify;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
    }

    public function twoStepSecurity()
    {
        $basic = basicControl();
        $user = auth()->user();

        $google2fa = new Google2FA();
        $secret = $user->two_fa_code ?? $this->generateSecretKeyForUser($user);

        $qrCodeUrl = $google2fa->getQRCodeUrl(
            auth()->user()->username,
            $basic->site_title,
            $secret
        );
        $qrCodeUrl = 'https://quickchart.io/qr?text=' . urlencode($qrCodeUrl);
        return view('user.twoFA.index', compact('secret', 'qrCodeUrl'));
    }

    private function generateSecretKeyForUser(User $user)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user->update(['two_fa_code' => $secret]);

        return $secret;
    }

    public function twoStepEnable(Request $request)
    {
        $user = $this->user;
        $secret = auth()->user()->two_fa_code;
        $google2fa = new Google2FA();
        $valid = $google2fa->verifyKey($secret, $request->code);
        if ($valid) {
            $user['two_fa'] = 1;
            $user['two_fa_verify'] = 1;
            $user->save();

            $this->mail($user, 'TWO_STEP_ENABLED', [
                'action' => 'Enabled',
                'code' => $request->code,
                'ip' => request()->ip(),
                'browser' => UserSystemInfo::get_browsers() . ', ' . UserSystemInfo::get_os(),
                'time' => date('d M, Y h:i:s A'),
            ]);

            return back()->with('success', 'Google Authenticator Has Been Enabled.');
        } else {

            return back()->with('error', 'Wrong Verification Code.');
        }

    }

    public function twoStepDisable(Request $request)
    {
        $this->validate($request, [
            'password' => 'required',
        ]);
        if (!Hash::check($request->password, auth()->user()->password)) {
            return back()->with('error', 'Incorrect password. Please try again.');
        }
        auth()->user()->update([
            'two_fa' => 0,
            'two_fa_verify' => 1,
        ]);

        return redirect()->route('user.dashboard')->with('success', 'Two-step authentication disabled successfully.');
    }

    public function twoStepRegenerate()
    {
        $user = $this->user;
        $user->two_fa_code = null;
        $user->save();
        session()->flash('success','Re-generate Successfully');
        return redirect()->route('user.twostep.security');
    }


    public function manage(Request $request)
    {
        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
        if (is_null($twoFactorSetting->security_pin)) {
            return redirect(userRoute('securityPin.create'));
        }
        if ($request->isMethod('get')) {
            $data['enable_for'] = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);
            $data['twoFactorSetting'] = $twoFactorSetting;

            $transactionType = config('transactionType');
            $user = auth()->user();
            if ($user && isset($user->type)) {
                switch ($user->type) {
                    case 'user':
                        if (module_exists('Agent')) {
                            unset($transactionType['cash_in']);
                        }
                        break;
                    case 'agent':
                        $transactionType = Arr::except($transactionType,
                            ['transfer','request','exchange','redeem','escrow',
                                'voucher','invoice','virtual_card','bill_payment','cash_out','make_payment'
                            ]);
                        break;
                    case 'merchant':
                        if (module_exists('Merchant')) {
                            $transactionType = Arr::except($transactionType,
                                ['transfer','request','exchange','redeem','escrow','cash_in','cash_out',
                                    'voucher','deposit', 'invoice','virtual_card','bill_payment','make_payment'
                                ]);
                        }
                        break;
                }
            }
            $data['transactionType'] = $transactionType;

            return view('user.twoFactor.manage', $data);
        }
        elseif ($request->isMethod('post')) {
            $purifiedData = Purify::clean($request->all());

            $validationRules = [
                'security_pin' => 'required|integer|digits:5',
            ];
            $validate = Validator::make($purifiedData, $validationRules);

            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('alert', 'You have entered an incorrect PIN')->withInput();
            }

            $purifiedData = (object)$purifiedData;

            $enable_for = isset($purifiedData->enable_for) ? json_encode($purifiedData->enable_for) : '[]';
            $twoFactorSetting->enable_for = $enable_for;
            $twoFactorSetting->save();
            return back()->with('success', 'Update Successfully');
        }
    }

    public function reset(Request $request)
    {
        $twoFactorSetting = TwoFactorSetting::with('securityQuestion')->firstOrCreate(['user_id' => Auth::id()]);
        if (is_null($twoFactorSetting->security_pin)) {
            return redirect(userRoute('securityPin.create'));
        }
        if ($request->isMethod('get')) {
            $data['twoFactorSetting'] = $twoFactorSetting;
            return view('user.twoFactor.reset', $data);
        }
        elseif ($request->isMethod('post')) {
            $purifiedData = Purify::clean($request->all());
            $validationRules = [
                'answer' => 'required|min:1',
                'old_security_pin' => 'required|integer|digits:5',
                'security_pin' => 'required|confirmed|integer|digits:5',
            ];
            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }

            $purifiedData = (object)$purifiedData;
            if ($twoFactorSetting->answer !== $purifiedData->answer) {
                return back()->with('error', 'Security answer did not match')->withInput();
            } elseif (!Hash::check($purifiedData->old_security_pin, $twoFactorSetting->security_pin)) {
                return back()->with('error', 'Old pin did not match')->withInput();
            } else {
                $twoFactorSetting->security_pin = bcrypt($purifiedData->security_pin);
                $twoFactorSetting->save();
                return back()->with('success', 'PIN reset successfully');
            }
        }
    }

    public function create()
    {
        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
        if (isset($twoFactorSetting->security_pin)) {
            return redirect(route('user.securityPin.reset'));
        }
        $data['securityQuestions'] = SecurityQuestion::all();

        return view('user.twoFactor.create', $data);
    }

    public function store(Request $request)
    {
        $purifiedData = Purify::clean($request->all());

        $validationRules = [
            'security_question' => 'required|integer|min:1|not_in:0|exists:security_questions,id',
            'answer' => 'required|min:1',
            'hints' => 'required|min:1',
            'security_pin' => 'required|confirmed|integer|digits:5',
        ];

        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $purifiedData = (object)$purifiedData;
        $twoFactorSetting = TwoFactorSetting::where('user_id', Auth::id())->first();
        $twoFactorSetting->security_question_id = $purifiedData->security_question;
        $twoFactorSetting->answer = $purifiedData->answer;
        $twoFactorSetting->hints = $purifiedData->hints;
        $twoFactorSetting->security_pin = bcrypt($purifiedData->security_pin);
        $twoFactorSetting->save();

        return redirect(route('user.securityPin.reset'))->with('success', 'Security PIN Saved Successfully');
    }


}
