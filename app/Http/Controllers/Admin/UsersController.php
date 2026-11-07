<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\UserAllRecordDeleteJob;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Fund;
use App\Models\Gateway;
use App\Models\Language;
use App\Models\Payout;
use App\Models\PayoutMethod;
use App\Models\Transaction;
use App\Models\User;
use App\Models\UserKyc;
use App\Models\UserLogin;
use App\Models\Wallet;
use App\Rules\PhoneLength;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Validation\Rule;
use App\Traits\Upload;
use App\Traits\Notify;
use Exception;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;

class UsersController extends Controller
{
    use Upload, Notify;

    public function index($state = 'all')
    {
        $data['basic'] = basicControl();
        $data['currencies'] = Currency::select(['id', 'code', 'name'])->where('is_active', 1)->get();
        $userRecord = \Cache::get('userRecord');
        if (!$userRecord) {
            $userRecord = User::withTrashed()
                ->where('type', 'user')
                ->selectRaw('COUNT(id) AS totalUserWithTrashed')
                ->selectRaw('COUNT(CASE WHEN deleted_at IS NULL THEN id END) AS totalUser')
                ->selectRaw('(COUNT(CASE WHEN deleted_at IS NULL THEN id END) / COUNT(id)) * 100 AS totalUserPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeUser')
                ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeUserPercentage')
                ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN id END) AS todayJoin')
                ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURDATE() THEN id END) / COUNT(id)) * 100 AS todayJoinPercentage')
                ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS deactivateUser')
                ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS deactivateUserPercentage')
                ->get()
                ->toArray();
            \Cache::put('userRecord', $userRecord);
        }
        $userRecord = collect($userRecord)->collapse();
        $data['languages'] = Language::all();
        $data['allCountry'] = config('country');
        return view('admin.user_management.list', $data, compact('userRecord', 'state'));

    }

    public function search(Request $request, $state = null)
    {
        $search = $request->search['value'];
        $filterStatus = $request->filterStatus;
        $filterName = $request->filterName;
        $filterEmailVerification = $request->filterEmailVerification;
        $filterSMSVerification = $request->filterSMSVerification;
        $filterTwoFaSecurity = $request->filterTwoFaVerification;

        $users = User::query()
            ->where('type', 'user')
            ->filterByState($state)
            ->orderBy('id', 'DESC')
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where('email', 'LIKE', "%{$search}%")
                    ->orWhere('username', 'LIKE', "%{$search}%")
                    ->orWhere('firstname', 'LIKE', "%{$search}%")
                    ->orWhere('lastname', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            })
            ->when(isset($filterName) && !empty($filterName), function ($query) use ($filterName) {
                return $query->where('username', 'LIKE', "%{$filterName}%")
                    ->orWhere('firstname', 'LIKE', "%{$filterName}%")
                    ->orWhere('lastname', 'LIKE', "%{$filterName}%");
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus == 'all') {
                    return $query->where('status', '!=', null);
                }
                return $query->where('status', $filterStatus);
            })
            ->when(isset($filterEmailVerification) && !empty($filterEmailVerification), function ($query) use ($filterEmailVerification) {
                return $query->where('email_verification', $filterEmailVerification);
            })
            ->when(isset($filterSMSVerification) && !empty($filterSMSVerification), function ($query) use ($filterSMSVerification) {
                return $query->where('sms_verification', $filterSMSVerification);
            })
            ->when(isset($filterTwoFaSecurity) && !empty($filterTwoFaSecurity), function ($query) use ($filterTwoFaSecurity) {
                return $query->where('two_fa_verify', $filterTwoFaSecurity);
            });

        return DataTables::of($users)
            ->addColumn('checkbox', function ($item) {
                return '<input type="checkbox" id="chk-' . $item->id . '"
                           class="form-check-input row-tic tic-check" name="check" value="' . $item->id . '"
                           data-id="' . $item->id . '">';
            })
            ->addColumn('name', function ($item) {
                $url = route('admin.user.view.profile', $item->id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0">
                              ' . $item->profilePicture() . '
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . $item->name . '</h5>
                              <span class="fs-6 text-body">@' . $item->username . '</span>
                            </div>
                        </a>';

            })
            ->addColumn('email-phone', function ($item) {
                return '<span class="d-block h5 mb-0">' . $item->email . '</span>
                            <span class="d-block fs-5">' . $item->phone . '</span>';
            })
            ->addColumn('country', function ($item) {
                return $item->country ?? 'N/A';
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Active') . '
                  </span>';

                } else {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Inactive') . '
                  </span>';
                }
            })
            ->addColumn('last login', function ($item) {
                return diffForHumans($item->last_login);
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('admin.user.edit', $item->id);
                $viewProfile = route('admin.user.view.profile', $item->id);
                return '<div class="btn-group" role="group">
                      <a href="' . $editUrl . '" class="btn btn-white btn-sm edit_user_btn">
                        <i class="bi-pencil-square me-1"></i> ' . trans("Edit") . '
                      </a>
                    <div class="btn-group">
                      <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                      <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                       <a class="dropdown-item" href="' . $viewProfile . '">
                          <i class="bi-eye dropdown-item-icon"></i> ' . trans("View Profile") . '
                        </a>
                          <a class="dropdown-item" href="' . route('admin.send.email', $item->id) . '"> <i
                                class="bi-envelope dropdown-item-icon"></i> ' . trans("Send Mail") . ' </a>
                          <a class="dropdown-item loginAccount" href="javascript:void(0)"
                           data-route="' . route('admin.login.as.user', $item->id) . '"
                           data-bs-toggle="modal" data-bs-target="#loginAsUserModal">
                            <i class="bi bi-box-arrow-in-right dropdown-item-icon"></i>
                           ' . trans("Login As User") . '
                        </a>
                         <a class="dropdown-item addBalance" href="javascript:void(0)"
                           data-route="' . route('admin.user.update.balance', $item->id) . '"
                           data-id="' . $item->id . '"
                           data-bs-toggle="modal" data-bs-target="#addBalanceModal">
                            <i class="bi bi-cash-coin dropdown-item-icon"></i>
                            ' . trans("Manage Balance") . '
                        </a>
                      </div>
                    </div>
                  </div>';
            })->rawColumns(['action', 'checkbox', 'name', 'email-phone', 'status'])
            ->make(true);
    }

    public function userEdit($id)
    {
        $data['languages'] = Language::all();
        $data['basicControl'] = basicControl();
        $data['allCountry'] = config('country');
        $data['userLoginInfo'] = UserLogin::where('user_id', $id)->orderBy('id', 'desc')->limit(5)->get();

        $data['user'] = User::findOrFail($id);
        return view('admin.user_management.edit_user', $data);
    }

    public function userUpdate(Request $request, $id)
    {
        $languages = Language::all()->map(function ($item) {
            return $item->id;
        });
        $request->validate([
            'firstName' => 'required|string|min:2|max:100',
            'lastName' => 'required|string|min:2|max:100',
            'phone' => 'required|unique:users,phone,' . $id,
            'country' => 'required|string|min:2|max:100',
            'city' => 'required|string|min:2|max:100',
            'state' => 'required|string|min:2|max:100',
            'addressOne' => 'required|string|min:2|max:100',
            'addressTwo' => 'required|string|min:2',
            'zipCode' => 'required|string|min:2|max:100',
            'status' => 'nullable|integer|in:0,1',
            'image' => 'nullable|mimes:jpeg,png,jpg,gif',
            'language_id' => Rule::in($languages),
        ]);

        $user = User::where('id', $id)->firstOr(function () {
            throw new \Exception('User not found!');
        });

        if ($request->hasFile('image')) {
            try {
                $image = $this->fileUpload($request->image, config('filelocation.profileImage.path'), null, null, 'webp', 70, $user->image, $user->image_driver);
                if ($image) {
                    $profileImage = $image['path'];
                    $driver = $image['driver'];
                }
            } catch (\Exception $exp) {
                return back()->with('error', 'Image could not be uploaded.');
            }
        }

        try {
            $user->update([
                'firstname' => $request->firstName,
                'lastname' => $request->lastName,
                'phone' => $request->phone,
                'language_id' => $request->language_id,
                'address_one' => $request->addressOne,
                'address_two' => $request->addressTwo,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zipCode,
                'country' => $request->country,
                'image' => $profileImage ?? $user->image,
                'image_driver' => $driver ?? $user->image_driver,
                'status' => $request->status
            ]);

            return back()->with('success', 'Basic Information Updated Successfully.');
        } catch (\Exception $exp) {
            return back()->with('error', $exp->getMessage());
        }
    }


    public function passwordUpdate(Request $request, $id)
    {
        $request->validate([
            'newPassword' => 'required|min:5|same:confirmNewPassword',
        ]);

        try {
            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('User not found!');
            });

            $user->update([
                'password' => bcrypt($request->newPassword)
            ]);

            return back()->with('success', 'Password Updated Successfully.');

        } catch (\Exception $exp) {
            return back()->with('error', $exp->getMessage());
        }
    }

    public function EmailUpdate(Request $request, $id)
    {
        $request->validate([
            'new_email' => 'required|email:rfc,dns|unique:users,email,' . $id
        ]);

        try {
            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('User not found!');
            });

            $user->update([
                'email' => $request->new_email,
            ]);

            return back()->with('success', 'Email Updated Successfully.');

        } catch (\Exception $exp) {
            return back()->with('error', $exp->getMessage());
        }

    }

    public function usernameUpdate(Request $request, $id)
    {
        $request->validate([
            'username' => 'required|unique:users,username,' . $id
        ]);

        try {
            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('User not found!');
            });

            $user->update([
                'username' => $request->username,
            ]);

            return back()->with('success', 'Username Updated Successfully.');

        } catch (\Exception $exp) {
            return back()->with('error', $exp->getMessage());
        }

    }

    public function updateBalanceUpdate(Request $request, $id)
    {

        $request->validate([
            'amount' => 'required|numeric|min:1',
            'currency_id' => 'required|numeric|min:1'
        ]);

        try {

            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('User not found!');
            });

            $currency = Currency::where('id', $request->currency_id)->firstOr(function () {
                throw new \Exception('Currency not found!');
            });

            $basic = basicControl();

            if ($request->balance_operation == 1) {

                updateWallet($user->id, $request->currency_id, $request->amount, 1);
                $remark = 'Balance credited by admin';
                BasicService::makeTransaction($user, $request->currency_id, $request->amount, 0,
                    '+', Str::random(12), $remark);


                $msg = [
                    'amount' => $request->amount,
                    'currency' => $currency->code,
                ];

                $action = [
                    "name" => $basic->site_title,
                    "image" => getFile($basic->favicon_driver, $basic->favicon),
                    "link" => route('user.transaction'),
                    "icon" => "fa-light fa-bell-on text-white"
                ];
                $firebaseAction = '#';
                $this->userFirebasePushNotification($user, 'ADD_BALANCE', $msg, $firebaseAction);
                $this->userPushNotification($user, 'ADD_BALANCE', $msg, $action);
                $this->sendMailSms($user, 'ADD_BALANCE', $msg);

                return redirect()->route('admin.user.transaction', $user->id)->with('success', 'Balance Updated Successfully.');

            } else {

                updateWallet($user->id, $request->currency_id, $request->amount, 0);
                $remark = 'Balance debited by admin';
                BasicService::makeTransaction($user, $request->currency_id, $request->amount, 0,
                    '-', Str::random(12), $remark);

                $msg = [
                    'amount' => $request->amount,
                    'currency' => $currency->code,
                ];

                $action = [
                    "name" => $basic->site_title,
                    "image" => getFile($basic->logo_driver, $basic->logo),
                    "link" => route('user.transaction'),
                    "icon" => "fa-light fa-bell-on text-white"
                ];
                $firebaseAction = route('user.transaction');
                $this->userFirebasePushNotification($user, 'DEDUCTED_BALANCE', $msg, $firebaseAction);
                $this->userPushNotification($user, 'DEDUCTED_BALANCE', $msg, $action);
                $this->sendMailSms($user, 'DEDUCTED_BALANCE', $msg);

                return redirect()->route('admin.user.transaction', $user->id)->with('success', 'Balance Updated Successfully.');

            }

        } catch (\Exception $exp) {
            return back()->with('error', $exp->getMessage());
        }

    }


    public function preferencesUpdate(Request $request, $id)
    {
        $languages = Language::all()->map(function ($item) {
            return $item->id;
        });

        $request->validate([
            'language_id' => Rule::in($languages),
            'time_zone' => 'required|string|min:1|max:100',
            'email_verification' => 'nullable|integer|in:0,1',
            'sms_verification' => 'nullable|integer|in:0,1',
        ]);

        try {
            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('User not found!');
            });

            $user->update([
                'language_id' => $request->language_id,
                'time_zone' => $request->time_zone,
                'email_verification' => $request->email_verification,
                'sms_verification' => $request->sms_verification,
            ]);

            return back()->with('success', 'Preferences Updated Successfully.');

        } catch (\Exception $exp) {
            return back()->with('error', $exp->getMessage());
        }


    }

    public function userTwoFaUpdate(Request $request, $id)
    {
        try {
            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('User not found!');
            });
            $user->update([
                'two_fa_verify' => ($request->two_fa_security == 1) ? 0 : 1
            ]);

            return back()->with('success', 'Two Fa Security Updated Successfully.');

        } catch (\Exception $exp) {
            return back()->with('error', $exp->getMessage());
        }
    }

    public function userDelete(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('User not found!');
            });
            UserAllRecordDeleteJob::dispatch($user->id);
            $user->forceDelete();

            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            DB::commit();
            return redirect()->route('admin.users')->with('success', 'User Account Deleted Successfully.');
        } catch (\Exception $exp) {
            DB::rollBack();
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('error', $exp->getMessage());
        }
    }

    public function deleteMultiple(Request $request)
    {
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select any user.');
            return response()->json(['error' => 1]);
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            User::whereIn('id', $request->strIds)->get()->map(function ($user) {
                UserAllRecordDeleteJob::dispatch($user->id);
                $user->forceDelete();
            });
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            session()->flash('success', 'User has been deleted successfully');
            return response()->json(['success' => 1]);
        }
    }

    public function userAdd($type = 'user')
    {
        $data['type'] = validateUserType($type);
        $data['allCountry'] = config('country');
        return view('admin.user_management.add_user', $data);
    }

    public function userStore(Request $request)
    {
        try {
            $phoneCode = $request->phone_code;
            $validated = $request->validate([
                'firstname' => 'required|string|max:255',
                'lastname' => 'required|string|max:255',
                'username' => 'required|string|unique:users,username|min:2|max:255',
                'email' => 'required|email|unique:users,email|min:2|max:255',
                'phone_code' => 'required | max:15',
                'phone' => ['required', 'string', 'unique:users,phone', new PhoneLength($phoneCode)],
                'country_code' => 'required | string | max:80',
                'country' => 'required | string | max:80',
                'city' => 'required|string|min:2|max:255',
                'state' => 'nullable|string|min:2|max:255',
                'zip_code' => 'nullable|string|min:2|max:20',
                'address_one' => 'required|string|min:2',
                'address_two' => 'nullable|string|min:2',
                'status' => 'nullable|integer|in:0,1',
                'image' => 'nullable|mimes:jpeg,png,jpg,gif',
                'type' => ['required', Rule::in(getAllowedUserTypes())],
            ]);

            if ($request->hasFile('image')) {
                try {
                    $image = $this->fileUpload($request->image, config('filelocation.profileImage.path'),
                        null, null, 'webp', 80);
                    $validated['image'] = $image['path'];
                    $validated['image_driver'] = $image['driver'];
                } catch (\Exception $e) {
                    return back()->with('error', 'Image could not be uploaded.');
                }
            }

            $response = User::create($validated);
            if (!$response) {
                throw new Exception('Something went wrong, Please try again.');
            }

            $type = ucfirst($request->type);
            $message = $type. "created successfully!";

            return redirect()->route('admin.user.create.success.message', $response->id)->with('success', $message);

        } catch (\Exception $exp) {
            return back()->withInput()->with('error', $exp->getMessage());
        }

    }

    public function userCreateSuccessMessage($id)
    {
        $data['user'] = User::findOrFail($id);
        return view('admin.user_management.components.user_add_success_message', $data);
    }

    public function userViewProfile($id)
    {
        $data['user'] = User::findOrFail($id);
        $data['basic'] = basicControl();
        $data['transactions'] = Transaction::with('user')->where('user_id', $id)
            ->orderBy('id', 'DESC')
            ->limit(5)->get();

        $data['paymentLog'] = Deposit::with('user', 'gateway')->where('user_id', $id)
            ->where('status', '!=', 0)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get();

        $data['withDraws'] = Payout::with('user', 'method')->where('user_id', $id)
            ->where('status', '!=', 0)
            ->orderBy('id', 'DESC')
            ->limit(5)->get();

        $data['currencies'] = Currency::select(['id', 'code', 'name'])->where('is_active', 1)->get();

        return view('admin.user_management.user_view_profile', $data);
    }

    public function transaction($id)
    {
        $user = User::findOrFail($id);
        return view('admin.user_management.transactions', compact('user'));
    }

    public function userTransactionSearch(Request $request, $id)
    {

        $basicControl = basicControl();
        $search = $request->search['value'];

        $filterTransactionId = $request->filter_trx_id;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transaction = Transaction::with('user')
            ->where('user_id', $id)
            ->when(!empty($search), function ($query) use ($search) {
                $query->where(function ($innerQuery) use ($search) {
                    $innerQuery->where('trx_id', 'LIKE', "%{$search}%")
                        ->orWhere('remarks', 'LIKE', "%{$search}%");
                });
            })
            ->when(!empty($request->filter_date) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filter_date) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($filterTransactionId), function ($query) use ($filterTransactionId) {
                return $query->where('trx_id', $filterTransactionId);
            })
            ->orderBy('id', 'DESC')
            ->get();


        return DataTables::of($transaction)
            ->addColumn('no', function () {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('trx', function ($item) {
                return $item->trx_id;
            })
            ->addColumn('amount', function ($item) {
                $statusClass = $item->trx_type == '+' ? 'text-success' : 'text-danger';
                return "<h6 class='mb-0 $statusClass '>" . $item->trx_type . ' ' . currencyPosition($item->amount, $item->currency_id) . "</h6>";

            })
            ->addColumn('charge', function ($item) {
                return currencyPosition($item->charge);

            })
            ->addColumn('remarks', function ($item) {
                return $item->remarks;
            })
            ->addColumn('date-time', function ($item) {
                return dateTime($item->created_at, 'd M Y h:i A');
            })
            ->rawColumns(['amount', 'charge'])
            ->make(true);
    }


    public function payment($id)
    {
        $data['user'] = User::findOrFail($id);
        $data['methods'] = Gateway::where('status', 1)->orderBy('sort_by', 'asc')->get();
        return view('admin.user_management.payment_log', $data);
    }

    public function userPaymentSearch(Request $request, $id)
    {
        $filterTransactionId = $request->filter_trx_id;
        $filterMethod = $request->filter_gateway;
        $filterStatus = $request->filter_status;
        $basicControl = basicControl();
        $search = $request->search['value'];

        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $funds = Deposit::with('user', 'gateway')
            ->where('user_id', $id)
            ->where('status', '!=', 0)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('trx_id', 'LIKE', "%$search%")
                        ->orWhereHas('gateway', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%");
                        });
                });
            })
            ->when(!empty($filterTransactionId), function ($query) use ($filterTransactionId) {
                return $query->where('trx_id', $filterTransactionId);
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus == "all") {
                    return $query->where('status', '!=', null);
                }
                return $query->where('status', $filterStatus);
            })
            ->when(isset($filterMethod), function ($query) use ($filterMethod) {
                return $query->whereHas('gateway', function ($subQuery) use ($filterMethod) {
                    if ($filterMethod == "all") {
                        $subQuery->where('id', '!=', null);
                    } else {
                        $subQuery->where('id', $filterMethod);
                    }
                });
            })
            ->when(!empty($request->filter_date) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filter_date) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();


        return DataTables::of($funds)
            ->addColumn('trx', function ($item) {
                return $item->trx_id;
            })
            ->addColumn('method', function ($item) {
                return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                                <div class="flex-shrink-0">
                                  ' . $item->picture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->gateway)->name . '</h5>
                                </div>
                              </a>';
            })
            ->addColumn('amount', function ($item) {
                $statusClass = $item->getStatusClass();
                return "<h6 class='mb-0 $statusClass '>" . fractionNumber(getAmount($item->amount)) . ' ' . $item->payment_method_currency . "</h6>";
            })
            ->addColumn('charge', function ($item) {
                return "<span class='text-danger'>" . fractionNumber(getAmount($item->percentage_charge) + getAmount($item->fixed_charge)) . ' ' . $item->payment_method_currency . "</span>";
            })
            ->addColumn('payable', function ($item) {
                return "<h6>" . currencyPosition($item->payable_amount_in_base_currency) . "</h6>";
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 0) {
                    return '<span class="badge bg-soft-warning text-warning">' . trans('Pending') . '</span>';
                } else if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">' . trans('Successful') . '</span>';
                } else if ($item->status == 2) {
                    return '<span class="badge bg-soft-warning text-warning">' . trans('Pending') . '</span>';
                } else if ($item->status == 3) {
                    return '<span class="badge bg-soft-danger text-danger">' . trans('Cancel') . '</span>';
                }
            })
            ->addColumn('date', function ($item) {
                return dateTime($item->created_at, 'd M Y h:i A');
            })
            ->addColumn('action', function ($item) use ($basicControl) {
                $details = null;
                if ($item->information) {
                    $details = [];
                    foreach ($item->information as $k => $v) {
                        if ($v->type == "file") {
                            $details[kebab2Title($k)] = [
                                'type' => $v->type,
                                'field_name' => $v->field_name,
                                'field_value' => getFile(config('filesystems.default'), $v->field_value),
                            ];
                        } else {
                            $details[kebab2Title($k)] = [
                                'type' => $v->type,
                                'field_name' => $v->field_name,
                                'field_value' => @$v->field_value ?? $v->field_name
                            ];
                        }
                    }
                }
                if (optional($item->gateway)->id > 999) {
                    $icon = $item->status == 2 ? 'pencil' : 'eye';
                    return "<button type='button' class='btn btn-white btn-sm edit_btn'
                data-detailsinfo='" . json_encode($details) . "'
                data-id='$item->id'
                data-feedback='$item->note'
                data-amount='" . currencyPosition(getAmount($item->amount)) . "'
                data-method='" . optional($item->gateway)->name . "'
                data-gatewayimage='" . getFile(optional($item->gateway)->driver, optional($item->gateway)->image) . "'
                data-datepaid='" . dateTime($item->created_at) . "'
                data-status='$item->status'
                data-username='" . optional($item->user)->username . "'
                data-action='" . route('admin.payment.action', $item->id) . "'
                data-bs-toggle='modal'
                data-bs-target='#accountInvoiceReceiptModal'>  <i class='bi-$icon fill me-1'></i> </button>";
                } else {
                    return '-';
                }
            })
            ->rawColumns(['method', 'amount', 'charge', 'payable', 'status', 'action'])
            ->make(true);
    }

    public function payout($id)
    {
        $data['user'] = User::findOrFail($id);
        $data['methods'] = PayoutMethod::where('is_active', 1)->orderBy('id', 'asc')->get();
        return view('admin.user_management.payout_log', $data);
    }

    public function userPayoutSearch(Request $request, $id)
    {
        $basicControl = basicControl();
        $search = $request->search['value'];
        $filterTransactionId = $request->filter_trx_id;
        $filterMethod = $request->filter_method;
        $filterStatus = $request->filter_status;

        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $payout = Payout::with('user', 'method')->where('user_id', $id)
            ->where('status', '!=', 0)
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('trx_id', 'LIKE', "%$search%")
                        ->orWhereHas('method', function ($q) use ($search) {
                            $q->where('name', 'LIKE', "%$search%");
                        });
                });
            })
            ->when(!empty($filterTransactionId), function ($query) use ($filterTransactionId) {
                return $query->where('trx_id', $filterTransactionId);
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus == "all") {
                    return $query->where('status', '!=', null);
                }
                return $query->where('status', $filterStatus);
            })
            ->when(isset($filterMethod), function ($query) use ($filterMethod) {
                return $query->whereHas('method', function ($subQuery) use ($filterMethod) {
                    if ($filterMethod == "all") {
                        $subQuery->where('id', '!=', null);
                    } else {
                        $subQuery->where('id', $filterMethod);
                    }
                });
            })
            ->when(!empty($request->filter_date) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filter_date) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();


        return DataTables::of($payout)
            ->addColumn('trx', function ($item) {
                return $item->trx_id;
            })
            ->addColumn('method', function ($item) {
                return '<a class="d-flex align-items-center me-2 cursor-unset" href="javascript:void(0)">
                                <div class="flex-shrink-0">
                                  ' . $item->picture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->method)->name . '</h5>
                                </div>
                              </a>';
            })
            ->addColumn('amount', function ($item) {
                $statusClass = $item->getStatusClass();
                return "<h6 class='mb-0 $statusClass '>" . fractionNumber(getAmount($item->amount)) . ' ' . $item->payout_currency_code . "</h6>";

            })
            ->addColumn('charge', function ($item) {
                return "<span class='text-danger'>" . getAmount($item->charge) . ' ' . $item->payout_currency_code . "</span>";
            })
            ->addColumn('net amount', function ($item) {
                return "<h6>" . currencyPosition(getAmount($item->amount_in_base_currency)) . "</h6>";
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-warning text-warning">' . trans('Pending') . '</span>';
                } else if ($item->status == 2) {
                    return '<span class="badge bg-soft-success text-success">' . trans('Successful') . '</span>';
                } else if ($item->status == 3) {
                    return '<span class="badge bg-soft-danger text-danger">' . trans('Cancel') . '</span>';
                }
            })
            ->addColumn('date', function ($item) {
                return dateTime($item->created_at, 'd M Y h:i A');
            })
            ->addColumn('action', function ($item) use ($basicControl) {

                $details = null;
                if ($item->information) {
                    $details = [];
                    foreach ($item->information as $k => $v) {
                        if ($v->type == "file") {
                            $details[kebab2Title($k)] = [
                                'type' => $v->type,
                                'field_name' => $v->field_name,
                                'field_value' => getFile(config('filesystems.default'), @$v->field_value ?? $v->field_name),
                            ];
                        } else {
                            $details[kebab2Title($k)] = [
                                'type' => $v->type,
                                'field_name' => $v->field_name,
                                'field_value' => @$v->field_value ?? $v->field_name
                            ];
                        }
                    }
                }

                $icon = $item->status == 1 ? 'pencil' : 'eye';

                $statusColor = '';
                $statusText = '';
                if ($item->status == 0) {
                    $statusColor = 'badge bg-soft-warning text-warning';
                    $statusText = 'Pending';
                } else if ($item->status == 1) {
                    $statusColor = 'badge bg-soft-warning text-warning';
                    $statusText = 'Pending';
                } else if ($item->status == 2) {
                    $statusColor = 'badge bg-soft-success text-success';
                    $statusText = 'Success';
                } else if ($item->status == 3) {
                    $statusColor = 'badge bg-soft-danger text-danger';
                    $statusText = 'Cancel';
                }

                return "<button type='button' class='btn btn-white btn-sm edit_btn'
                data-id='$item->id'
                data-info='" . json_encode($details) . "'
                data-sendername='" . $item->user->firstname . ' ' . $item->user->lastname . "'
                data-transactionid='$item->trx_id'
                data-feedback='$item->feedback'
                data-amount=' " . currencyPosition(getAmount($item->amount)) . "'
                data-method='" . optional($item->method)->name . "'
                data-gatewayimage='" . getFile(optional($item->method)->driver, optional($item->method)->logo) . "'
                data-datepaid='" . dateTime($item->created_at, 'd M Y') . "'
                data-status='$item->status'
                data-status_color='$statusColor'
                data-status_text='$statusText'
                data-username='" . optional($item->user)->username . "'
                data-action='" . route('admin.payout.action', $item->id) . "'
                data-bs-toggle='modal'
                data-bs-target='#accountInvoiceReceiptModal'>  <i class='bi-$icon fill me-1'></i> </button>";
            })
            ->rawColumns(['method', 'amount', 'charge', 'net amount', 'status', 'action'])
            ->make(true);
    }

    public function userKyc($id)
    {
        try {
            $data['user'] = User::where('id', $id)->firstOr(function () {
                throw new Exception('No User found.');
            });
            return view('admin.user_management.user_kyc', $data);
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function KycSearch(Request $request, $id)
    {
        $filterVerificationType = $request->filter_verification_type;
        $filterStatus = $request->filter_status;

        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transaction = UserKyc::with('user')
            ->where('user_id', $id)
            ->orderBy('id', 'DESC')
            ->when(!empty($filterVerificationType), function ($query) use ($filterVerificationType) {
                return $query->where('kyc_type', $filterVerificationType);
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus == "all") {
                    return $query->where('status', '!=', null);
                }
                return $query->where('status', $filterStatus);
            })
            ->when(!empty($request->filter_date) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filter_date) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->get();

        return DataTables::of($transaction)
            ->addColumn('no', function () {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('verification_type', function ($item) {
                return $item->kyc_type;

            })
            ->addColumn('status', function ($item) {
                if ($item->status == 0) {
                    return '<span class="badge bg-soft-warning text-warning">' . trans('Pending') . '</span>';
                } else if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">' . trans('Verified') . '</span>';
                } else if ($item->status == 2) {
                    return '<span class="badge bg-soft-danger text-danger">' . trans('Rejected') . '</span>';
                }
            })
            ->addColumn('date', function ($item) {
                return dateTime($item->created_at);

            })
            ->addColumn('action', function ($item) {
                $url = route('admin.kyc.view', $item->id);
                return '<a href="' . $url . '" class="btn btn-white btn-sm">
                    <i class="bi-eye me-1"></i>
                  </a>';
            })
            ->rawColumns(['name', 'status', 'action'])
            ->make(true);
    }


    public function loginAsUser($id)
    {
        Auth::guard('web')->loginUsingId($id);
        return redirect()->route('user.dashboard');
    }


    public function blockProfile(Request $request, $id)
    {
        try {
            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('No User found.');
            });

            $user->update([
                'status' => 0
            ]);

            return back()->with('success', 'Block Profile Successfully');
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function mailAllUser()
    {
        return view('admin.user_management.mail_all_user');
    }

    public function sendEmail($id)
    {
        try {
            $user = User::where('id', $id)->firstOr(function () {
                throw new \Exception('No User found.');
            });
            return view('admin.user_management.send_mail_form', compact('user'));
        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function sendMailUser(Request $request, $id = null)
    {

        $request->validate([
            'subject' => 'required|min:5',
            'description' => 'required|min:10',
        ]);

        try {

            $user = User::where('id', $id)->first();

            $subject = $request->subject;
            $template = $request->description;

            if (isset($user)) {
                Mail::to($user)->send(new SendMail(basicControl()->sender_email, $subject, $template));
            } else {
                $users = User::all();
                foreach ($users as $user) {
                    Mail::to($user)->queue(new SendMail(basicControl()->sender_email, $subject, $template));
                }
            }

            return back()->with('success', 'Email Sent Successfully');

        } catch (\Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }

    public function getBalance($id)
    {
        $wallets = Wallet::where('user_id', $id)
            ->with('currency')
            ->get()
            ->map(function ($wallet) {
                if (optional($wallet->currency)->currency_type) {
                    return [
                        'currency' => optional($wallet->currency)->name,
                        'value' => getAmount($wallet->balance, 2) . ' ' . optional($wallet->currency)->code,
                    ];
                } else {
                    return [
                        'currency' => optional($wallet->currency)->name,
                        'value' => $wallet->balance . ' ' . optional($wallet->currency)->code,
                    ];
                }
            });

        return response()->json([
            'status' => true,
            'balances' => $wallets
        ]);

    }


}
