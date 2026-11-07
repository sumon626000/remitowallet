<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\NotificationPermission;
use App\Models\NotificationTemplate;
use App\Models\TwoFactorSetting;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class UserSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function index(Request $request)
    {
        if ($request->method() == 'GET') {
            $data['currencies'] = Currency::where('is_active', 1)->orderBy('name', 'asc')->get();
            return view('user.setting.index', $data);
        }
        if ($request->method() == 'POST') {
            $user = Auth::user();
            $user->store_currency_id = $request->currency;
            $user->qr_currency_id = $request->qr_currency_id;
            $user->save();

            return back()->with('success', 'Updated Successfully');
        }
    }

    public function apiKey(Request $request)
    {
        $user = auth()->user();
        if ($request->method() == 'GET') {
            $public_key = $user->public_key;
            $secret_key = $user->secret_key;
            if (!$public_key || !$secret_key) {
                $user->public_key = bin2hex(random_bytes(20));
                $user->secret_key = bin2hex(random_bytes(20));
                $user->save();
            }
            return view('user.api.index', compact('user'));
        }
        if ($request->method() == 'POST') {
            $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
            $purifiedData = Purify::clean($request->all());
            $rules['security_pin'] = 'required|integer|digits:5';
            $validate = Validator::make($request->all(), $rules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
            }

            $user->public_key = bin2hex(random_bytes(20));
            $user->secret_key = bin2hex(random_bytes(20));
            $user->save();
            return back()->with('success', 'Api key generated successfully');
        }
    }

    public function modeChange($mode)
    {
        $user = auth()->user();
        if ($mode == 'test') {
            $user->mode = 0;
            $user->save();
            return redirect()->route('user.api.key')->with('success', 'Mode Updated');
        } else {
            $user->mode = 1;
            $user->save();
            return redirect()->route('user.api.key')->with('success', 'Mode Updated');
        }
    }

    public function apiDocx()
    {
        return view('user.api.docx');
    }


    public function notificationsDD()
    {
        $data['user'] = User::with('notifypermission')->findOrFail($this->user->id);
        $data['notificationTemplates'] = NotificationTemplate::where('notify_for', 0)->get()->unique('template_key');

        return view('user.setting.notifyTemplate', $data);
    }

    public function notifications()
    {
        $user = User::with('notifypermission')->findOrFail(auth()->id());
        $role = $user->type;

        $allowed = match ($role) {
            'user' => [1, 2, 5, 6],
            'agent' => [1, 3, 5, 7],
            'merchant' => [1, 4, 6, 7],
            default => [1],
        };

        $notificationTemplates = NotificationTemplate::where('notify_for', 0)
            ->whereIn('type', $allowed)
            ->get()
            ->unique('template_key');

        return view('user.setting.notifyTemplate',compact('user','notificationTemplates'));
    }


    public function notifyPermissionChange(Request $request)
    {
        try {
            $user = auth()->user();

            NotificationPermission::updateOrCreate(
                [
                    'notifyable_id' => $user->id,
                    'notifyable_type' => User::class,
                ],
                [
                    'template_email_key' => $request->email_key ?? [],
                    'template_sms_key' => $request->sms_key ?? [],
                    'template_push_key' => $request->push_key ?? [],
                    'template_in_app_key' => $request->in_app_key ?? [],
                ]
            );

            return back()->with('success', 'Notification Permission Updated Successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }


}
