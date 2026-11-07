<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillMethod;
use App\Models\BillService;
use App\Traits\BillPay;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BillServiceController extends Controller
{
    use BillPay;

    public function fetchServices(Request $request, $id)
    {
        $billMethod = BillMethod::findOrFail($id);

        $purifiedData = $request->all();
        $validationRules = [
            'api_service' => 'required',
            'operator_id' => 'sometimes',
        ];
        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $methodObj = 'App\\Services\\Bill\\' . $billMethod->code . '\\Card';
        if ($billMethod->code == 'reloadly' && $request->api_service == 'AIRTIME') {
            $response = $methodObj::fetchOperators($request->all(), $billMethod);
        } else {
            $response = $methodObj::fetchServices($request->all(), $billMethod);
        }

        if (!$response) {
            return back()->with('error', 'Something Went Wrong');
        }
        if ($response['status'] == 'error') {
            return back()->with('error', $response['data']);
        }
        if ($response['status'] == 'success') {
            $data['services'] = $response['data'];
            $data['api_service'] = $request->api_service;
            $data['billMethod'] = $billMethod;
            return view('admin.bill_payment.fetch_service.fetch_service', $data);
        }
    }

    public function addServices(Request $request)
    {
        $billMethod = BillMethod::find($request->methodId);
        if (!$billMethod) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Bill method not found'
            ]);
        }
        $response = $request->res;
        $api_service = $request->api_service;
        $resInFormat = $this->getFormatRes($response, $api_service, $billMethod);

        $service = BillService::where('code', $resInFormat['code'])
            ->where('country', $this->getCountry($billMethod, $response))->where('service', $api_service)->first();
        if ($service) {
            $service->type = $resInFormat['type'];
            $service->country = $this->getCountry($billMethod, $response, $api_service);
            $service->amount = $resInFormat['amount'];
            $service->min_amount = $resInFormat['min_amount'];
            $service->max_amount = $resInFormat['max_amount'];
            $service->fixed_charge = $resInFormat['fixed_charge'];
            $service->extra_response = $resInFormat['extra'];
            $service->save();
        } else {

            $service = new BillService();
            $service->bill_method_id = $billMethod->id;
            $service->service = $api_service;
            $service->code = $resInFormat['code'];
            $service->type = $resInFormat['type'];
            $service->country = $this->getCountry($billMethod, $response);
            $service->info = $response;
            $service->label_name = $resInFormat['label_name'];
            $service->amount = $resInFormat['amount'];
            $service->currency = $this->getIsoCode($this->getCountry($billMethod, $response, $api_service));
            $service->min_amount = $resInFormat['min_amount'];
            $service->max_amount = $resInFormat['max_amount'];
            $service->fixed_charge = $resInFormat['fixed_charge'];
            $service->extra_response = $resInFormat['extra'];
            $service->save();
        }

        return response()->json([
            'status' => 'success'
        ]);
    }

    public function addServicesBulk(Request $request)
    {
        $billMethod = BillMethod::find($request->methodId);
        if (!$billMethod) {
            return response()->json([
                'status' => 'error',
                'msg' => 'Bill method not found'
            ]);
        }
        $responses = $request->res;
        $api_service = $request->api_service;

        if (count($responses) < 1) {
            return response()->json([
                'status' => 'error',
            ]);
        }

        foreach ($responses as $response) {
            try {
                $response = json_decode($response);
                $resInFormat = $this->getFormatRes($response, $api_service, $billMethod);

                $service = BillService::where('code', $resInFormat['code'])
                    ->where('country', $this->getCountry($billMethod, $response))->where('service', $api_service)->first();
                if ($service) {
                    $service->type = $resInFormat['type'];
                    $service->country = $this->getCountry($billMethod, $response, $api_service);
                    $service->amount = $resInFormat['amount'];
                    $service->min_amount = $resInFormat['min_amount'];
                    $service->max_amount = $resInFormat['max_amount'];
                    $service->fixed_charge = $resInFormat['fixed_charge'];
                    $service->extra_response = $resInFormat['extra'];
                    $service->save();
                } else {
                    $service = new BillService();
                    $service->bill_method_id = $billMethod->id;
                    $service->service = $api_service;
                    $service->code = $resInFormat['code'];
                    $service->type = $resInFormat['type'];
                    $service->country = $this->getCountry($billMethod, $response, $api_service);
                    $service->info = $response;
                    $service->label_name = $resInFormat['label_name'];
                    $service->amount = $resInFormat['amount'];
                    $service->currency = $this->getIsoCode($this->getCountry($billMethod, $response, $api_service));
                    $service->min_amount = $resInFormat['min_amount'];
                    $service->max_amount = $resInFormat['max_amount'];
                    $service->fixed_charge = $resInFormat['fixed_charge'];
                    $service->extra_response = $resInFormat['extra'];
                    $service->save();
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        session()->flash('success', 'Added Successfully');
        return response()->json([
            'status' => 'success',
            'route' => route('admin.bill.method.edit', $billMethod->id)
        ]);
    }

    public function serviceList(Request $request)
    {
        $search = $request->all();
        try {
            $data['services'] = BillService::with('method')->whereHas('method', function ($query) {
                $query->where('is_active', 1);
            })
                ->when(isset($search['type']) && $search['type'], function ($query) use ($search) {
                    return $query->where("type", 'LIKE', '%' . $search['type'] . '%');
                })
                ->when(isset($search['code']) && $search['code'], function ($query) use ($search) {
                    return $query->where("code", 'LIKE', '%' . $search['code'] . '%');
                })
                ->when(isset($search['status']) && $search['status'] != 'all', function ($query) use ($search) {
                    if ($search['status'] == 'active') {
                        return $query->where("status", 1);
                    } elseif ($search['status'] == 'inActive') {
                        return $query->where("status", 0);
                    }
                })
                ->when(isset($search['service']) && $search['service'] != 'all', function ($query) use ($search) {

                    return $query->where("service", 'LIKE', '%' . $search['service'] . '%');
                })
                ->latest()->paginate(20);

            $data['activeMethod'] = BillMethod::select('id', 'methodName')->where('is_active', 1)->firstOrFail();
            $data['countries'] = BillService::where('bill_method_id', $data['activeMethod']->id)->groupBy('country')->get();
            $data['categories'] = BillService::where('bill_method_id', $data['activeMethod']->id)->groupBy('service')->get();
            $data['countryList'] = config('country');
            return view('admin.bill_payment.service.index', $data);
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function chargeLimitAdd(Request $request)
    {
        $purifiedData = $request->all();
        $validationRules = [
            'service' => 'required',
            'country' => 'required',
            'percent_charge' => 'required',
            'fixed_charge' => 'required',
            'currency' => 'required',
        ];
        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }
        try {
            $services = BillService::where('country', $request->country)->where('service', $request->service)->get();
            foreach ($services as $service) {
                $service->percent_charge = $request->percent_charge;
                $service->fixed_charge = $request->fixed_charge;
                $service->min_amount = $request->min_amount;
                $service->max_amount = $request->max_amount;
                $service->currency = $request->currency;
                $service->save();
            }

            return back()->with('success', 'Charge and Limit has been applied');
        } catch (\Exception $e) {
            return back()->with('alert', $e->getMessage());
        }
    }

    public function chargeLimitEdit(Request $request, $id)
    {
        $purifiedData = $request->all();
        $validationRules = [
            'percent_charge' => 'required',
            'fixed_charge' => 'required',
            'currency' => 'required',
        ];
        $validate = Validator::make($purifiedData, $validationRules);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }
        $service = BillService::findOrFail($id);
        try {
            $service->percent_charge = $request->percent_charge;
            $service->fixed_charge = $request->fixed_charge;
            $service->min_amount = $request->min_amount;
            $service->max_amount = $request->max_amount;
            $service->currency = $request->currency;
            $service->save();
            return back()->with('success', 'Charge and Limit has been updated');
        } catch (\Exception $e) {
            return back()->with('alert', $e->getMessage());
        }
    }

    public function statusChange($id)
    {
        $service = BillService::findOrFail($id);
        try {
            if ($service) {
                if ($service->status == 1) {
                    $service->status = 0;
                } else {
                    $service->status = 1;
                }
                $service->save();
                return back()->with('success', 'Updated Successfully');
            }
        } catch (\Exception $e) {
            return back()->with('alert', $e->getMessage());
        }
    }

    public function fetchOperators(Request $request)
    {
        $billMethod = BillMethod::where('code', $request->methodCode)->first();
        if ($billMethod) {
            $methodObj = 'App\\Services\\Bill\\' . $billMethod->code . '\\Card';
            $response = $methodObj::fetchOperators($request->category, $billMethod);
            return response()->json([
                'status' => 'success',
                'data' => $response
            ]);
        }

        return response()->json([
            'status' => 'error',
            'data' => 'something went wrong'
        ]);
    }

    public function fetchBalance(Request $request)
    {
        $billMethod = BillMethod::where('code', $request->code)->first();
        if ($billMethod) {
            $methodObj = 'App\\Services\\Bill\\' . $billMethod->code . '\\Card';
            $response = $methodObj::getBalance($billMethod, $request->currencyCode);

            if ($response['status'] == 'success') {
                return response()->json([
                    'status' => 'success',
                    'balance' => getAmount($response['balance'], 2),
                    'currencyCode' => $response['currencyCode']
                ]);
            }
            return response()->json([
                'status' => 'error',
            ]);
        }
    }
}
