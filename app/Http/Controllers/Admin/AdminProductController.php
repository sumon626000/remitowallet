<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\SellerContact;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreProduct;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdminProductController extends Controller
{
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
            ->get()
            ->toArray())->collapse();

        return view('admin.store.storeList', $data);
    }

    public function storeListSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_name;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $stores = Store::withCount('productsMap')->latest()
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
                    $subquery->where('name', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($stores)
            ->addColumn('store', function ($item) {
                $image = getFile($item->driver, $item->image);
                return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                            <div class="flex-grow-1 ">
                                <h5 class="text-hover-primary mb-0">' . $item->name . '</h5>
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
            ->addColumn('owner', function ($item) {
                $url = route("admin.user.edit", $item->user_id);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0"> ' . optional($item->user)->profilePicture() . ' </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->user)->name . '</h5>
                              <span class="fs-6 text-body">@' . optional($item->user)->username . '</span>
                            </div>
                        </a>';
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
                $viewRoute = route('admin.store.view', $item->id);
                return "<a href='" . $viewRoute . "' class='btn btn-white btn-sm'>
                            <i class='bi-eye me-1'></i> " . trans('View') . "
                        </a>";
            })
            ->rawColumns(['store', 'product', 'shipping_charge', 'delivery_note', 'owner', 'status', 'copy_link', 'action'])
            ->make(true);
    }

    public function storeView($id)
    {
        $data['store'] = Store::findOrFail($id);
        return view('admin.store.storeView', $data);
    }

    public function productList()
    {
        $data['categories'] = StoreCategory::orderBy('name', 'asc')->get();
        $data['products'] = collect(StoreProduct::selectRaw('COUNT(id) AS totalProduct')
            ->selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS activeProduct')
            ->selectRaw('(COUNT(CASE WHEN status = 1 THEN id END) / COUNT(id)) * 100 AS activeProductPercentage')
            ->selectRaw('COUNT(CASE WHEN status = 0 THEN id END) AS inactiveProduct')
            ->selectRaw('(COUNT(CASE WHEN status = 0 THEN id END) / COUNT(id)) * 100 AS inactiveProductPercentage')
            ->selectRaw('COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) AS todayProduct')
            ->selectRaw('(COUNT(CASE WHEN DATE(created_at) = CURRENT_DATE THEN id END) / COUNT(id)) * 100 AS todayProductPercentage')
            ->selectRaw('COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) AS thisMonthProduct')
            ->selectRaw('(COUNT(CASE WHEN MONTH(created_at) = MONTH(CURDATE()) AND YEAR(created_at) = YEAR(CURDATE()) THEN id END) / COUNT(id)) * 100 AS thisMonthProductPercentage')
            ->get()
            ->toArray())->collapse();

        return view('admin.store.product.productList', $data);
    }

    public function productListSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_name;
        $filterCategory = $request->filter_category;
        $filterStatus = $request->filter_status;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = StoreProduct::with(['category', 'user'])->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('name', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStatus), function ($query) use ($filterStatus) {
                if ($filterStatus != "all") {
                    return $query->where('status', $filterStatus);
                }
            })
            ->when(isset($filterCategory), function ($query) use ($filterCategory) {
                if ($filterCategory != "all") {
                    return $query->where('category_id', $filterCategory);
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
                    $subquery->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($transfers)
            ->addColumn('product', function ($item) {
                $image = getFile($item->driver, $item->thumbnail);
                return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                            <div class="flex-shrink-0">
                              <div class="avatar avatar-sm avatar-circle">
                                <img class="avatar-img" src="' . $image . '" alt="...">
                             </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . $item->name . '</h5>
                            </div>
                        </a>';
            })
            ->addColumn('price', function ($item) {
                $amount = currencyPosition($item->price, $item->user->sotre_currency_id);
                return '<span class="amount-highlight">'.$amount.'</span>';
            })
            ->addColumn('category', function ($item) {
                return optional($item->category)->name;
            })
            ->addColumn('owner', function ($item) {
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
            ->addColumn('transfer_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('admin.product.view', $item->id);
                return " <a href='" . $viewRoute . "' class='btn btn-white btn-sm'>
                            <i class='bi-eye me-1'></i> " . trans('View') . "
                        </a>";
            })
            ->rawColumns(['product', 'price', 'category', 'owner', 'status', 'action'])
            ->make(true);
    }

    public function productView($id)
    {
        $data['product'] = StoreProduct::with(['productImages', 'productStores.store', 'productAttrs.attribute'])->findOrFail($id);
        return view('admin.store.product.productView', $data);
    }

    public function orderList()
    {
        $data['gateways'] = Gateway::select('id', 'name')->orderBy('name', 'ASC')->get();
        $data['orders'] = collect(ProductOrder::selectRaw('COUNT(CASE WHEN status = 1 THEN id END) AS totalOrder')
            ->selectRaw('COUNT(CASE WHEN stage IS NULL AND status = 1 THEN id END) AS newArriveOrder')
            ->selectRaw('(COUNT(CASE WHEN stage IS NULL AND status = 1 THEN id END) / COUNT(CASE WHEN status = 1 THEN id END)) * 100 AS newArriveOrderPercentage')
            ->selectRaw('COUNT(CASE WHEN stage = 1 AND status = 1 THEN id END) AS processingOrder')
            ->selectRaw('(COUNT(CASE WHEN stage = 1 AND status = 1 THEN id END) / COUNT(CASE WHEN status = 1 THEN id END)) * 100 AS processingOrderPercentage')
            ->selectRaw('COUNT(CASE WHEN stage = 4 AND status = 1 THEN id END) AS deliveredOrder')
            ->selectRaw('(COUNT(CASE WHEN stage = 4 AND status = 1 THEN id END) / COUNT(CASE WHEN status = 1 THEN id END)) * 100 AS deliveredOrderPercentage')
            ->selectRaw('COUNT(CASE WHEN stage = 5 AND status = 1 THEN id END) AS cancelOrder')
            ->selectRaw('(COUNT(CASE WHEN stage = 5 AND status = 1 THEN id END) / COUNT(CASE WHEN status = 1 THEN id END)) * 100 AS cancelOrderPercentage')
            ->get()
            ->toArray())->collapse();


        return view('admin.store.orderList', $data);
    }

    public function orderListSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStage = $request->filterStage;
        $filterGateway = $request->filterGateway;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = ProductOrder::query()
            ->with(['store','store.user','gateway'])
            ->where('status', 1)->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('order_number', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStage), function ($query) use ($filterStage) {
                if ($filterStage != "all") {
                    if ($filterStage == -1) {
                        return $query->where('stage', null);
                    }
                    return $query->where('stage', $filterStage);
                }
            })
            ->when(isset($filterGateway), function ($query) use ($filterGateway) {
                if ($filterGateway != "all") {
                    return $query->where('gateway_id', $filterGateway);
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
                    $subquery->where('order_number', 'LIKE', "%{$search}%")
                        ->orWhere('total_amount', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($transfers)
            ->addColumn('order_number', function ($item) {
                return $item->order_number;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->total_amount, $item->store?->user?->sotre_currency_id);
                return '<span class="amount-highlight">'.$amount.'</span>';
            })
            ->addColumn('shipping_charge', function ($item) {
                $charge = currencyPosition($item->shipping_charge, $item->store?->user?->sotre_currency_id);
                return '<span class="text-danger">' . $charge . '</span>';
            })
            ->addColumn('email', function ($item) {
                return $item->email;
            })
            ->addColumn('store', function ($item) {
                $image = getFile(optional($item->store)->driver, optional($item->store)->image);
                return '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                            <div class="flex-shrink-0">
                              <div class="avatar avatar-sm avatar-circle">
                                <img class="avatar-img" src="' . $image . '" alt="...">
                             </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional($item->store)->name . '</h5>
                            </div>
                        </a>';
            })
            ->addColumn('gateway', function ($item) {
                return optional($item->gateway)->name;
            })
            ->addColumn('user', function ($item) {
                $url = route("admin.user.edit", $item->store?->user_id ?? 0);
                return '<a class="d-flex align-items-center me-2" href="' . $url . '">
                            <div class="flex-shrink-0">
                              ' . optional(optional($item->store)->user)->profilePicture() . '
                            </div>
                            <div class="flex-grow-1 ms-3">
                              <h5 class="text-hover-primary mb-0">' . optional(optional($item->store)->user)->name . '</h5>
                              <span class="fs-6 text-body">@' . optional(optional($item->store)->user)->username . '</span>
                            </div>
                        </a>';
            })
            ->addColumn('status', function ($item) {
                if ($item->stage == 1) {
                    return '<span class="badge bg-soft-info text-info">
                    <span class="legend-indicator bg-info"></span>' . trans('Processing') . '
                  </span>';

                } elseif ($item->stage == 2) {
                    return '<span class="badge bg-soft-dark text-dark">
                    <span class="legend-indicator bg-dark"></span>' . trans('On Shipping') . '
                  </span>';
                } elseif ($item->stage == 3) {
                    return '<span class="badge bg-soft-primary text-primary">
                    <span class="legend-indicator bg-primary"></span>' . trans('Out For Delivery') . '
                  </span>';
                } elseif ($item->stage == 4) {
                    return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Delivered') . '
                  </span>';
                } elseif ($item->stage == 5) {
                    return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Cancel') . '
                  </span>';
                } else {
                    return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('New Arrival') . '
                  </span>';
                }
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('admin.order.view', $item->order_number);
                return " <a href='" . $viewRoute . "' class='btn btn-white btn-sm'>
                        <i class='bi-eye me-1'></i> " . trans('View') . "
                      </a>";
            })
            ->rawColumns(['order_number', 'amount', 'shipping_charge', 'email', 'store', 'gateway', 'user', 'status', 'action'])
            ->make(true);
    }

    public function orderView($orderNumber)
    {
        $order = ProductOrder::query()->where('status', 1)->where('order_number', $orderNumber)->firstOrFail();
        $data['orderDetails'] = ProductOrderDetail::with(['product', 'order'])->where('order_id', $order->id)->get();
        return view('admin.store.orderView', $data, compact('order'));
    }

    public function singleStageChange(Request $request, $orderId)
    {
        if ($request->stage == 'processing') {
            ProductOrder::where('id', $orderId)->update([
                'stage' => 1,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
        if ($request->stage == 'on-shipping') {
            ProductOrder::where('id', $orderId)->update([
                'stage' => 2,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
        if ($request->stage == 'out-for-delivery') {
            ProductOrder::where('id', $orderId)->update([
                'stage' => 3,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
        if ($request->stage == 'delivered') {
            ProductOrder::where('id', $orderId)->update([
                'stage' => 4,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
        if ($request->stage == 'cancel') {
            $productOrder = ProductOrder::where('id', $orderId)->firstOrFail();

            $productOrder->cancel_from = $productOrder->stage;
            $productOrder->save();

            ProductOrder::where('id', $orderId)->update([
                'stage' => 5,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
    }

    public function contactList()
    {
        $data['stores'] = Store::select(['id', 'name'])->get();
        return view('admin.store.contactList', $data);
    }

    public function contactListSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->name;
        $filterStore = $request->filterStore;
        $filterDate = explode('-', $request->filterDate);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $transfers = SellerContact::with(['user', 'store'])->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->where('sender_name', 'LIKE', '%' . $filterName . '%');
            })
            ->when(isset($filterStore), function ($query) use ($filterStore) {
                if ($filterStore != "all") {
                    return $query->where('store_id', $filterStore);
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
                    $subquery->where('sender_name', 'LIKE', "%{$search}%")
                        ->orWhereHas('user', function ($q) use ($search) {
                            $q->where('firstname', 'LIKE', "%$search%")
                                ->orWhere('lastname', 'LIKE', "%$search%")
                                ->orWhere('username', 'LIKE', "%$search%");
                        });
                });
            });
        return DataTables::of($transfers)
            ->addColumn('sender_name', function ($item) {
                return $item->sender_name;
            })
            ->addColumn('store', function ($item) {
                return optional($item->store)->name;
            })
            ->addColumn('message', function ($item) {
                return $item->message;
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
            ->addColumn('time', function ($item) {
                return dateTime($item->created_at, basicControl()->date_time_format);
            })
            ->rawColumns(['sender_name', 'store', 'message', 'user', 'time'])
            ->make(true);
    }
}
