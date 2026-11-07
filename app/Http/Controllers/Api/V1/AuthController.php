<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Mail\SendMail;
use App\Models\Currency;
use App\Models\Fund;
use App\Models\Language;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Traits\ApiValidation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
	use ApiValidation;

	public function registerUserForm()
	{
		try {
			$info = json_decode(json_encode(getIpInfo()), true);
			$country_code = null;
			if (!empty($info['code'])) {
				$data['country_code'] = @$info['code'][0];
			}
			$data['countries'] = config('country');
			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e));
		}
	}

	public function registerUser(Request $request)
	{
		$basic = basicControl();
		try {
            $basic = basicControl();
            $data = $request->all();
            $registerRules = [
                'firstname' => 'required|string',
                'lastname' => 'required|string',
                'username' => 'required|string|alpha_dash|min:5|unique:users,username',
                'email' => 'required|string|email|unique:users,email',
                'password' => $basic->strong_password == 0 ?
                    ['required', 'confirmed', 'min:6'] :
                    ['required', 'confirmed',  Password::min(6)->mixedCase()
                        ->letters()
                        ->numbers()
                        ->symbols()
                        ->uncompromised()],
                'phone' => ['required', 'numeric', 'unique:users,phone'],
                'phone_code' => ['required', 'numeric'],
                'country' => ['required'],
                'country_code' => ['required']

            ];

            $message = [
                'password.letters' => 'password must be contain letters',
                'password.mixed' => 'password must be contain 1 uppercase and lowercase character',
                'password.symbols' => 'password must be contain symbols',
            ];
            $validation = Validator::make($request->all(), $registerRules,$message);
            if ($validation->fails()) {
                return response()->json($this->withErrors(collect($validation->errors())->collapse()));
            }

			if ($request->sponsor != null) {
				$sponsorId = User::where('username', $request->sponsor)->first()->id;
			} else {
				$sponsorId = null;
			}
			$user = User::create([
                'firstname' => $data['firstname'],
                'lastname' => $data['lastname'],
                'username' => $data['username'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'phone' => $data['phone'],
                'phone_code' => $data['phone_code'],
                'country' => $data['country'],
                'country_code' => $data['country_code'],
				'referral_id' => $sponsorId,
				'language_id' => Language::select('id')->where('default_status', true)->first()->name ?? null,
                'email_verification' => ($basic->email_verification) ? 0 : 1,
                'sms_verification' => ($basic->sms_verification) ? 0 : 1,
				'qr_link' => strRandom(20),
			]);


            $user->two_fa_verify = ($user->two_fa == 1) ? 0 : 1;
			$user->save();

			$currencies = Currency::All();
			foreach ($currencies as $currency) {
				Wallet::firstOrCreate(['user_id' => $user->id, 'currency_id' => $currency->id]);
			}

			if ($user && basicControl()->joining_bonus > 0 && basicControl()->signup_bonus_status == 1) {
				$fund = new Fund();
				$fund->user_id = $user->id;
				$fund->currency_id = basicControl()->base_currency;
				$fund->percentage = 0;
				$fund->charge_percentage = 0;
				$fund->charge_fixed = 0;
				$fund->charge = 0;
				$fund->amount = basicControl()->joining_bonus;
				$fund->email = $user->email;
				$fund->status = 1;
				$fund->note = 'Joining bonus';
				$fund->utr = (string)Str::uuid();
				$fund->save();

				updateWallet($fund->user_id, $fund->currency_id, $fund->amount, 1);
				$transaction = new Transaction();
				$transaction->amount = $fund->amount;
				$transaction->charge = $fund->charge;
				$transaction->currency_id = $fund->currency_id;
				$fund->transactional()->save($transaction);
			}

			$user->save();


			return response()->json([
				'status' => 'success',
				'message' => 'User Created Successfully',
				'token' => $user->createToken("API TOKEN")->plainTextToken
			]);


		} catch (\Exception $th) {
			return response()->json($this->withErrors($th->getMessage()));
		}
	}

    public function loginUser(Request $request)
    {
        $validateUser = Validator::make($request->all(),
            [
                'username' => 'required|string',
                'password' => 'required|string',
                'type' => ['required', Rule::in(getAllowedUserTypes())],
            ]);
    
        if ($validateUser->fails()) {
            return response()->json($this->withErrors(collect($validateUser->errors())->collapse()[0]));
        }
    
        try {
    
    
            $user = User::where('username', $request->username)
                        ->orWhere('email', $request->username)
                        ->first();
    
            if (!$user) {
                return response()->json($this->withErrors('Invalid username or email'));
            }
    
            if ($user->type !== $request->type) {
                return response()->json($this->withErrors("You are not registered as {$request->type}."));
            }
    
            if (!in_array($user->type, getAllowedUserTypes())) {
                return response()->json($this->withErrors("Login restricted for $user->type."));
            }
    
    
            
    
            $user->two_fa_verify = ($user->two_fa == 1) ? 0 : 1;
            $user->save();
    
            return response()->json([
                'status' => 'success',
                'message' => 'Logged in successfully',
                'token' => $user->createToken("API TOKEN")->plainTextToken
            ]);
    
        } catch (\Throwable $th) {
            return response()->json($this->withErrors($th->getMessage()));
        }
    }

	public function getEmailForRecoverPass(Request $request)
	{
		$validateUser = Validator::make($request->all(),
			[
				'email' => 'required|email',
			]);

		if ($validateUser->fails()) {
			return response()->json($this->withErrors(collect($validateUser->errors())->collapse()[0]));
		}

		try {
			$user = User::where('email', $request->email)->first();
			if (!$user) {
				return response()->json($this->withErrors('Email does not exit on record'));
			}

			$code = rand(10000, 99999);
			$data['email'] = $request->email;
			$data['message'] = 'OTP has been send';
			$user->verify_code = $code;
			$user->save();

			$basic = basicControl();
			$message = 'Your Password Recovery Code is ' . $code;
			$email_from = $basic->sender_email;
			@Mail::to($request->email)->send(new SendMail($email_from, "Recovery Code", $message));

			return response()->json($this->withSuccess($data));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function getCodeForRecoverPass(Request $request)
	{
		$validateUser = Validator::make($request->all(),
			[
				'code' => 'required',
				'email' => 'required|email',
			]);

		if ($validateUser->fails()) {
			return response()->json($this->withErrors(collect($validateUser->errors())->collapse()[0]));
		}

		try {
			$user = User::where('email', $request->email)->first();
			if (!$user) {
				return response()->json($this->withErrors('Email does not exit on record'));
			}

			if ($user->verify_code == $request->code && $user->updated_at > Carbon::now()->subMinutes(5)) {
				$user->verify_code = null;
				$user->save();
				return response()->json($this->withSuccess('Code Matching'));
			}

			return response()->json($this->withErrors('Invalid Code'));
		} catch (\Exception $e) {
			return response()->json($this->withErrors($e->getMessage()));
		}
	}

	public function updatePass(Request $request)
	{
		if (config('basic.strong_password') == 0) {
			$rules['password'] = ['required', 'min:6', 'confirmed'];
		} else {
			$rules['password'] = ["required", 'confirmed',
				Password::min(6)->mixedCase()
					->letters()
					->numbers()
					->symbols()
					->uncompromised()];
		}
		$rules['email'] = ['required', 'email'];

		$validateUser = Validator::make($request->all(), $rules);

		if ($validateUser->fails()) {
			return response()->json($this->withErrors(collect($validateUser->errors())->collapse()[0]));
		}

		$user = User::where('email', $request->email)->first();
		if (!$user) {
			return response()->json($this->withErrors('Email does not exist on record'));
		}
		$user->password = Hash::make($request->password);
		$user->save();
		return response()->json($this->withSuccess('Password Updated'));
	}

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json($this->withSuccess('User is logged out successfully'));
    }

}
