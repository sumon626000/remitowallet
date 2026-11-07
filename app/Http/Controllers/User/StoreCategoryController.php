<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\StoreCategory;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\Facades\DataTables;

class StoreCategoryController extends Controller
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

    public function categoryList()
    {
        $data['categories'] = collect(StoreCategory::selectRaw('COUNT(id) AS totalCategory')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeCategory')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeCategoryPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS inactiveCategory')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS inactiveCategoryPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayCategory')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayCategoryPercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthCategory')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthCategoryPercentage')
            ->own()
            ->get()
            ->toArray())->collapse();

        return view('user.store.category.index', $data);
    }

    public function categoryListSearch(Request $request)
    {
        $userId = auth()->id();
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_name;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = StoreCategory::withCount('activeProducts')
            ->where('user_id', $userId)->latest()
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
        return DataTables::of($transfers)
            ->addColumn('sl', function ($item) {
                static $counter = 0;
                $counter++;
                return $counter;
            })
            ->addColumn('name', function ($item) {
                return $item->name;
            })
            ->addColumn('active_products', function ($item) {
                return '<span class="text-dark badge bg-soft-dark">' . $item->active_products_count . '</span>';
            })
            ->addColumn('status', function ($item) {
                if ($item->status == 1) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Active') . '
                  </span>';

                } else {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('In-Active') . '
                  </span>';
                }
            })
            ->addColumn('created_at', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->addColumn('action', function ($item) {
                $editUrl = route('user.category.update', $item->id);
                $deleteRoute = route('user.category.delete', $item->id);
                return '
                <div class="btn-group" role="group">
                    <a href="#" class="btn btn-white btn-sm editCategory"
                        data-bs-target="#editCategory" data-bs-toggle="modal"
                        data-name="' . $item->name . '" data-status="' . $item->status . '"
                        data-route="' . $editUrl . '"
                    > <i class="bi-pencil-square me-1"></i> ' . trans("Edit") . '
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                           <a class="dropdown-item" href="#"
                                data-route="' . $deleteRoute . '"
                                data-bs-target="#categoryDelete" data-bs-toggle="modal">
                                <i class="bi-trash dropdown-item-icon"></i> ' . trans("Delete") . '
                            </a>
                        </div>
                    </div>
                </div>';
            })
            ->rawColumns(['sl', 'name', 'active_products', 'status', 'created_at', 'action'])
            ->make(true);
    }

    public function categorySave(Request $request)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'name' => 'required',
        ]);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $storeCategory = new StoreCategory();
        $storeCategory->user_id = Auth::id();
        $storeCategory->name = $request->name;
        $storeCategory->status = $request->status;

        $storeCategory->save();
        return back()->with('success', 'Added Successfully');
    }

    public function categoryUpdate(Request $request, $id)
    {
        $purifiedData = $request->all();
        $validator = Validator::make($purifiedData, [
            'name' => 'required',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $storeCategory = StoreCategory::own()->findOrFail($id);
        $storeCategory->name = $request->name;
        $storeCategory->status = $request->status;

        $storeCategory->save();
        return back()->with('success', 'Updated Successfully');
    }

    public function categoryDelete($id)
    {
        $storeCategory = StoreCategory::own()->with(['products'])->findOrFail($id);
        if (count($storeCategory->products) > 0) {
            return back()->with('alert', 'This category has lot of products');
        }
        $storeCategory->delete();
        return back()->with('success', 'Deleted Successfully');
    }
}
