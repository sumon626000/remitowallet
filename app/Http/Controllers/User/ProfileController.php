<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Kyc;
use App\Models\Language;
use App\Models\UserLogin;
use App\Rules\PhoneLength;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Stevebauman\Purify\Facades\Purify;

class ProfileController extends Controller
{
    use Upload;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
    }

    public function changePassword(Request $request)
    {
        $purifiedData = Purify::clean($request->all());
        $validator = Validator::make($purifiedData, [
            'current_password' => 'required|min:5',
            'password' => 'required|min:5|confirmed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        try {
            $user = Auth::user();
            $purifiedData = (object)$purifiedData;

            if (!Hash::check($purifiedData->current_password, $user->password)) {
                return back()->withInput()->withErrors(['current_password' => 'current password did not match']);
            }

            $user->password = bcrypt($purifiedData->password);
            $user->save();

            return back()->with('success', 'Password changed successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function index(Request $request)
    {
        $userProfile = $this->user;
        $countries = config('country');
        $country_code = $userProfile->phone_code;

        if ($request->isMethod('get')) {
            $languages = Language::select('id', 'name')->where('status', 1)->orderBy('name', 'ASC')->get();
            $data['userLoginInfo'] = UserLogin::where('user_id',auth()->id())->orderBy('id', 'desc')->limit(5)->get();
            return view('user.profile.show', $data, compact('country_code', 'userProfile', 'countries', 'languages'));
        }
        elseif ($request->isMethod('post')) {
            $purifiedData = Purify::clean($request->all());

            $validator = Validator::make($purifiedData, [
                'firstname' => 'required|min:3|max:100|string',
                'lastname' => 'required|min:3|max:100|string',
                'username' => 'sometimes|required|min:5|max:50|unique:users,username,' . $userProfile->id,
                'email' => 'sometimes|required|min:5|max:50|unique:users,email,' . $userProfile->id,
                'language' => 'required|integer|not_in:0|exists:languages,id',
                'timezone' => 'required',
                'city' => 'nullable|max:100',
                'state' => 'nullable|max:100',
                'zip_code' => 'nullable|max:100',
                'address_one' => 'nullable|max:2500',
                'address_two' => 'nullable|max:2500',
                'phone' => ['required', 'string', "unique:users,phone, $userProfile->id",new PhoneLength($phoneCode = $request->input('phone_code'))],
            ]);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            try {
                $purifiedData = (object)$purifiedData;
                if ($purifiedData->email != $userProfile->email) {
                    $userProfile->email_verification = 0;
                }
                if ($purifiedData->phone != $userProfile->phone) {
                    $userProfile->sms_verification = 0;
                }

                $userProfile->firstname = $purifiedData->firstname;
                $userProfile->lastname = $purifiedData->lastname;
                $userProfile->username = $purifiedData->username;
                $userProfile->email = $purifiedData->email;
                $userProfile->address_one = $purifiedData->address ?? null;
                $userProfile->phone = $purifiedData->phone;
                $userProfile->phone_code = $purifiedData->phone_code ?? $userProfile->phone_code;
                $userProfile->language_id = $purifiedData->language;
                $userProfile->time_zone = $purifiedData->timezone;
                $userProfile->country_code = Str::upper($purifiedData->country_code);
                $userProfile->country = $purifiedData->country;
                $userProfile->city = $purifiedData->city;
                $userProfile->state = $purifiedData->state;
                $userProfile->zip_code = $purifiedData->zip_code;
                $userProfile->address_one = $purifiedData->address_one;
                $userProfile->address_two = $purifiedData->address_two;


                if ($request->file('profile_picture') && $request->file('profile_picture')->isValid()) {
                    $extension = $request->profile_picture->extension();
                    $profileName = strtolower($userProfile->username . '.' . $extension);
                    $image = $this->fileUpload($request->profile_picture, config('filelocation.userProfile.path'), $profileName, null, 'webp', 80, $userProfile->image, $userProfile->image_driver);
                    if ($image) {
                        $userProfile->image = $image['path'];
                        $userProfile->image_driver = $image['driver'];
                    }
                }
                $userProfile->save();
                return back()->with('success', 'Profile Update Successfully');

            } catch (\Exception $e) {
                return back()->with('error', $e->getMessage());
            }
        }
    }


    public function profileUpdate(Request $request)
    {
        $languages = Language::all()->map(function ($item) {
            return $item->id;
        });
        throw_if(!$languages, 'Language not found.');

        $req = $request->except('_method', '_token');
        $user = Auth::user();
        $rules = [
            'first_name' => 'required|string|min:1|max:100',
            'last_name' => 'required|string|min:1|max:100',
            'email' => 'required|email:rfc,dns',
            'phone' => 'required|min:1|max:50',
            'username' => "sometimes|required|alpha_dash|min:5|unique:users,username," . $user->id,
            'address' => 'required|string|min:2|max:500',
            'language_id' => Rule::in($languages),
        ];
        $message = [
            'firstname.required' => 'First name field is required',
            'lastname.required' => 'Last name field is required',
        ];

        $validator = Validator::make($req, $rules, $message);
        if ($validator->fails()) {
            $validator->errors()->add('profile', '1');
            return back()->withErrors($validator)->withInput();
        }
        try {
            $response = $user->update([
                'language_id' => $req['language_id'],
                'firstname' => $req['first_name'],
                'lastname' => $req['last_name'],
                'email' => $req['email'],
                'phone' => $req['phone'],
                'username' => $req['username'],
                'address_one' => $req['address'],
            ]);

            throw_if(!$response, 'Something went wrong, While updating profile data');
            return back()->with('success', 'Profile updated Successfully.');
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function updatePassword(Request $request)
    {
        $rules = [
            'current_password' => "required",
            'password' => "required|min:5|confirmed",
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $user = Auth::user();
        try {
            if (Hash::check($request->current_password, $user->password)) {
                $user->password = bcrypt($request->password);
                $user->save();
                return back()->with('success', 'Password Changes successfully.');
            } else {
                throw new \Exception('Current password did not match');
            }
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


}
