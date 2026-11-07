<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Transaction;
use App\Models\VirtualCardMethod;
use App\Models\VirtualCardOrder;
use App\Models\VirtualCardTransaction;
use App\Models\Wallet;
use App\Services\VirtualCardCurl;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Facades\App\Services\BasicService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\Facades\DataTables;

class VirtualCardController extends Controller
{
    use Upload, Notify;

    public function index()
    {
        $data['virtualCardMethods'] = VirtualCardMethod::orderBy('name', 'ASC')->get();
        return view('admin.virtual_card.index', $data);
    }

    public function edit($id)
    {
        $data['virtualCardMethod'] = VirtualCardMethod::findOrFail($id);
        return view('admin.virtual_card.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'currency' => 'required|string',
            'debit_currency' => 'required|string',
        ];

        $virtualCard = VirtualCardMethod::where('id', $id)->firstOr(function () {
            throw new \Exception('No virtual card method found.');
        });

        $validationRules = [
            'currency' => 'required',
            'debit_currency' => 'required',
        ];

        $parameters = [];
        foreach ($request->except('_token', '_method', 'image') as $k => $v) {
            foreach ($virtualCard->parameters as $key => $cus) {
                if ($k != $key) {
                    continue;
                } else {
                    $rules[$key] = 'required|max:191';
                    $parameters[$key] = $v;
                }
            }
        }
        $fund_params = [];
        foreach ($request->fund as $k => $v) {
            foreach ($virtualCard->add_fund_parameter as $key => $cus) {
                if ($k != $key) {
                    continue;
                } else {
                    foreach ($cus as $key1 => $cus1) {
                        $rules[$key][$key1] = 'required|max:191';
                        $fund_params[$key][$key1] = $cus1->field_value;
                    }
                }
            }
        }

        $collectionSpecification = collect($request->fund);
        $fund_params = [];
        foreach ($request->fund as $k => $v) {
            foreach ($virtualCard->add_fund_parameter as $key => $cus) {
                if ($k != $key) {
                    continue;
                } else {
                    foreach ($cus as $key1 => $cus1) {
                        $rules[$key][$key1] = 'required|min:0';
                        if ($v[$cus1->field_name] < 0 || $v[$cus1->field_name] == null) {
                            $v[$cus1->field_name] = 0;
                        }
                        $fund_params[$key][$key1] = [
                            'field_name' => $cus1->field_name,
                            'field_level' => $cus1->field_level,
                            'field_value' => $v[$cus1->field_name],
                            'type' => $cus1->type,
                            'validation' => $cus1->validation,
                        ];
                    }
                }
            }
        }

        $purifiedData = $request->all();
        $purifiedData['image'] = $request->image;
        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }
        $purifiedData = (object)$purifiedData;
        if ($request->hasFile('image')) {
            try {
                $uploadedImage = $this->fileUpload($request->image, config('filelocation.virtualCardMethod.path'), null, config('filelocation.virtualCardMethod.size'), 'webp', 60, $virtualCard->image, $virtualCard->driver);
            } catch (\Exception $exp) {
                return back()->with('error', 'Image could not be uploaded.');
            }
        }


        try {
            $response = $virtualCard->update([
                'currency' => $purifiedData->currency,
                'debit_currency' => $purifiedData->debit_currency,
                'parameters' => $parameters,
                'add_fund_parameter' => $fund_params,
                'info_box' => $purifiedData->info_box,
                'image' => $uploadedImage['path'] ?? $virtualCard->image,
                'driver' => $uploadedImage['driver'] ?? $virtualCard->driver,
            ]);

            if (!$response) {
                throw new \Exception('Unexpected error! Please try again.');
            }
            return back()->with('success', 'Virtual card data has been updated.');
        } catch (\Exception $exception) {
            return back()->with('alert', $exception->getMessage());
        }
    }

    public function statusChange(Request $request, $id)
    {
        DB::beginTransaction();
        try {
            $virtualMethod = VirtualCardMethod::findOrFail($id);
            $virtualMethod->status = 1;
            $virtualMethod->save();
            $virtualMethods = VirtualCardMethod::where('id', '!=', $id)->get();
            foreach ($virtualMethods as $virtualMethod) {
                $virtualMethod->status = 0;
                $virtualMethod->save();
            }
            DB::commit();
            return back()->with('success', 'Activated Successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('alert', 'Something Went Wrong');
        }
    }

    public function cardOrder(Request $request)
    {
        $data['virtualCardMethods'] = VirtualCardMethod::orderBy('name', 'ASC')->get();
        $data['cardOrders'] = collect(VirtualCardOrder::selectRaw('COUNT(id) AS totalRequest')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS pendingRequest')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(CASE WHEN status IN (0, 2, 3) THEN id END)) * 100 AS pendingRequestPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS approveRequest')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(CASE WHEN status IN (0, 2, 3) THEN id END)) * 100 AS approveRequestPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS rejectedRequest')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(CASE WHEN status IN (0, 2, 3) THEN id END)) * 100 AS rejectedRequestPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 3 THEN id END) AS reSubmitRequest')
            ->selectRaw('(COUNT(CASE WHEN status = 3 THEN id END) / COUNT(CASE WHEN status IN (0, 2, 3) THEN id END)) * 100 AS reSubmitRequestPercentage')
            ->whereIn('status', [0, 2, 3])
            ->get()
            ->toArray())
            ->collapse();

        return view('admin.virtual_card.cardOrder', $data);
    }

    public function cardOrderSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStatus = $request->filterStatus;
        $filterMethod = $request->filterMethod;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $cardOrders = VirtualCardOrder::with(['user', 'cardMethod'])->whereIn('status', [0, 2, 3])->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->whereHas('user', function ($qq) use ($filterName) {
                    $qq->where('username', 'LIKE', '%' . $filterName . '%');
                    $qq->orWhere('firstname', 'LIKE', '%' . $filterName . '%');
                    $qq->orWhere('lastname', 'LIKE', '%' . $filterName . '%');
                });
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(isset($filterMethod), function ($query) use ($filterMethod) {
                if ($filterMethod != "all") {
                    return $query->where('virtual_card_method_id', $filterMethod);
                }
            })
            ->when(!empty($request->filterDate) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filterDate) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('currency', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($cardOrders)
            ->addColumn('provider', function ($item) {
                return optional($item->cardMethod)->name;
            })
            ->addColumn('currency', function ($item) {
                return $item->currency;
            })
            ->addColumn('user', function ($item) {
                $url = route("admin.user.edit", $item->user_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                                <div class="flex-shrink-0">
                                  ' . optional($item->user)->profilePicture() . '
                                </div>
                                <div class="flex-grow-1 ms-3">
                                  <h5 class="text-hover-primary mb-0">' . optional($item->user)->firstname . ' ' . optional($item->user)->lastname . '</h5>
                                  <span class="fs-6 text-body">@' . optional($item->user)->username . '</span>
                                </div>
                              </a>';
            })
            ->addColumn('status', function ($item) {
                return $item->statusMessage;
            })
            ->addColumn('requested_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('admin.virtual.cardOrderDetail', $item->id);

                return "<div class='btn-group' role='group'>
                      <a href='" . $viewRoute . "' class='btn btn-white btn-sm'>
                        <i class='bi-eye me-1'></i> " . trans('View') . "
                      </a>";

            })
            ->rawColumns(['provider', 'currency', 'user', 'status', 'requested_at', 'action'])
            ->make(true);
    }

    public function cardOrderDetail($id)
    {
        $data['cardOrderDetail'] = VirtualCardOrder::findOrFail($id);
        return view('admin.virtual_card.cardOrderDetail', $data);
    }

    public function cardOrderRejected(Request $request, $id)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'reason' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();
        $cardOrder = VirtualCardOrder::where('status', '!=', '2')->findOrFail($id);
        $cardOrder->status = 2;
        $cardOrder->reason = $purifiedData['reason'];
        $cardOrder->resubmitted = $purifiedData['resubmitted'];
        $cardOrder->save();

        if ($this->chargeReturn($cardOrder)) {
            DB::commit();
        } else {
            DB::rollBack();
        }
        return back()->with('success', 'Request has been Rejected');
    }

    public function chargeReturn($cardOrder)
    {
        $chargeCurrency = $cardOrder->charge_currency;
        $virtualCardCharge = $cardOrder->charge;
        $availableBalance = Wallet::where('user_id', $cardOrder->user_id)->where('currency_id', $chargeCurrency)->first();
        if ($availableBalance == null) {
            return false;
        }
        $newBalance = $availableBalance->balance + $virtualCardCharge;
        $availableBalance->balance = $newBalance;
        $availableBalance->save();

        $remark = 'Virtual Card Charge Return';
        BasicService::makeTransaction($cardOrder->user, $chargeCurrency, $virtualCardCharge, 0, '+', strRandom(), $remark, $cardOrder->id, VirtualCardOrder::class);

        $params = [
            'amount' => $virtualCardCharge,
            'currency' => optional($cardOrder->currency)->code ?? null,
        ];
        $action = [
            "link" => "#",
            "icon" => "fa fa-money-bill-alt text-white"
        ];

        $this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_REJECTED', $params);
        $this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_REJECTED', $params, $action);
        $this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_REJECTED', $params);

        return true;
    }

    public function cardOrderApprove($id)
    {
        $cardOrder = VirtualCardOrder::with(['cardMethod'])->findOrFail($id);

        $methodObj = 'App\\Services\\VirtualCard\\' . optional($cardOrder->cardMethod)->code . '\\Card';

        $data = $methodObj::cardRequest($cardOrder, 'create');
        if (!$data) {
            return back()->with('alert', 'Method not available or unknown errors occur');
        }

        if ($data['status'] == 'error') {
            $cardOrder->last_error = $data['data'];
            $cardOrder->save();
            return back()->with('alert', $data['data']);
        } elseif ($data['status'] == 'success') {
            $this->cardCreate($cardOrder, $data['data'], $data['name_on_card'], $data['card_id'], $data['cvv'], $data['card_number'], $data['brand'], $data['expiry_date'], $data['balance']);
            $this->cardCreateNotify($cardOrder, $data['name_on_card'], $data['card_id'], $data['cvv'], $data['card_number'], $data['brand'], $data['expiry_date'], $data['balance']);
            return redirect()->route('admin.virtual.cardList')->with('success', 'Card Approved');
        }
    }

    public function cardCreateNotify($cardOrder, $name_on_card, $card_id, $cvv, $card_number, $brand, $expiry_date, $balance)
    {
        $params = [
            'name_on_card' => $name_on_card ?? null,
            'card_id' => $card_id ?? null,
            'cvv' => $cvv ?? null,
            'card_number' => $card_number ?? null,
            'brand' => $brand ?? null,
            'expiry_date' => $expiry_date ?? null,
            'balance' => $balance ?? null,
            'currency' => $cardOrder->currency ?? null,
        ];
        $action = [
            "link" => "#",
            "icon" => "fa fa-money-bill-alt text-white"
        ];

        $this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_APPROVE', $params);
        $this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_APPROVE', $params, $action);
        $this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_APPROVE', $params);
    }

    public function cardCreate($cardOrder, $data, $name_on_card, $card_id, $cvv, $card_number, $brand, $expiry_date, $balance)
    {
        $reqFieldSpecification = [];
        foreach ($data as $inKey => $inVal) {
            $reqFieldSpecification[$inKey] = [
                'field_name' => $inKey,
                'field_value' => $inVal,
            ];
        }
        $cardOrder->card_info = $reqFieldSpecification;
        $cardOrder->balance = $balance;
        $cardOrder->card_id = $card_id;
        $cardOrder->cvv = $cvv;
        $cardOrder->card_number = $card_number;
        $cardOrder->expiry_date = $expiry_date;
        $cardOrder->brand = $brand;
        $cardOrder->name_on_card = $name_on_card;
        $cardOrder->status = 1;
        $cardOrder->save();

        return 0;
    }

    public function cardList($type = 'all')
    {
        switch ($type) {
            case 'block':
                $type = 5;
                break;
            case 'add-fund':
                $type = 8;
                break;
            default:
                $type = 'all';
                break;
        }

        $data['virtualCardMethods'] = VirtualCardMethod::orderBy('name', 'ASC')->get();
        $data['cards'] = collect(VirtualCardOrder::cards()->selectRaw('COUNT(id) AS totalCard')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS approveCard')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(CASE WHEN status IN (1, 5, 6, 7, 8, 9) THEN id END)) * 100 AS approveCardPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 2 THEN id END) AS rejectedCard')
            ->selectRaw('(COUNT(CASE WHEN status = 2 THEN id END) / COUNT(CASE WHEN status IN (1, 5, 6, 7, 8, 9) THEN id END)) * 100 AS rejectedCardPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 7 THEN id END) AS blockCard')
            ->selectRaw('(COUNT(CASE WHEN status = 7 THEN id END) / COUNT(CASE WHEN status IN (1, 5, 6, 7, 8, 9) THEN id END)) * 100 AS blockCardPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 8 THEN id END) AS fundRequestCard')
            ->selectRaw('(COUNT(CASE WHEN status = 8 THEN id END) / COUNT(CASE WHEN status IN (1, 5, 6, 7, 8, 9) THEN id END)) * 100 AS fundRequestCardPercentage')
            ->get()
            ->toArray())
            ->collapse();

        $data['type'] = $type;
        return view('admin.virtual_card.card.index', $data);
    }

    public function cardListSearch(Request $request, $type = 'all')
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStatus = $request->filterStatus;
        $filterMethod = $request->filterMethod;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $cardOrders = VirtualCardOrder::cards()->with(['user', 'cardMethod'])->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('card_number', 'LIKE', "%$filterName%");
            })
            ->when($type != 'all', function ($query) use ($type) {
                return $query->where('status', $type);
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(isset($filterMethod), function ($query) use ($filterMethod) {
                if ($filterMethod != "all") {
                    return $query->where('virtual_card_method_id', $filterMethod);
                }
            })
            ->when(!empty($request->filterDate) && $endDate == null, function ($query) use ($startDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $query->whereDate('created_at', $startDate);
            })
            ->when(!empty($request->filterDate) && $endDate != null, function ($query) use ($startDate, $endDate) {
                $startDate = Carbon::createFromFormat('d/m/Y', trim($startDate));
                $endDate = Carbon::createFromFormat('d/m/Y', trim($endDate));
                $query->whereBetween('created_at', [$startDate, $endDate]);
            })
            ->when(!empty($search), function ($query) use ($search) {
                return $query->where(function ($subquery) use ($search) {
                    $subquery->where('balance', 'LIKE', "%{$search}%")
                        ->orWhere('card_number', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($cardOrders)
            ->addColumn('provider', function ($item) {
                return optional($item->cardMethod)->name;
            })
            ->addColumn('card_number', function ($item) {
                return $item->card_number;
            })
            ->addColumn('balance', function ($item) {
                return '<h5>' . $item->balance . ' ' . $item->currency . '</h5>';
            })
            ->addColumn('brand', function ($item) {
                return $item->brand;
            })
            ->addColumn('expiry_date', function ($item) {
                return $item->expiry_date;
            })
            ->addColumn('user', function ($item) {
                $url = route("admin.user.edit", $item->user_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0">
                              ' . optional($item->user)->profilePicture() . '
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->user)->name . '</h5>
                              <span class="fs-6 text-body">@' . optional($item->user)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('status', function ($item) {
                return $item->statusMessage;
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('admin.virtual.cardView', $item->id);
                $tranRoute = route('admin.virtual.cardTransaction', $item->id);

                return "<div class='btn-group' role='group'>
                      <a href='" . $viewRoute . "' class='btn btn-white btn-sm'>
                        <i class='bi-eye me-1'></i> " . trans('View') . "
                      </a>
                      <a href='" . $tranRoute . "' class='btn btn-white btn-sm ms-2'>
                        <i class='fa-light fa-chart-line me-1'></i> " . trans('Transaction') . "
                      </a>";

            })
            ->rawColumns(['provider', 'card_number', 'balance', 'brand', 'expiry_date', 'user', 'status', 'action'])
            ->make(true);
    }

    public function cardView($id)
    {
        $data['cardView'] = VirtualCardOrder::with(['cardMethod'])->findOrFail($id);
        return view('admin.virtual_card.card.view', $data);
    }

    public function cardBlock(Request $request, $id)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'reason' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $cardOrder = VirtualCardOrder::with(['cardMethod'])->findOrFail($id);

        $methodObj = 'App\\Services\\VirtualCard\\' . optional($cardOrder->cardMethod)->code . '\\Card';
        $data = $methodObj::cardRequest($cardOrder, 'block');

        if (!$data) {
            return back()->with('error', 'Method not available or unknown errors occur');
        }

        if ($data['status'] == 'error') {
            $cardOrder->last_error = $data['data'];
            $cardOrder->save();
            return back()->with('alert', $data['data']);
        } elseif ($data['status'] == 'success') {

            $cardOrder->status = 7;
            $cardOrder->save();

            $params = [
                'cardNumber' => $cardOrder->card_number ?? null,
            ];
            $action = [
                "link" => "#",
                "icon" => "fa fa-money-bill-alt text-white"
            ];

            $this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_BLOCK', $params);
            $this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_BLOCK', $params, $action);
            $this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_BLOCK', $params);

            return back()->with('success', 'Card has been blocked');
        }
    }

    public function cardUnBlock($id)
    {
        $cardOrder = VirtualCardOrder::with(['cardMethod'])->where('status', 7)->findOrFail($id);

        $methodObj = 'App\\Services\\VirtualCard\\' . optional($cardOrder->cardMethod)->code . '\\Card';
        $data = $methodObj::cardRequest($cardOrder, 'unblock');

        if (!$data) {
            return back()->with('error', 'Method not available or unknown errors occur');
        }

        if ($data['status'] == 'error') {
            $cardOrder->last_error = $data['data'];
            $cardOrder->save();
            return back()->with('alert', $data['data']);
        } elseif ($data['status'] == 'success') {
            $cardOrder->status = 1;
            $cardOrder->save();

            $params = [
                'cardNumber' => $cardOrder->card_number ?? null,
            ];
            $action = [
                "link" => "#",
                "icon" => "fa fa-money-bill-alt text-white"
            ];

            $this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_UNBLOCK', $params);
            $this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_UNBLOCK', $params, $action);
            $this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_UNBLOCK', $params);

            return back()->with('success', 'Card has been blocked');
        }
    }

    public function cardFundApprove($id)
    {
        $cardOrder = VirtualCardOrder::with(['cardMethod'])->where('status', 8)->findOrFail($id);

        $methodObj = 'App\\Services\\VirtualCard\\' . optional($cardOrder->cardMethod)->code . '\\Card';
        $data = $methodObj::cardRequest($cardOrder, 'fundApprove');

        if (!$data) {
            return back()->with('error', 'Method not available or unknown errors occur');
        }

        if ($data['status'] == 'error') {
            $cardOrder->last_error = $data['data'];
            $cardOrder->save();
            return back()->with('alert', $data['data']);
        } elseif ($data['status'] == 'success') {
            $cardOrder->status = 1;
            $cardOrder->balance = $data['balance'];
            $cardOrder->save();

            $params = [
                'amount' => $cardOrder->fund_amount ?? 0.00,
                'currency' => $cardOrder->currency ?? null,
                'cardNumber' => $cardOrder->card_number ?? null,
            ];
            $action = [
                "link" => "#",
                "icon" => "fa fa-money-bill-alt text-white"
            ];

            $this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_FUND_APPROVE', $params);
            $this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_FUND_APPROVE', $params, $action);
            $this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_FUND_APPROVE', $params);
            return back()->with('success', 'Card has been funded');
        }
    }

    public function cardFundReturn($id)
    {
        $cardOrder = VirtualCardOrder::where('status', 8)->findOrFail($id);
        $currency = Currency::select('id')->where('code', $cardOrder->currency)->firstOrFail();
        $cardOrder->status = 6;
        $cardOrder->save();

        $userId = $cardOrder->user_id;
        $currencyId = $currency->id;
        $amount = $cardOrder->fund_amount + $cardOrder->fund_charge;
        updateWallet($userId, $currencyId, $amount, 1);

        $remark = 'Virtual Card Fund Request Return';
        BasicService::makeTransaction($cardOrder->user, $currencyId, $cardOrder->fund_amount, 0, '+', strRandom(), $remark, $cardOrder->id, VirtualCardOrder::class);

        $params = [
            'amount' => $amount,
            'currency' => $cardOrder->currency ?? null,
            'cardNumber' => $cardOrder->card_number ?? null,
        ];
        $action = [
            "link" => "#",
            "icon" => "fa fa-money-bill-alt text-white"
        ];

        $this->sendMailSms($cardOrder->user, 'VIRTUAL_CARD_FUND_RETURN', $params);
        $this->userPushNotification($cardOrder->user, 'VIRTUAL_CARD_FUND_RETURN', $params, $action);
        $this->userFirebasePushNotification($cardOrder->user, 'VIRTUAL_CARD_FUND_RETURN', $params);

        return back()->with('success', 'Fund has been return');
    }

    public function cardTransaction($id)
    {
        $data['transactions'] = VirtualCardTransaction::with(['user', 'cardOrder.cardMethod'])->where('card_order_id', $id)->orderBy('id', 'desc')->paginate(20);
        return view('admin.virtual_card.card.transaction', $data);
    }

}
