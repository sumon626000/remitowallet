<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductAttrList;
use App\Models\ProductAttrMap;
use App\Models\StoreProduct;
use App\Models\StoreProductStock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Stevebauman\Purify\Facades\Purify;
use Yajra\DataTables\Facades\DataTables;

class StoreProductStockController extends Controller
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

    public function stockList()
    {
        $data['productStocks'] = StoreProductStock::own()->with(['product'])->latest()->groupBy('product_id')->get()->map(function ($item) {
            $q = StoreProductStock::where('product_id', $item->product_id)->sum('quantity');
            $item['sumQuantity'] = $q;
            return $item;
        });

        return view('user.store.productStock.stockList', $data);
    }

    public function stockListSearch(Request $request)
    {
        $search = $request->search['value'] ?? null;
        $filterName = $request->filter_name;
        $filterDate = explode('-', $request->filter_date);
        $startDate = $filterDate[0];
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $products = StoreProductStock::own()->with(['product'])->has('product')->latest()
            ->when(isset($filterName), function ($query) use ($filterName) {
                return $query->whereHas('product',function ($qq) use ($filterName){
                    $qq->where('name', 'LIKE', '%' . $filterName . '%');
                });
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
                return $query->whereHas('product',function ($qq) use ($search){
                    $qq->where('name', 'LIKE', '%' . $search . '%');
                });
            })->groupBy('product_id')->get()->map(function ($item) {
                $q = StoreProductStock::where('product_id', $item->product_id)->sum('quantity');
                $item['sumQuantity'] = $q;
                return $item;
            });

        return DataTables::of($products)
            ->addColumn('sl', function ($item) {
                static $count = 0;
                return ++$count;
            })
            ->addColumn('name', function ($item) {
                $link = route('user.product.edit',$item->product_id);
                $image = getFile($item->product?->driver, $item->product?->thumbnail);
                return '<td class="table-column-ps-0">
                  <a class="d-flex align-items-center" href="'.$link.'">
                    <div class="flex-shrink-0">
                      <img class="avatar avatar-lg" src="'.$image.'" alt="Image Description">
                    </div>
                    <div class="flex-grow-1 ms-3">
                      <h5 class="text-inherit mb-0">'.$item->product?->name.'</h5>
                    </div>
                  </a>
                </td>';
            })
            ->addColumn('quantity', function ($item) {
                return '<span class="text-dark badge bg-soft-dark">' . number_format($item->sumQuantity) . '</span>';
            })
            ->addColumn('date', function ($item) {
                return dateTime($item->created_at);
            })
            ->addColumn('action', function ($item) {
                $viewRoute = route('user.stock.view',$item->product_id);
                $deleteRoute = route('user.attr.delete',$item->id);

                return '
                <div class="btn-group" role="group">
                    <a href="' . $viewRoute . '" class="btn btn-white btn-sm" >
                        <i class="bi-eye me-1"></i> ' . trans("View") . '
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                           <a class="dropdown-item delete_btn" href="#"
                                data-route="' . $deleteRoute . '"
                                data-bs-target="#attrDelete" data-bs-toggle="modal">
                                <i class="bi-trash dropdown-item-icon"></i> ' . trans("Delete") . '
                            </a>
                        </div>
                    </div>
                </div>';

            })
            ->rawColumns(['sl', 'name', 'quantity', 'action'])
            ->make(true);
    }

    public function stockCreate(Request $request)
    {
        if ($request->method() == "GET") {
            $data['products'] = StoreProduct::own()->where('status', 1)->get();
            return view('user.store.productStock.stockCreate', $data);
        }
        if ($request->method() == "POST") {
           
            $purifiedData = $request->all();
            $validator = Validator::make($purifiedData, [
                'product' => 'required',
                'attrName.*' => 'required',
                'quantity.*' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            if(isset($request->quantity) && count($request->quantity) > 0){
             for ($i = 0; $i < count($request->quantity); $i++) {
                $data = [
                    'user_id' => Auth::id(),
                    'product_id' => $request->product,
                    'product_attr_lists_id' => $request->attrName[$i],
                    'quantity' => $request->quantity[$i],
                ];

                $checkExit = StoreProductStock::where('product_id', $request->product)
                    ->where(function ($query) use ($request, $i) {
                        foreach ($request->attrName[$i] as $key => $id) {
                            $query->whereJsonContains('product_attr_lists_id', $id);
                        }
                    })->exists();
                if ($checkExit == false) {
                    if ($request->quantity[$i] < 0) {
                        return back()->with('error', 'Quantity must be grater than 0');
                    }
                    StoreProductStock::create($data);
                } else {
                    $newStock = StoreProductStock::where('product_id', $request->product)
                        ->where(function ($query) use ($request, $i) {
                            foreach ($request->attrName[$i] as $key => $id) {
                                $query->whereJsonContains('product_attr_lists_id', $id);
                            }
                        })->firstOrFail();
                    $newQty = $newStock->quantity + $request->quantity[$i];
                    if ($newQty < 0) {
                        return back()->with('error', 'Quantity must be grater than 0');
                    }
                    $newStock->quantity = $newQty;
                    $newStock->save();
                }
            }
            return redirect()->route('user.stock.list')->with('success', 'Stock Added');   
            }
            
            return back()->with('error','Please add quatity');
        }
    }

    public function stockAttrFetch(Request $request)
    {
        $productAttr = ProductAttrMap::with(['attribute.attrLists'])->where('product_id', $request->productId)->get();
        $dynamicForm = '';
        foreach ($productAttr as $k => $item) {
            $fieldLabel = optional($item->attribute)->name;
            $fieldName = Str::snake(optional($item->attribute)->name);
            $variants = optional($item->attribute)->attrLists ?? [];

            $options = '';
            if (!empty($variants)) {
                foreach ($variants as $variant) {
                    $options .= '<option value="' . $variant->id . '">' . trans($variant->name) . '</option>';

                }
            }

            $dynamicForm .= '<div class="col-md-4 form-group">
                                <label class="form-label">' . $fieldLabel . '</label>
                                <select name="attrName[0][]" class="form-control attrId" required>
                                    ' . $options . '
                                </select>
                            </div>';
        }


        $html = '<div class="col-md-12 column-form mt-3">
					 <div class="card card-primary shadow">
							<div class="card-header d-flex justify-content-between align-items-center">
								<h4 class="card-header-title">' . trans('Field information') . '</h4>
								<div class="d-flex justify-content-between">
								    <button  class="btn btn-primary btn-icon btn-sm copyFormData " type="button">
										<i class="fas fa-copy"></i>
									</button>
									<button  class="btn btn-danger btn-icon btn-sm removeContentDiv" style="display: none" type="button">
										<i class="bi-x-lg"></i>
									</button>
                                </div>
							</div>

							<div class="card-body">
								<div class="row">
									' . $dynamicForm . '
									<div class="col-md-4 form-group">
										<label class="form-label">' . trans('Quantity') . '</label>
										<input name="quantity[]" class="form-control quantity" type="number" value=""
										required placeholder="Quantity">
									</div>
								</div>
							</div>

						</div>
				</div>';

        return response()->json([
            'status' => 'success',
            'data' => $html
        ]);
    }

    public function stockEdit(Request $request, $productId)
    {
        $productAttrList = collect();
        $data['product'] = StoreProduct::findOrFail($productId);
        $data['productStocks'] = StoreProductStock::own()->where('product_id', $productId)->get()->map(function ($item) use ($productAttrList) {
            $check = ProductAttrList::with(['attrName'])->whereIn('id', $item->product_attr_lists_id)->get();
            $productAttrList->push($check);

            $res['user_id'] = $item->user_id;
            $res['product_id'] = $item->product_id;
            $res['product_attr_lists_id'] = $item->product_attr_lists_id;
            $res['quantity'] = $item->quantity;
            $res['product_attributes'] = $check;
            return (object)$res;
        });


        return view('user.store.productStock.stockView', $data);
    }

}
