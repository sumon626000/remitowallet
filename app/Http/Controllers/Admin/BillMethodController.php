<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BillMethod;
use App\Traits\Upload;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;

class BillMethodController extends Controller
{
    use Upload;

    public function index()
    {
        $data['billMethods'] = BillMethod::get();
        return view('admin.bill_payment.methods.index', $data);
    }

    public function edit($id)
    {
        $data['billMethod'] = BillMethod::with(['billServices'])->findOrFail($id);
        try {
            $billServices = $data['billMethod']->billServices->groupBy('country');
            $countryLists = config('country');

            $isoCode = [];
            foreach ($billServices as $key => $item) {
                foreach ($countryLists as $value) {
                    if ($key == $value['code']) {
                        $isoCode[$value['iso_code']] = $value['iso_code'];
                        break;
                    }
                }
            }
            $data['isoCodes'] = $isoCode;
            return view('admin.bill_payment.methods.edit', $data);
        } catch (\Exception $e) {
            return back()->with('success', $e->getMessage());
        }
    }

    public function save(Request $request, $id)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'methodName' => 'required|min:3|max:50',
            'description' => 'required|min:3|max:500',
        ], [
            'min' => 'This field must be at least :min characters.',
            'string' => 'This field must be :string.',
            'required' => 'This field is required.',
        ]);


        $purifiedData = (object)$purifiedData;
        $data['billMethod'] = BillMethod::with(['billServices'])->findOrFail($id);

        $parameters = [];
        if ($data['billMethod']->parameters) {
            foreach ($request->except('_token', '_method', 'image') as $k => $v) {
                foreach ($data['billMethod']->parameters as $key => $cus) {
                    if ($k != $key) {
                        continue;
                    } else {
                        $rules[$key] = 'required|max:191';
                        $parameters[$key] = $v;
                    }
                }
            }
        }

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            $data['billMethod']->methodName = $purifiedData->methodName;
            $data['billMethod']->description = $purifiedData->description;
            $data['billMethod']->parameters = $parameters;

            if ($request->file('logo')) {
                $old = $data['billMethod']->logo;
                $oldDriver = $data['billMethod']->driver;
                $uploadedImage = $this->fileUpload($request->logo, config('filelocation.billPaymentMethod.path'), null, config('filelocation.billPaymentMethod.size'), 'webp', 60, $old, $oldDriver);
                $data['billMethod']->logo = $uploadedImage['path'] ?? null;
                $data['billMethod']->driver = $uploadedImage['driver'] ?? null;
            }

            $data['billMethod']->save();
            return back()->with('success', 'Method Successfully Saved');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function activated($id)
    {
        $billMethod = BillMethod::findOrFail($id);
        try {
            BillMethod::where('id', '!=', $id)->get()->map(function ($query) {
                $query->is_active = 0;
                $query->save();
            });

            $billMethod->is_active = 1;
            $billMethod->save();
            return back()->with('success', $billMethod->methodName . ' Activated Successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function serviceRate(Request $request, $id)
    {
        $billMethod = BillMethod::findOrFail($id);
        try {
            $collectionSpecification = collect($request->convert_rate);
            $rate_params = [];
            if ($collectionSpecification) {
                foreach ($collectionSpecification as $k => $v) {
                    if ($v == null) {
                        $v = 1.00;
                    }
                    $rate_params[$k] = $v;
                }
            }
            $billMethod->convert_rate = $rate_params;
            $billMethod->save();
            return redirect()->route('admin.bill.method.edit', $billMethod->id)->with('success', 'Rate Updated');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

}
