<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ChargesLimit;
use App\Models\Currency;
use App\Models\Gateway;
use App\Models\TwoFactorSetting;
use App\Models\User;
use App\Models\Voucher;
use App\Models\Wallet;
use App\Traits\ChargeLimitTrait;
use App\Traits\Notify;
use App\Traits\PaymentTrait;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class VoucherController extends Controller
{
    use Notify,PaymentTrait, ChargeLimitTrait;

    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware(function ($request, $next) {
            $this->user = auth()->user();
            return $next($request);
        });
        $this->theme = template();
    }

    public function index()
    {
        $userId = Auth::id();
        $data['currencies'] = Currency::select('id', 'code', 'name')->orderBy('code', 'ASC')->get();
        $data['vouchers'] = collect(Voucher::selectRaw('COUNT(id) AS totalVoucher')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS generatedVoucher')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS generatedVoucherPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS paymentDoneVoucher')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(id)) * 100 AS paymentDoneVoucherPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 5 THEN id END) AS cancelVoucher')
            ->selectRaw('(COUNT(CASE WHEN status = 5 THEN id END) / COUNT(id)) * 100 AS cancelVoucherPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingVoucher')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS pendingVoucherPercentage')
            ->where(function ($query) use ($userId) {
                $query->where('sender_id', $userId);
                $query->orWhere('receiver_id', $userId);
            })
            ->get()
            ->toArray())->collapse();
        return view('user.voucher.index', $data);
    }

    public function search(Request $request)
    {
        $userId = Auth::id();
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_trx_id;
        $filterCurrency = $request->filter_currency;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = Voucher::with(['sender', 'receiver', 'currency'])
            ->visibleToUser($userId)
            ->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('utr', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(isset($filterCurrency), function ($query) use ($filterCurrency) {
                if ($filterCurrency != "all") {
                    return $query->where('currency_id', $filterCurrency);
                }
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
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('utr', 'LIKE', "%{$search}%")
                        ->orWhere('amount', 'LIKE', "%{$search}%")
                        ->orWhereHas('sender', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('receiver', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($transfers)
            ->addColumn('type', function ($item) {
                return renderAmountTypeIcon($item->sender_id);
            })
            ->addColumn('transaction_id', function ($item) {
                return $item->utr;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->amount,$item->currency_id);
                return '<span class="amount-highlight">' . $amount . ' </span>';
            })
            ->addColumn('participant', function ($item) {
                $isSender = $item->sender_id == Auth::id();
                $user = $isSender ? $item->receiver : $item->sender;
                $role = $isSender ? trans('Receiver') : trans('Sender');

                if ($user) {
                    return
                        '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                        <div class="flex-shrink-0">' . $user->profilePicture() . '</div>
                        <div class="flex-grow-1 ms-2">
                            <h5 class="text-hover-primary mb-0">' . e($user->name) . '
                                <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="' . e($role) . '"></i>
                            </h5>
                            <span class="fs-6 text-body">@' . e($user->username) . '</span>
                        </div>
                    </a>';
                } else {
                    return '<span class="badge bg-soft-dark text-body "> '. $item->email .'</span>';
                }
            })
            ->addColumn('status', function ($item) {
                return $item->getStatus();
            })
            ->addColumn('created_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('action', function ($item) {
                $userId = auth()->id();
                if ($item->status == 0 && $item->sender_id == $userId) {
                    $generateRoute = route('user.voucher.confirmInit', $item->utr);
                    $generateText = trans('Generate');
                    return <<<HTML
                        <div class="btn-group" role="group">
                            <a href="{$generateRoute}" class="btn btn-soft-primary btn-xs" target="_blank">
                                <i class="bi bi-cpu me-1"></i> {$generateText}
                            </a>
                        </div>
                    HTML;
                }
                $viewRoute = route('user.voucher.paymentView', $item->utr);
                $label = $item->receiver_id == $userId ? trans('Review') : trans('View');
                return <<<HTML
                    <div class="btn-group" role="group">
                        <a href="{$viewRoute}" class="btn btn-soft-info btn-xs">
                            <i class="bi bi-eye me-1"></i> {$label}
                        </a>
                    </div>
                HTML;
            })
            ->rawColumns(['transaction_id', 'amount', 'type', 'participant', 'status', 'created_at','action'])
            ->make(true);
    }

    public function voucherPaymentPublicView(Request $request, $utr)
    {
        $voucher = Voucher::where('utr', $utr)->first();

        abort_if(!$voucher, '404');

        if ($request->isMethod('get')) {
            return view('user.voucher.publicPayment', compact('voucher'));
        } elseif ($request->isMethod('post')) {

            $reqStatus = $request->status;

            if ($voucher->status == 1 && ($reqStatus == 2 || $reqStatus == 5)) {
                if ($reqStatus == 5) {
                    $voucher->status = $reqStatus;
                    $voucher->save();
                    return redirect(route('home'))->with('success', 'Transaction canceled');
                } else {
                    return redirect(route('voucher.public.payment', $utr));
                }
            }
        }
    }

    public function create(Request $request)
    {
        if ($request->isMethod('get')) {
            $data['currencies'] = Currency::select('id', 'code', 'name', 'currency_type')->where('is_active', 1)->get();
            return view('user.voucher.create', $data);
        } elseif ($request->isMethod('post')) {
            $allowUser = basicControl()->allowUser;
            $purifiedData = $request->all();
            $validationRules = [
                'recipient' => 'required|min:4',
                'amount' => 'required|numeric|min:1|not_in:0',
                'currency' => 'required|integer|min:1|not_in:0',
            ];
            if (!$allowUser) {
                $validationRules['recipient'] = 'required|email';
            }

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }
            $purifiedData = (object)$purifiedData;

            $amount = $purifiedData->amount;
            $currency_id = $purifiedData->currency;
            $recipient = $purifiedData->recipient;
            $charge_from = 1;

            $checkAmountValidate = $this->checkAmountValidate($amount, $currency_id, config('transactionType.voucher'), $charge_from);//6 = voucher
            if (!$checkAmountValidate['status']) {
                return back()
                    ->withInput()
                    ->withErrors('amount', $checkAmountValidate['message'])
                    ->with('error', $checkAmountValidate['message']);
            }

            $checkRecipientValidate = $this->checkRecipientValidate($recipient);
            if (!$checkRecipientValidate['status']) {
                return back()
                    ->withInput()
                    ->withErrors('recipient', $checkRecipientValidate['message'])
                    ->with('error', $checkRecipientValidate['message']);
            }

            $receiver = $checkRecipientValidate['receiver'];
            $voucher = new Voucher();
            $voucher->sender_id = Auth::id();
            $voucher->receiver_id = optional($receiver)->id ?? null;
            $voucher->currency_id = $checkAmountValidate['currency_id'];
            $voucher->percentage = $checkAmountValidate['percentage'];
            $voucher->charge_percentage = $checkAmountValidate['percentage_charge']; // amount after calculation percent of charge
            $voucher->charge_fixed = $checkAmountValidate['fixed_charge'];
            $voucher->charge = $checkAmountValidate['charge'];
            $voucher->amount = $checkAmountValidate['amount'];
            $voucher->transfer_amount = $checkAmountValidate['transfer_amount'];
            $voucher->received_amount = $checkAmountValidate['received_amount'];
            $voucher->charge_from = $checkAmountValidate['charge_from']; //0 = Sender, 1 = Receiver
            $voucher->note = $purifiedData->note;
            $voucher->email = optional($receiver)->email ?? $recipient;
            $voucher->status = 0;// 0=Pending, 1=generate, 2=payment done, 5=cancel
            $voucher->utr = 'V';
            $voucher->save();
            return redirect(route('user.voucher.confirmInit', $voucher->utr))->with('success', 'Initiated successfully');
        }
    }

    public function confirmInit(Request $request, $utr)
    {
        $voucher = Voucher::where('utr', $utr)->first();
        $user = Auth::user();

        if (!$voucher || $voucher->status != 0) {
            return redirect(route('user.voucher.createRequest'))->with('error', 'Transaction already complete or invalid code');
        }

        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if ($request->isMethod('get')) {
            return view('user.voucher.confirmInit', compact(['utr', 'voucher', 'enable_for']));
        }
        elseif ($request->isMethod('post')) {
            if (in_array('voucher', $enable_for)) {
                $purifiedData = $request->all();
                $validationRules = [
                    'security_pin' => 'required|integer|digits:5',
                ];
                $validate = Validator::make($purifiedData, $validationRules);

                if ($validate->fails()) {
                    return back()->withErrors($validate)->withInput();
                }
                if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                    return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->withInput();
                }
            }
            $checkAmountValidate = $this->checkAmountValidate($voucher->amount, $voucher->currency_id, config('transactionType.voucher'), $voucher->charge_from);//6=voucher

            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }

            $voucher->status = 1;
            $voucher->save();

            if (is_null($voucher->receiver_id)) {
                $url = route('voucher.paymentPublicView', $utr);
                $receivedUser = new User();
                $receivedUser->name = 'Concern';
                $receivedUser->email = $voucher->email;
                $receiver = $voucher->email;
            } else {
                $receivedUser = $voucher->receiver;
                $receiver = optional($voucher->receiver)->name;
                $url = route('user.voucher.paymentView', $utr);
            }

            $params = [
                'sender' => $user->name,
                'amount' => getAmount($voucher->amount),
                'currency' => optional($voucher->currency)->code,
                'transaction' => $voucher->utr,
                'link' => $url,
            ];

            $action = [
                "link" => $url,
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = $url;
            if (is_null($voucher->receiver_id)) {
                $this->mail($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params);
            } else {
                $this->sendMailSms($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params);
                $this->userPushNotification($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params, $action);
                $this->userFirebasePushNotification($receivedUser, 'VOUCHER_PAYMENT_REQUEST_TO', $params, $firebaseAction);
            }

            $params = [
                'receiver' => $receiver,
                'amount' => getAmount($voucher->amount),
                'currency' => optional($voucher->currency)->code,
                'transaction' => $voucher->utr,
            ];
            $action = [
                "link" => route('user.voucher.index'),
                "icon" => "fa fa-money-bill-alt text-white"
            ];
            $firebaseAction = route('user.voucher.index');
            $this->sendMailSms($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params);
            $this->userPushNotification($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params, $action);
            $this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_REQUEST_FROM', $params, $firebaseAction);

            return to_route('user.confirm.success')->with([
                'message' => __("Your Voucher has been initiated successfully"),
                'next_route' => route('user.voucher.index'),
                'next_text' => __('View Voucher List')
            ]);

        }
    }

    public function checkRecipient(Request $request)
    {
        if ($request->ajax()) {
            $data = $this->checkRecipientValidate($request->recipient);
            return response()->json($data);
        }
    }

    public function checkRecipientValidate($recipient)
    {
        $field = filter_var($recipient, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

        if (basicControl()->allowUser) {
            $receiver = User::where($field, $recipient)->byType('user')->first();

            if ($receiver && $receiver->id == Auth::id()) {
                $data['status'] = false;
                $data['message'] = 'Transfer not allowed to self email';
            } elseif ($receiver && $receiver->id != Auth::id()) {
                $data['status'] = true;
                $data['message'] = "User found. Are you looking for $receiver->name ?";
                $data['receiver'] = $receiver;
            } elseif (!$receiver && $field == 'email') {
                $data['status'] = true;
                $data['message'] = '';
                $data['receiver'] = $receiver;
            } else {
                $data['status'] = false;
                $data['message'] = 'No user found';
            }
        } else {
            if ($field == 'email') {
                $receiver = User::where($field, $recipient)->byType('user')->first();

                if ($receiver) {
                    $requestMoneyUrl = route('user.requestMoney.initialize');
                    $data['status'] = false;
                    $data['message'] = "User exist, voucher creation not allowed to existing user. Go to  <a href=$requestMoneyUrl>Request money</a>";
                } else {
                    $data['status'] = true;
                    $data['message'] = '';
                    $data['receiver'] = $receiver;
                }
            } else {
                $data['status'] = false;
                $data['message'] = 'Please Enter a valid email';
            }
        }

        return $data;
    }

    public function checkInitiateAmount(Request $request)
    {
        if ($request->ajax()) {
            $amount = $request->amount;
            $currency_id = $request->currency_id;
            $transaction_type_id = $request->transaction_type_id;
            $charge_from = $request->charge_from;
            $data = $this->checkAmountValidate($amount, $currency_id, $transaction_type_id, $charge_from);
            return response()->json($data);
        }
    }


    public function voucherPublicPayment(Request $request, $utr)
    {
        $voucher = Voucher::doesntHave('successDepositable')->where('utr', $utr)->first();
        if (!$voucher || $voucher->status != 1)
            abort(404);

        if ($request->isMethod('get')) {
            $methods = Gateway::getActiveMethods();
            return view('user.voucher.payment', compact('methods', 'voucher'));
        }
        elseif ($request->isMethod('post')) {
            $purifiedData = Purify::clean($request->all());
            $validationRules = [
                'amount' => 'required|numeric|min:1|not_in:0',
                'currency' => 'required|integer|min:1|not_in:0',
                'methodId' => 'required|integer|min:1|not_in:0',
            ];

            $validate = Validator::make($purifiedData, $validationRules);
            if ($validate->fails()) {
                return back()->withErrors($validate)->withInput();
            }

            $purifiedData = (object)$purifiedData;
            $amount = $purifiedData->amount;
            $currency_id = $purifiedData->currency;
            $methodId = $purifiedData->methodId;

            $checkAmountValidate = $this->validatePayment($amount, $currency_id, config('transactionType.deposit'), $methodId);//7 = deposit
            if (!$checkAmountValidate['status']) {
                return back()->withInput()->with('error', $checkAmountValidate['message']);
            }
            $deposit  = $this->createDeposit($checkAmountValidate, Voucher::class, $voucher->id);

            return redirect(route('payment.process', $deposit->trx_id));
        }
    }

    public function voucherPaymentView(Request $request, $utr)
    {
        $user = Auth::user();
        //0=Pending, 1=generate, 2 = payment done, 5 = cancel
        $voucher = Voucher::where('utr', $utr)
            ->where(fn($q) => $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id))
            ->first();

        if ($request->isMethod('get')) {
            return view('user.voucher.userPayment', compact('voucher'));
        }
        elseif ($request->isMethod('post')) {

            $reqStatus = $request->status;

            if ($voucher->status == 1 && ($reqStatus == 2 || $reqStatus == 5)) {
                if ($reqStatus == 5) {
                    $voucher->status = $reqStatus;
                    $voucher->save();

                    // send mail sms notification who receiver payment
                    $params = [
                        'receiver' => optional($voucher->receiver)->name,
                        'amount' => getAmount($voucher->amount),
                        'currency' => optional($voucher->currency)->code,
                        'transaction' => $voucher->utr,
                    ];
                    $action = [
                        "link" => route('user.voucher.index'),
                        "icon" => "fa fa-money-bill-alt text-white"
                    ];
                    $firebaseAction = route('user.voucher.index');
                    $sender = $voucher->sender;
                    $this->sendMailSms($sender, 'VOUCHER_PAYMENT_CANCEL_TO', $params);
                    $this->userPushNotification($sender, 'VOUCHER_PAYMENT_CANCEL_TO', $params, $action);
                    $this->userFirebasePushNotification($sender, 'VOUCHER_PAYMENT_CANCEL_TO', $params, $firebaseAction);

                    // send mail sms notification who make payment
                    $params = [
                        'sender' => optional($voucher->sender)->name,
                        'amount' => getAmount($voucher->amount),
                        'currency' => optional($voucher->currency)->code,
                        'transaction' => $voucher->utr,
                    ];
                    $this->sendMailSms($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params);
                    $this->userPushNotification($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params, $action);
                    $this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_CANCEL_FROM', $params, $firebaseAction);

                    return redirect(route('user.voucher.index'))->with('success', 'Transaction canceled');
                } elseif ($reqStatus == 2) {

                    $fromWallet = Wallet::where('is_active', 1)->where('user_id', auth()->id())->where('currency_id', $voucher->currency_id)->firstOrFail();
                    if ($voucher->received_amount > $fromWallet->balance) {
                        return back()->withInput()->with('error', 'Please add fund ' . $voucher->currency->name . ' wallet to payment voucher');
                    }
                    /* Add money to Sender Wallet */

                    $sender_wallet = updateWallet($voucher->sender_id, $voucher->currency_id, $voucher->received_amount, 1);

                    $remark = 'Balance credited from voucher';
                    BasicService::makeTransaction($voucher->sender, $voucher->currency_id, $voucher->received_amount,
                        $voucher->charge_from == 1 ? $voucher->charge : 0,
                        '+', $voucher->utr, $remark, $voucher->id, Voucher::class);

                    $action = [
                        "link" => route('user.voucher.index'),
                        "icon" => "fa fa-money-bill-alt text-white"
                    ];
                    $firebaseAction = route('user.voucher.index');

                    if ($voucher->receiver){
                        /* Deduct money from receiver wallet */
                        $receiver_wallet = updateWallet($voucher->receiver_id, $voucher->currency_id, $voucher->transfer_amount, 0);
                        $remark = 'Balance debited from voucher';
                        BasicService::makeTransaction($voucher->receiver, $voucher->currency_id, $voucher->received_amount,
                            $voucher->charge_from == 1 ? $voucher->charge : 0,
                            '-', $voucher->utr, $remark, $voucher->id, Voucher::class);

                        // send mail sms notification who receive payment
                        $params = [
                            'receiver' => optional($voucher->receiver)->name,
                            'amount' => getAmount($voucher->amount),
                            'currency' => optional($voucher->currency)->code,
                            'transaction' => $voucher->utr,
                        ];

                        $receiver = $voucher->receiver;
                        $this->sendMailSms($receiver, 'VOUCHER_PAYMENT_TO', $params);
                        $this->userPushNotification($receiver, 'VOUCHER_PAYMENT_TO', $params, $action);
                        $this->userFirebasePushNotification($receiver, 'VOUCHER_PAYMENT_TO', $params, $firebaseAction);
                    }

                    $voucher->status = 2;
                    $voucher->save();

                    // send mail sms notification who make payment
                    $params = [
                        'sender' => optional($voucher->sender)->name,
                        'amount' => getAmount($voucher->amount),
                        'currency' => optional($voucher->currency)->code,
                        'transaction' => $voucher->utr,
                    ];
                    $this->sendMailSms($user, 'VOUCHER_PAYMENT_FROM', $params);
                    $this->userPushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $action);
                    $this->userFirebasePushNotification($user, 'VOUCHER_PAYMENT_FROM', $params, $firebaseAction);

                    return back()->with('success', 'Payment Confirmed');
                }
            }
            return redirect(route('user.voucher.index'))->with('success', 'Payment Confirmed');
        }
    }
}
