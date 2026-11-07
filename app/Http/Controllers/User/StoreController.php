<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Store;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\Facades\DataTables;

class StoreController extends Controller
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

    public function storeList()
    {
        $data['stores'] = collect(Store::selectRaw('COUNT(id) AS totalStore')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeStore')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeStorePercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS inactiveStore')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS inactiveStorePercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayStore')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayStorePercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthStore')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthStorePercentage')
            ->own()
            ->get()
            ->toArray())->collapse();

        return view('user.store.storeList', $data);
    }

    public function storeListSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_name;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $stores = Store::withCount('productsMap')->own()->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('name', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
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
                    $subquery->where('name', 'LIKE', "%{$search}%");
                });
            });

        return DataTables::of($stores)
            ->addColumn('store', function ($item) {
                return '
                  <a class="d-flex align-items-center" href="#">
                    <div class="flex-grow-1">
                      <h5 class="text-inherit mb-0">'.$item->name.'</h5>
                    </div>
                  </a>';
            })
            ->addColumn('product', function ($item) {
                return '<span class="text-dark badge bg-soft-dark">' . number_format($item->products_map_count) . '</span>';
            })
            ->addColumn('shipping_charge', function ($item) {
                if ($item->shipping_charge == 1) {
                    return '<span class="badge bg-soft-primary text-primary">
                    <span class="legend-indicator bg-primary"></span>' . trans('Active') . '
                  </span>';

                } else {
                    return '<span class="badge bg-soft-dark text-dark">
                    <span class="legend-indicator bg-dark"></span>' . trans('Inactive') . '
                  </span>';
                }
            })
            ->addColumn('delivery_note', function ($item) {
                return $item->type();
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
            ->addColumn('copy_link', function ($item) {
                $route = route('public.view', $item->link);
                return '<a href="javascript:void(0)" onclick="copyText(\'' . $route . '\')">
                    <i class="bi-clipboard"></i>
                </a>';
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('user.store.edit', $item->id);
                $deleteRoute = route('user.store.delete', $item->id);

                return '
                <div class="btn-group" role="group">
                    <a href="'.$viewRoute.'" class="btn btn-white btn-sm" >
                        <i class="bi-pencil-square me-1"></i> ' . trans("Edit") . '
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                           <a class="dropdown-item" href="#"
                                data-route="' . $deleteRoute . '"
                                data-bs-target="#storeDelete" data-bs-toggle="modal">
                                <i class="bi-trash dropdown-item-icon"></i> ' . trans("Delete") . '
                            </a>
                        </div>
                    </div>
                </div>';

            })
            ->rawColumns(['store', 'product', 'shipping_charge', 'delivery_note', 'status', 'copy_link', 'action'])
            ->make(true);
    }

    public function storeCreate(Request $request)
    {
        if ($request->method() == 'GET') {
            return view('user.store.storeCreate');
        }
        if ($request->method() == 'POST') {
            $purifiedData = $request->all();
            $validator = Validator::make($purifiedData, [
                'name' => 'required',
                'shipping_charge' => 'required',
                'delivery_note' => 'required',
                'status' => 'required',
                'image' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            if (auth()->user()->store_currency_id == null) {
                return back()->withInput()->with('error', 'Please Set Currency From Setting Option');
            }
            $store = Store::where('link', $request->link)->exists();
            if ($store) {
                return back()->withInput()->with('error', 'Link Already Exists');
            }

            if (preg_match('/' . preg_quote('/', '/') . '/', $request->link)) {
                return back()->withInput()->with('error', '"/" can not be uses');
            }

            $store = new Store();
            $store->user_id = Auth::id();
            $store->name = $purifiedData['name'];
            $store->shipping_charge = $purifiedData['shipping_charge'];
            $store->status = $purifiedData['status'];
            $store->delivery_note = $purifiedData['delivery_note'];
            if ($request->hasFile('image')) {
                $upload = $this->fileUpload($request->image, config('filelocation.store.path'), null, config('filelocation.store.size'), 'webp');
            }
            $store->image = $upload['path'] ?? null;
            $store->driver = $upload['driver'] ?? null;
            $store->short_description = $purifiedData['short_description'];
            $store->save();

            if ($request->link == null) {
                $data = $store->id . '|' . $store->name;
                $store->link = $this->encrypt($data);
            } else {
                $store->link = $purifiedData['link'];
            }

            $store->save();
            return redirect()->route('user.store.list')->with('success', 'Store Create Successfully');
        }
    }

    public function encrypt($data)
    {
        return implode(unpack("H*", $data));
    }

    public function storeEdit($id, Request $request)
    {
        $data['store'] = Store::own()->findOrFail($id);
        if ($request->method() == 'GET') {
            return view('user.store.storeEdit', $data);
        }
        if ($request->method() == 'POST') {
            $purifiedData = $request->all();
            $validator = Validator::make($purifiedData, [
                'name' => 'required',
                'shipping_charge' => 'required',
                'delivery_note' => 'required',
                'status' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $store = Store::where('link', $purifiedData['link'])->where('id', '!=', $data['store']->id)->exists();
            if ($store) {
                return back()->with('error', 'Link Already Exists');
            }

            if (preg_match('/' . preg_quote('/', '/') . '/', $purifiedData['link'])) {
                return back()->withInput()->with('error', '"/" can not be uses');
            }

            $data['store']->name = $purifiedData['name'];
            $data['store']->shipping_charge = $purifiedData['shipping_charge'];
            $data['store']->status = $purifiedData['status'];
            $data['store']->delivery_note = $purifiedData['delivery_note'];
            $data['store']->short_description = $purifiedData['short_description'];
            if ($request->image) {
                $upload = $this->fileUpload($request->image, config('filelocation.store.path'), null, config('filelocation.store.size'), 'webp', null, $data['store']->image, $data['store']->driver);
                $data['store']->image = $upload['path'];
                $data['store']->driver = $upload['driver'];
            }
            $data['store']->link = $purifiedData['link'];
            $data['store']->save();
            return back()->with('success', 'Updated Successfully');
        }

    }

    public function storeLinkCheck(Request $request)
    {
        if (preg_match('/' . preg_quote('/', '/') . '/', $request->link)) {
            return response()->json([
                'status' => 'success',
                'msg' => '"/" can not be uses'
            ]);
        }
        if ($request->storeId == -1) {
            $store = Store::where('link', $request->link)->exists();
            if ($store) {
                return response()->json([
                    'status' => 'success',
                    'msg' => 'Link Already Exists'
                ]);
            } else {
                return response()->json([
                    'status' => 'notFound',
                ]);
            }
        } else {

            $store = Store::where('link', $request->link)->where('id', '!=', $request->storeId)->exists();
            if ($store) {
                return response()->json([
                    'status' => 'success',
                    'msg' => 'Link Already Exists'
                ]);
            } else {
                return response()->json([
                    'status' => 'notFound',
                ]);
            }

        }
    }

    public function storeDelete($id)
    {
        $store = Store::own()->with(['productsMap'])->findOrFail($id);
        if (count($store->productsMap) > 0) {
            return back()->with('error', 'Store has lot of product');
        }
        $store->delete();
        return back()->with('success', 'Deleted Successfully');
    }
}
