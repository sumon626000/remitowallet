<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ContentDetails;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\Transfer;
use App\Models\TwoFactorSetting;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Models\Wallet;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\Facades\DataTables;

class VirtualCardController extends Controller
{
    use Notify, Upload;

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
        $basicControl = basicControl();
        $orderLock = 'false';
        $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();

        if ($checkOrder) {
            $orderLock = 'true';
        }
        if ($basicControl->v_card_multiple == 0) {
            $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
            if ($checkOrder) {
                $orderLock = 'true';
            }
        }

        $data['cardOrder'] = VirtualCardOrder::where('user_id', auth()->id())->where('status', '!=', 1)->latest()->first();
        $data['template'] = ContentDetails::whereHas('content', function ($query) {
            $query->where('name', 'virtual_card');
        })->first();
        $data['approveCards'] = VirtualCardOrder::cards()->where('user_id', auth()->id())->latest()->get();
        return view('user.virtual_card.cardForm', $data, compact('orderLock'));
    }

    public function order()
    {
        $basicControl = basicControl();
        $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->whereIn('status', [0, 3, 4])->latest()->exists();
        if ($checkOrder) {
            return back()->with('error', 'You can not eligible for request card');
        }
        if ($basicControl->v_card_multiple == 0) {
            $checkOrder = VirtualCardOrder::where('user_id', auth()->id())->where('status', 1)->latest()->exists();
            if ($checkOrder) {
                return back()->with('error', 'You can not eligible for multiple card');
            }
        }

        $data['virtualCardMethod'] = VirtualCardMethod::where('status', 1)->firstOrFail();
        return view('user.virtual_card.orderCard', $data);
    }

    public function orderSubmit(Request $request)
    {

        $purifiedData = $request->all();
        $validationRules = [
            'currency' => 'required',
        ];

        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }


        if ($this->checkUserBalance() == false) {
            return back()->withInput()->with('error', 'Please add fund your ' . basicControl()->base_currency . ' wallet');
        }

        $virtualCardMethod = VirtualCardMethod::where('status', 1)->firstOrFail();
        $virtualCardOrder = new VirtualCardOrder();

        $purifiedData = (object)$purifiedData;

        $rulesSpecification = [];
        $inputFieldSpecification = [];
        if ($virtualCardMethod->form_field != null) {
            foreach ($virtualCardMethod->form_field as $key => $cus) {
                $rulesSpecification[$key] = [$cus->validation];
                if ($cus->type == 'file') {
                    array_push($rulesSpecification[$key], 'image');
                    array_push($rulesSpecification[$key], 'mimes:jpeg,jpg,png');
                    array_push($rulesSpecification[$key], 'max:2048');
                }
                if ($cus->type == 'text') {
                    array_push($rulesSpecification[$key], 'max:191');
                }
                if ($cus->type == 'textarea') {
                    array_push($rulesSpecification[$key], 'max:300');
                }
                $inputFieldSpecification[] = $key;
            }
        }

        $this->validate($request, $rulesSpecification);

        $collectionSpecification = collect($request);
        $reqFieldSpecification = [];
        if ($virtualCardMethod->form_field != null) {
            foreach ($collectionSpecification as $k => $v) {
                foreach ($virtualCardMethod->form_field as $inKey => $inVal) {
                    if ($k != $inKey) {
                        continue;
                    } else {
                        if ($inVal->type == 'file') {
                            if ($request->hasFile($inKey)) {

                                try {
                                    $image = $request->file($inKey);
                                    $location = config('filelocation.virtualCardOrder.path');
                                    $filename = $this->fileUpload($image, $location);;
                                    $reqField[$inKey] = [
                                        'field_name' => $inKey,
                                        'field_value' => $filename['path'],
                                        'field_level' => $inVal->field_level,
                                        'type' => $inVal->type,
                                        'validation' => $inVal->validation,
                                    ];

                                } catch (\Exception $exp) {
                                    return back()->with('error', 'Image could not be uploaded.')->withInput();
                                }

                            }
                        } else {
                            $reqFieldSpecification[$inKey] = [
                                'field_name' => $inKey,
                                'field_value' => $v,
                                'field_level' => $inVal->field_level,
                                'type' => $inVal->type,
                                'validation' => $inVal->validation,
                            ];
                        }
                    }
                }
            }
            $virtualCardOrder->form_input = $reqFieldSpecification;
        } else {
            $virtualCardOrder->form_input = null;
        }

        $virtualCardOrder->virtual_card_method_id = $virtualCardMethod->id;
        $virtualCardOrder->user_id = auth()->id();
        $virtualCardOrder->currency = $purifiedData->currency;
        $virtualCardOrder->status = 4;
        $virtualCardOrder->save();

        return redirect()->route('user.order.confirm', $virtualCardOrder->id)->with('success', 'Request initiated successfully');
    }

    public function confirmOrder(Request $request, $orderId)
    {
        $user = Auth::user();
        $order = VirtualCardOrder::with('user', 'cardMethod')->where('user_id', auth()->id())->where('id', $orderId)->firstOrFail();
        if (!$order || $order->status != 4) { //Check is transaction found and unpaid
            return redirect(route('user.virtual.card'))->with('success', 'Request already send');
        }

        $twoFactorSetting = TwoFactorSetting::firstOrCreate(['user_id' => $user->id]);
        $enable_for = is_null($twoFactorSetting->enable_for) ? [] : json_decode($twoFactorSetting->enable_for, true);

        if ($request->isMethod('get')) {
            return view('user.virtual_card.confirm', compact(['orderId', 'order', 'enable_for']));
        } elseif ($request->isMethod('post')) {
            // Security PIN check and validation
            if (in_array('virtual_card', $enable_for)) {
                $purifiedData = $request->all();
                $validationRules = [
                    'security_pin' => 'required|integer|digits:5',
                ];
                $validate = Validator::make($purifiedData, $validationRules);

                if ($validate->fails()) {
                    return back()->withErrors($validate)->withInput();
                }
                if (!Hash::check($purifiedData['security_pin'], $twoFactorSetting->security_pin)) {
                    return back()->withErrors(['security_pin' => 'You have entered an incorrect PIN'])->with('error', 'You have entered an incorrect PIN')->withInput();
                }
            }
            if ($this->chargePay($order)) {
                $order->status = 0;
                $order->save();
                return redirect()->route('user.virtual.card')->with('success', 'Your virtual card request is send');
            }
            return back()->with('error', 'Something went wrong');
        }
    }

    public function orderReSubmit(Request $request)
    {
        $data['virtualCardMethod'] = VirtualCardMethod::where('status', 1)->firstOrFail();
        $data['cardOrder'] = VirtualCardOrder::where('user_id', auth()->id())->latest()->firstOrFail();
        if ($request->method() == 'GET') {
            return view('user.virtual_card.reOrderCard', $data);
        } else {
            $purifiedData = $request->all();
            $rules = [
                'currency' => 'required',
            ];
            $message = [
                'currency.required' => 'Currency field is required',
            ];

            $validate = Validator::make($purifiedData, $rules, $message);

            if ($validate->fails()) {
                return back()->withInput()->withErrors($validate);
            }

            if ($this->checkUserBalance() == false) {
                return back()->withInput()->with('error', 'Please add fund your ' . basicControl()->base_currency . ' wallet');
            }

            $rulesSpecification = [];
            $inputFieldSpecification = [];
            if ($data['virtualCardMethod']->form_field != null) {
                foreach ($data['virtualCardMethod']->form_field as $key => $cus) {
                    $rulesSpecification[$key] = [$cus->validation];
                    if ($cus->type == 'file') {
                        array_push($rulesSpecification[$key], 'image');
                        array_push($rulesSpecification[$key], 'mimes:jpeg,jpg,png');
                        array_push($rulesSpecification[$key], 'max:2048');
                    }
                    if ($cus->type == 'text') {
                        array_push($rulesSpecification[$key], 'max:191');
                    }
                    if ($cus->type == 'textarea') {
                        array_push($rulesSpecification[$key], 'max:300');
                    }
                    $inputFieldSpecification[] = $key;
                }
            }
            $this->validate($request, $rulesSpecification);

            $collectionSpecification = collect($request);
            $reqFieldSpecification = [];
            if ($data['virtualCardMethod']->form_field != null) {
                foreach ($collectionSpecification as $k => $v) {
                    foreach ($data['virtualCardMethod']->form_field as $inKey => $inVal) {
                        if ($k != $inKey) {
                            continue;
                        } else {
                            if ($inVal->type == 'file') {
                                if ($request->hasFile($inKey)) {

                                    try {
                                        $image = $request->file($inKey);
                                        $location = config('location.virtualCardOrder.path');
                                        $filename = $this->uploadImage($image, $location);;
                                        $reqField[$inKey] = [
                                            'field_name' => $inKey,
                                            'field_value' => $filename,
                                            'field_level' => $inVal->field_level,
                                            'type' => $inVal->type,
                                            'validation' => $inVal->validation,
                                        ];

                                    } catch (\Exception $exp) {
                                        return back()->with('error', 'Image could not be uploaded.')->withInput();
                                    }

                                }
                            } else {
                                $reqFieldSpecification[$inKey] = [
                                    'field_name' => $inKey,
                                    'field_value' => $v,
                                    'field_level' => $inVal->field_level,
                                    'type' => $inVal->type,
                                    'validation' => $inVal->validation,
                                ];
                            }
                        }
                    }
                }
                $data['cardOrder']->form_input = $reqFieldSpecification;
            } else {
                $data['cardOrder']->form_input = null;
            }

            $data['cardOrder']->currency = $purifiedData['currency'];
            $data['cardOrder']->status = 3;
            $data['cardOrder']->save();

            $this->chargePay($data['cardOrder']);

            return redirect()->route('user.virtual.card')->with('success', 'Re Submitted Successfully');
        }
    }

    public function checkUserBalance()
    {
        $basicControl = basicControl();
        $baseCurrency = $basicControl->base_currency;
        $virtualCardCharge = $basicControl->v_card_charge;

        $currency = Currency::where('is_active', 1)->where('code', $baseCurrency)->firstOrFail();
        $availableBalance = Wallet::select('balance')->where('user_id', auth()->id())->where('currency_id', $currency->id)->first();
        if ($availableBalance && ($availableBalance->balance > $virtualCardCharge)) {
            return true;
        } else {
            return false;
        }
    }

    public function chargePay($cardOrder)
    {
        $basicControl = basicControl();
        $baseCurrency = $basicControl->base_currency;
        $virtualCardCharge = $basicControl->v_card_charge;
        $currency = Currency::where('is_active', 1)->where('code', $baseCurrency)->firstOrFail();
        $availableBalance = Wallet::where('user_id', auth()->id())->where('currency_id', $currency->id)->first();

        DB::beginTransaction();
        try {
            $newBalance = $availableBalance->balance - $virtualCardCharge;
            $availableBalance->balance = $newBalance;
            $availableBalance->save();

            $remark = 'Balance debited from virtual card apply';
            BasicService::makeTransaction(auth()->user(), $currency->id, $virtualCardCharge,
                0, '-', generateRandomAlphaNumeric('V'), $remark, $cardOrder->id, VirtualCardOrder::class);


            $cardOrder->charge = $virtualCardCharge;
            $cardOrder->charge_currency = $currency->id;
            $cardOrder->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }

        $params = [
            'amount' => $virtualCardCharge,
            'currency' => $baseCurrency,
        ];
        $action = [
            "name" => $basicControl->site_title,
            "image" => getFile($basicControl->favicon_driver, $basicControl->favicon),
            "link" => "#",
            "icon" => "fa-light fa-bell-on text-white"
        ];

        $this->sendMailSms(auth()->user(), 'VIRTUAL_CARD_APPLY', $params);
        $this->userPushNotification(auth()->user(), 'VIRTUAL_CARD_APPLY', $params, $action);
        $this->userFirebasePushNotification(auth()->user(), 'VIRTUAL_CARD_APPLY', $params);

        $params = [
            'username' => optional(auth()->user())->username ?? null,
            'amount' => $virtualCardCharge,
            'currency' => $basicControl->base_currency,
        ];

        $action = [
            "name" => auth()->user()->name,
            "image" => getFile(auth()->user()->image_driver, auth()->user()->image),
            "link" => route('admin.virtual.cardOrderDetail', $cardOrder->id),
            "icon" => "fa-light fa-bell-on text-white"
        ];
        $firebaseAction = route('admin.virtual.cardOrderDetail', $cardOrder->id);
        $this->adminMail('ADMIN_VIRTUAL_CARD_APPLY', $params);
        $this->adminPushNotification('ADMIN_VIRTUAL_CARD_APPLY', $params, $action);
        $this->adminFirebasePushNotification('ADMIN_VIRTUAL_CARD_APPLY', $params, $firebaseAction);

        return true;
    }

    public function cardBlock(Request $request, $id)
    {
        $purifiedData = $request->all();
        $rules = [
            'reason' => 'required',
        ];
        $message = [
            'reason.required' => 'Reason field is required',
        ];

        $validate = Validator::make($purifiedData, $rules, $message);

        if ($validate->fails()) {
            return back()->withInput()->withErrors($validate);
        }
        $card = VirtualCardOrder::findOrFail($id);
        if ($card->user_id != auth()->id()) {
            return back()->with('error', 'You have not permission');
        }
        $card->status = 5;
        $card->reason = $purifiedData['reason'];
        $card->save();
        return back()->with('success', 'Block Request Send');
    }

    public function cardTransaction($card_id)
    {
        return view('user.virtual_card.transaction', compact('card_id'));
    }

    public function cardTransactionSearch($card_id)
    {
        $userId = auth()->id();
        $search = $request->search['value'] ?? null;

        $transfers = VirtualCardTransaction::with(['user', 'gateway', 'currency'])
            ->where('user_id', $userId)->where('card_id', $card_id)->latest()
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('amount', 'LIKE', "%{$search}%");
                });
            });

        return DataTables::of($transfers)
            ->addColumn('sl', function ($item) {
                static $index = 1;
                return $index++;
            })
            ->addColumn('provider', function ($item) {
                return optional(optional($item->cardOrder)->cardMethod)->name;
            })
            ->addColumn('amount', function ($item) {
                return '<h5> ' . $item->amount . ' ' . $item->currency_code . ' </h5>';
            })
            ->addColumn('type', function ($item) {
                return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Complete') . '
                  </span>';
            })
            ->addColumn('transaction_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->rawColumns(['sl', 'provider', 'amount', 'type', 'transaction_at'])
            ->make(true);
    }
}
