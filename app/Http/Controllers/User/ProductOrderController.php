<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Gateway;
use App\Models\ProductOrder;
use App\Models\ProductOrderDetail;
use App\Models\Store;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class ProductOrderController extends Controller
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
            ->where('user_id', auth()->id())
            ->get()
            ->toArray())->collapse();


        return view('user.store.orderList', $data);
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


        $orders = ProductOrder::with(['store', 'store.user.storeCurrency'])
            ->where('user_id', auth()->id())->where('status', 1)->latest()
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
                        ->orWhere('email', 'LIKE', "%{$search}%");
                });
            });

        return DataTables::of($orders)
            ->addColumn('checkbox', function ($item) {
                return '<input type="checkbox" id="chk-' . $item->id . '"
                           class="form-check-input row-tic tic-check" name="check" value="' . $item->id . '"
                           data-id="' . $item->id . '">';
            })
            ->addColumn('order_number', function ($item) {
                return $item->order_number;
            })
            ->addColumn('amount', function ($item) {
                $amount = currencyPosition($item->total_amount, $item->store?->user?->store_currency_id);
                return '<span class="amount-highlight">'.$amount.'</span>';
            })
            ->addColumn('shipping_charge', function ($item) {
                $amount = currencyPosition($item->shipping_charge, $item->store?->user?->store_currency_id);
                return '<span class="text-danger">'.$amount.'</span>';
            })
            ->addColumn('email', function ($item) {
                return $item->email;
            })
            ->addColumn('store', function ($item) {
                $image = getFile(optional($item->store)->driver, optional($item->store)->image);
                return
                '<a class="d-flex align-items-center me-2" href="javascript:void(0)">
                    <div class="flex-grow-1 ">
                        <h5 class="text-hover-primary mb-0">' . optional($item->store)->name . '</h5>
                    </div>
                </a>';
            })
            ->addColumn('gateway', function ($item) {
                return optional($item->gateway)->name;
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
            ->addColumn('order_at', function ($item) {
                return dateTime($item->created_at);
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('user.order.view', $item->order_number);

                return "<div class='btn-group' role='group'>
                      <a href='" . $viewRoute . "' class='btn btn-soft-primary btn-xs'>
                        <i class='bi-eye me-1'></i> " . trans('View') . "
                      </a>";

            })
            ->rawColumns(['checkbox', 'order_number', 'amount', 'shipping_charge', 'email', 'store', 'gateway', 'status', 'order_at', 'action'])
            ->make(true);
    }

    public function orderView($orderNumber)
    {
        $order = ProductOrder::where('status', 1)->where('user_id', auth()->id())->where('order_number', $orderNumber)->firstOrFail();
        $data['orderDetails'] = ProductOrderDetail::with(['product'])->where('order_id', $order->id)->get();
        return view('user.store.orderView', $data, compact('order'));
    }

    public function stageChange(Request $request)
    {
        $user_id = auth()->id();
        if ($request->strIds == null) {
            session()->flash('error', 'You do not select ID.');
            return response()->json(['error' => 1]);
        } else {
            if ($request->stage == 'processing') {
                ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
                    'stage' => 1,
                ]);
                session()->flash('success', 'Stage Has Been Updated');
                return response()->json(['success' => 1]);
            }
            if ($request->stage == 'on-shipping') {
                ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
                    'stage' => 2,
                ]);
                session()->flash('success', 'Stage Has Been Updated');
                return response()->json(['success' => 1]);
            }
            if ($request->stage == 'out-for-delivery') {
                ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
                    'stage' => 3,
                ]);
                session()->flash('success', 'Stage Has Been Updated');
                return response()->json(['success' => 1]);
            }
            if ($request->stage == 'delivered') {
                ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
                    'stage' => 4,
                ]);
                session()->flash('success', 'Stage Has Been Updated');
                return response()->json(['success' => 1]);
            }
            if ($request->stage == 'cancel') {
                $productOrders = ProductOrder::where('user_id', $user_id)->where('user_id')->whereIn('id', $request->strIds)->get();
                foreach ($productOrders as $productOrder) {
                    $productOrder->cancel_from = $productOrder->stage;
                    $productOrder->save();
                }
                ProductOrder::where('user_id', $user_id)->whereIn('id', $request->strIds)->update([
                    'stage' => 5,
                ]);
                session()->flash('success', 'Stage Has Been Updated');
                return response()->json(['success' => 1]);
            }
        }
    }

    public function singleStageChange(Request $request, $orderId)
    {
        $user_id = auth()->id();
        if ($request->stage == 'processing') {
            ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
                'stage' => 1,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
        if ($request->stage == 'on-shipping') {
            ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
                'stage' => 2,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
        if ($request->stage == 'out-for-delivery') {
            ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
                'stage' => 3,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
        if ($request->stage == 'delivered') {
            ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
                'stage' => 4,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
        if ($request->stage == 'cancel') {
            $productOrder = ProductOrder::where('user_id', $user_id)->where('id', $orderId)->firstOrFail();

            $productOrder->cancel_from = $productOrder->stage;
            $productOrder->save();

            ProductOrder::where('user_id', $user_id)->where('id', $orderId)->update([
                'stage' => 5,
            ]);
            session()->flash('success', 'Stage Has Been Updated');
            return back();
        }
    }
}
