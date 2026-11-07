<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreShipping;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\Facades\DataTables;

class StoreShippingController extends Controller
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

    public function shippingList()
    {
        $data['stores'] = Store::own()->orderBy('name', 'asc')->get();
        $data['shipping'] = StoreShipping::own()->withCount('orders')->orderBy('id', 'desc')->get();
        return view('user.store.shipping.index', $data);
    }

    public function shippingSave(Request $request)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'store' => 'required',
            'address' => 'required',
            'charge' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $storeShipping = new StoreShipping();
        $storeShipping->user_id = Auth::id();
        $storeShipping->store_id = $request->store;
        $storeShipping->address = $request->address;
        $storeShipping->charge = $request->charge;
        $storeShipping->status = $request->status;

        $storeShipping->save();
        session()->flash('success', 'Created Successfully');
        return response()->json([
            'status' => 'success',
            'url' => route('user.shipping.list')
        ]);
    }

    public function shippingUpdate(Request $request)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'store' => 'required',
            'address' => 'required',
            'charge' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $storeShipping = StoreShipping::own()->find($request->id);
        $storeShipping->store_id = $request->store;
        $storeShipping->address = $request->address;
        $storeShipping->charge = $request->charge;
        $storeShipping->status = $request->status;

        $storeShipping->save();
        session()->flash('success', 'Updated Successfully');
        return response()->json([
            'status' => 'success',
            'url' => route('user.shipping.list')
        ]);
    }

    public function shippingDelete($id)
    {
        $storeShipping = StoreShipping::own()->with(['orders'])->findOrFail($id);
        if (count($storeShipping->orders)) {
            return back()->with('error', 'shipping has lot of orders');
        }
        $storeShipping->delete();
        return back()->with('success', 'Deleted Successfully');
    }
}
