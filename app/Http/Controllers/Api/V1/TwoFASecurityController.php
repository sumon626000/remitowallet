<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\GoogleAuthenticator;
use App\Helpers\UserSystemInfo;
use App\Http\Controllers\Controller;
use App\Models\SecurityQuestion;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Traits\ApiValidation;
use App\Traits\Notify;
use hisorange\BrowserDetect\Parser as Browser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PragmaRX\Google2FA\Google2FA;
use Stevebauman\Purify\Facades\Purify;

class TwoFASecurityController extends Controller
{
	use ApiValidation, Notify;

    private function generateSecretKeyForUser(User $user)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();
        $user->update(['two_fa_code' => $secret]);

        return $secret;
    }

    public function twoFASecurity()
    {
        $basic = basicControl();
        try {
            $user = auth()->user();
            $google2fa = new Google2FA();
            $secret = $user->two_fa_code ?? $this->generateSecretKeyForUser($user);
            $qrCodeUrl = $google2fa->getQRCodeUrl(
                auth()->user()->username,
                $basic->site_title,
                $secret
            );
            $qrCodeUrl = 'https://quickchart.io/qr?text=' . urlencode($qrCodeUrl);

            $data = [
                'twoFactorEnable' => $user->two_fa == 0 ? false : true,
                'secret' => $secret,
                'qrCodeUrl' => $qrCodeUrl,
                'downloadApp' => 'https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en',
            ];
            return response()->json($this->withSuccess($data));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }


    public function twoFASecurityEnable(Request $request)
    {
        try {
            $user = auth()->user();
            $validateUser = Validator::make($request->all(),
                [
                    'code' => 'required',
                ]);
            if ($validateUser->fails()) {
                return response()->json($this->withErrors(collect($validateUser->errors())->collapse()));
            }

            $secret = auth()->user()->two_fa_code;
            $google2fa = new Google2FA();
            $valid = $google2fa->verifyKey($secret, $request->code);
            if ($valid) {
                $user['two_fa'] = 1;
                $user['two_fa_verify'] = 1;
                $user->save();

                $this->mail($user, 'TWO_STEP_ENABLED', [
                    'action' => 'Enabled',
                    'code' => $user->two_fa_code,
                    'ip' => request()->ip(),
                    'browser' => UserSystemInfo::get_browsers() . ', ' . UserSystemInfo::get_os(),
                    'time' => date('d M, Y h:i:s A'),
                ]);

                return response()->json($this->withSuccess('Google Authenticator Has Been Enabled.'));
            } else {
                return response()->json($this->withErrors('Wrong Verification Code.'));
            }
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

    public function twoFASecurityDisable(Request $request)
    {
        try {
            $validate = Validator::make($request->all(),
                [
                    'password' => 'required',
                ]);
            if ($validate->fails()) {
                return response()->json($this->withErrors(collect($validate->errors())->collapse()));
            }

            if (!Hash::check($request->password, auth()->user()->password)) {
                return response()->json($this->withErrors('Incorrect password. Please try again.'));
            }

            $user = auth()->user();
            $user->update([
                'two_fa' => 0,
                'two_fa_verify' => 1,
            ]);

            return response()->json($this->withSuccess('Two-step authentication disabled successfully.'));
        } catch (\Exception $e) {
            return response()->json($this->withErrors($e->getMessage()));
        }
    }

	public function create()
	{
		try {
			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
			if (isset($twoFactorSetting->security_pin)) {
				$data['route'] = route('reset');
				return response()->json($this->withSuccess($data));
			}
			$data['securityQuestions'] = SecurityQuestion::all();

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
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
			return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
		}

		try {
			$purifiedData = (object)$purifiedData;
			$twoFactorSetting = TwoFactorSetting::where('user_id', Auth::id())->first();
			$twoFactorSetting->security_question_id = $purifiedData->security_question;
			$twoFactorSetting->answer = $purifiedData->answer;
			$twoFactorSetting->hints = $purifiedData->hints;
			$twoFactorSetting->security_pin = bcrypt($purifiedData->security_pin);
			$twoFactorSetting->save();

			return response()->json($this->withSuccess('Security PIN Saved Successfully'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}

	}

	public function reset(Request $request)
	{
		$twoFactorSetting = TwoFactorSetting::with('securityQuestion')->firstOrCreate(['user_id' => Auth::id()]);

		if (is_null($twoFactorSetting->security_pin)) {
			$data['route'] = route('create');
			return response()->json($this->withSuccess($data));
		}

		if ($request->method() == 'GET') {
			$data['twoFactorSetting'] = $twoFactorSetting;
			return response()->json($this->withSuccess($data));
		} elseif ($request->isMethod('post')) {
			$purifiedData = Purify::clean($request->all());
			$validationRules = [
				'answer' => 'required|min:1',
				'old_security_pin' => 'required|integer|digits:5',
				'security_pin' => 'required|confirmed|integer|digits:5',
			];
			$validate = Validator::make($purifiedData, $validationRules);
			if ($validate->fails()) {
				return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
			}

			$purifiedData = (object)$purifiedData;
			if ($twoFactorSetting->answer !== $purifiedData->answer) {
				return response()->json($this->withErrors('Security answer did not match'));
			} elseif (!Hash::check($purifiedData->old_security_pin, $twoFactorSetting->security_pin)) {
				return response()->json($this->withErrors('Old pin did not match'));
			} else {
				$twoFactorSetting->security_pin = bcrypt($purifiedData->security_pin);
				$twoFactorSetting->save();
				return response()->json($this->withSuccess('PIN reset successfully'));
			}
		}
	}

	public function manage(Request $request)
	{
		try {
			$twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => Auth::id()]);
			if (is_null($twoFactorSetting->security_pin)) {
				$data['route'] = route('create');
				return response()->json($this->withSuccess($data));
			}
			if ($request->isMethod('get')) {
				$data['enable_for'] = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);
				$data['twoFactorSetting'] = $twoFactorSetting;
				return response()->json($this->withSuccess($data));
			} elseif ($request->isMethod('post')) {
				$purifiedData = Purify::clean($request->all());

				$validationRules = [
					'security_pin' => 'required|integer|digits:5',
				];
				$validate = Validator::make($purifiedData, $validationRules);

				if ($validate->fails()) {
					return response()->json($this->withErrors(collect($validate->errors())->collapse()[0]));
				}
				if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
					return response()->json($this->withErrors('You have entered an incorrect PIN'));
				}

				$purifiedData = (object)$purifiedData;

				$enable_for = isset($purifiedData->enable_for) ? json_encode($purifiedData->enable_for) : '[]';
				$twoFactorSetting->enable_for = $enable_for;
				$twoFactorSetting->save();

				return response()->json($this->withSuccess('Update Successfully'));
			}
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}
}
