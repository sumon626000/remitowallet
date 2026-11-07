<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\ProductAttrMap;
use App\Models\ProductStoreMap;
use App\Models\Store;
use App\Models\StoreCategory;
use App\Models\StoreProduct;
use App\Models\StoreProductAttr;
use App\Models\StoreProductImage;
use App\Traits\Notify;
use App\Traits\Upload;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class StoreProductController extends Controller
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

    public function productList(Request $request)
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
            ->own()
            ->get()
            ->toArray())->collapse();

        $search = trim((string) $request->input('search', ''));
        $filterName = $request->input('filter_name');
        $filterCategory = $request->input('filter_category');
        $filterStatus = $request->input('filter_status');
        $filterDate = explode('-', (string) $request->input('filter_date'));
        $startDate = $filterDate[0] ?? null;
        $endDate = isset($filterDate[1]) ? trim($filterDate[1]) : null;

        $perPage = (int) $request->input('per_page', 12);
        if (!in_array($perPage, [12, 24, 48, 96])) {
            $perPage = 12;
        }

        $data['productItems'] = StoreProduct::with(['category', 'user'])->own()->latest()
            ->when($filterName, fn($q) => $q->where('name', 'LIKE', '%' . $filterName . '%'))
            ->when(isset($filterStatus) && $filterStatus !== '' && $filterStatus !== 'all',
                fn($q) => $q->where('status', $filterStatus))
            ->when(isset($filterCategory) && $filterCategory !== '' && $filterCategory !== 'all',
                fn($q) => $q->where('category_id', $filterCategory))
            ->when(!empty($startDate) && $endDate === null, function ($q) use ($startDate) {
                $q->whereDate('created_at', Carbon::createFromFormat('d/m/Y', trim($startDate)));
            })
            ->when(!empty($startDate) && $endDate !== null, function ($q) use ($startDate, $endDate) {
                $q->whereBetween('created_at', [
                    Carbon::createFromFormat('d/m/Y', trim($startDate)),
                    Carbon::createFromFormat('d/m/Y', trim($endDate)),
                ]);
            })
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('price', 'LIKE', "%{$search}%")
                        ->orWhereHas('category', fn($c) => $c->where('name', 'LIKE', "%{$search}%"));
                });
            })
            ->paginate($perPage)
            ->withQueryString();

        $data['perPage'] = $perPage;

        return view('user.store.product.productList', $data);
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
                return '<td class="table-column-ps-0">
                  <a class="d-flex align-items-center" href="#">
                    <div class="flex-shrink-0"> <img class="avatar avatar-lg" src="'.$image.'" alt="..."> </div>
                    <div class="flex-grow-1 ms-3">
                      <h5 class="text-inherit mb-0">'.$item->name.'</h5>
                    </div>
                  </a>
                </td>';
            })
            ->addColumn('price', function ($item) {
                $amount = currencyPosition($item->price, $item->user?->sotre_currency_id);
                return '<span class="amount-highlight">'.$amount.'</span>';
            })
            ->addColumn('category', function ($item) {
                return optional($item->category)->name;
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
                $viewRoute = route('user.product.view', $item->id);
                $editRoute = route('user.product.edit', $item->id);
                $deleteRoute = route('user.product.delete', $item->id);

                return '
                <div class="btn-group" role="group">
                    <a href="' . $viewRoute . '" class="btn btn-white btn-sm" >
                        <i class="bi-eye me-1"></i> ' . trans("View") . '
                    </a>
                    <div class="btn-group">
                        <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown">
                           <a class="dropdown-item" href="' . $editRoute . '">
                                <i class="bi-pencil-square dropdown-item-icon"></i> ' . trans("Edit") . '
                            </a>
                           <a class="dropdown-item" href="#"
                                data-route="' . $deleteRoute . '"
                                data-bs-target="#productDelete" data-bs-toggle="modal">
                                <i class="bi-trash dropdown-item-icon"></i> ' . trans("Delete") . '
                            </a>
                        </div>
                    </div>
                </div>';

            })
            ->rawColumns(['product', 'price', 'category', 'status', 'action'])
            ->make(true);
    }

    public function productCreate(Request $request)
    {
        if ($request->method() == 'GET') {
            $data['stores'] = Store::own()->where('status', 1)->orderBy('name', 'asc')->get();
            $data['categories'] = StoreCategory::own()->where('status', 1)->orderBy('name', 'asc')->get();
            $data['productsAttrs'] = StoreProductAttr::own()->where('status', 1)->orderBy('name', 'asc')->get();
            return view('user.store.product.productCreate', $data);
        }
        if ($request->method() == 'POST') {
            $purifiedData = $request->all();
            $validator = Validator::make($purifiedData, [
                'store' => 'required',
                'category' => 'required',
                'name' => 'required',
                'price' => 'required|numeric',
                'sku' => 'required',
                'attribute' => 'required',
                'thumbnail' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            DB::beginTransaction();
            try {
                $storeProduct = new StoreProduct();
                $storeProduct->user_id = Auth::id();
                $storeProduct->category_id = $purifiedData['category'];
                $storeProduct->name = $purifiedData['name'];
                $storeProduct->price = $purifiedData['price'];
                $storeProduct->sku = $purifiedData['sku'];
                $storeProduct->tag = $purifiedData['tag'];
                $storeProduct->status = $purifiedData['status'];
                $storeProduct->description = $purifiedData['description'];
                $storeProduct->instruction = $purifiedData['instruction'];
                if ($request->thumbnail) {
                    $thumbnail = $this->fileUpload($request->thumbnail, config('filelocation.product.path'), null, config('filelocation.product.thumbnail'), 'webp');
                    $storeProduct->thumbnail = $thumbnail['path'];
                    $storeProduct->driver = $thumbnail['driver'];
                }
                $storeProduct->save();

                if ($request->image) {
                    for ($i = 0; $i < count($request->image); $i++) {
                        $storeImage = new StoreProductImage();
                        $storeImage->product_id = $storeProduct->id;
                        $image = $this->fileUpload($request->image[$i], config('filelocation.product.path'), null, config('filelocation.product.size'), 'webp');
                        $storeImage->image = $image['path'];
                        $storeImage->driver = $image['driver'];
                        $storeImage->save();
                    }
                }
                if ($request->store) {
                    for ($i = 0; $i < count($request->store); $i++) {
                        $productStoreMap = new ProductStoreMap();
                        $productStoreMap->product_id = $storeProduct->id;
                        $productStoreMap->store_id = $request->store[$i];
                        $productStoreMap->save();
                    }
                }
                if ($request->attribute) {
                    for ($i = 0; $i < count($request->attribute); $i++) {
                        $productAtrMap = new ProductAttrMap();
                        $productAtrMap->product_id = $storeProduct->id;
                        $productAtrMap->attributes_id = $request->attribute[$i];
                        $productAtrMap->save();
                    }
                }
                DB::commit();
                return back()->with('success', 'Product has been created');

            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'something went wrong');
            }
        }
    }

    public function productView($id)
    {
        $data['stores'] = Store::own()->where('status', 1)->orderBy('name', 'asc')->get();
        $data['categories'] = StoreCategory::own()->where('status', 1)->orderBy('name', 'asc')->get();
        $data['productsAttrs'] = StoreProductAttr::own()->where('status', 1)->orderBy('name', 'asc')->get();
        $data['product'] = StoreProduct::own()->with(['productImages', 'productStores.store', 'productAttrs.attribute'])->findOrFail($id);
        return view('user.store.product.productView', $data);
    }

    public function productEdit($id, Request $request)
    {
        $data['product'] = StoreProduct::own()->with(['productImages', 'productStores.store', 'productAttrs.attribute'])->findOrFail($id);

        if ($request->method() == 'GET') {
            $data['stores'] = Store::own()->where('status', 1)->orderBy('name', 'asc')->get();
            $data['categories'] = StoreCategory::own()->where('status', 1)->orderBy('name', 'asc')->get();
            $data['productsAttrs'] = StoreProductAttr::own()->where('status', 1)->orderBy('name', 'asc')->get();
            return view('user.store.product.productEdit', $data);
        }
        if ($request->method() == 'POST') {
            $purifiedData = $request->all();
            $validator = Validator::make($purifiedData, [
                'store' => 'required',
                'category' => 'required',
                'name' => 'required',
                'price' => 'required|numeric',
                'sku' => 'required',
                'attribute' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $data['product']->category_id = $purifiedData['category'];
            $data['product']->name = $purifiedData['name'];
            $data['product']->price = $purifiedData['price'];
            $data['product']->sku = $purifiedData['sku'];
            $data['product']->tag = $purifiedData['tag'];
            $data['product']->status = $purifiedData['status'];
            $data['product']->description = $purifiedData['description'];
            $data['product']->instruction = $purifiedData['instruction'];
            if ($request->thumbnail) {
                $thumbnail = $this->fileUpload($request->thumbnail, config('filelocation.product.path'),
                    null, config('filelocation.product.thumbnail'), 'webp', 80,
                    $data['product']->thumbnail, $data['product']->driver);

                $data['product']->thumbnail = $thumbnail['path'];
                $data['product']->driver = $thumbnail['driver'];
            }
            $data['product']->save();

            if ($request->image) {
                for ($i = 0; $i < count($request->image); $i++) {
                    $storeImage = new StoreProductImage();
                    $storeImage->product_id = $data['product']->id;
                    $image = $this->fileUpload($request->image[$i], config('filelocation.product.path'), null, config('filelocation.product.size'),
                        'webp', 80 , $storeImage->image, $storeImage->driver);
                    $storeImage->image = $image['path'];
                    $storeImage->driver = $image['driver'];
                    $storeImage->save();
                }
            }
            if ($request->store) {
                $previousStore = ProductStoreMap::where('product_id', $data['product']->id)->get();
                foreach ($previousStore as $item) {
                    $item->delete();
                }
                for ($i = 0; $i < count($request->store); $i++) {
                    $productStoreMap = new ProductStoreMap();
                    $productStoreMap->product_id = $data['product']->id;
                    $productStoreMap->store_id = $request->store[$i];
                    $productStoreMap->save();
                }
            }
            if ($request->attribute) {
                $previousAttr = ProductAttrMap::where('product_id', $data['product']->id)->get();
                foreach ($previousAttr as $item) {
                    $item->delete();
                }
                for ($i = 0; $i < count($request->attribute); $i++) {
                    $productAtrMap = new ProductAttrMap();
                    $productAtrMap->product_id = $data['product']->id;
                    $productAtrMap->attributes_id = $request->attribute[$i];
                    $productAtrMap->save();
                }
            }

            return back()->with('success', 'Product Updated Successfully');
        }
    }

    public function productImageDelete($id)
    {
        $productImage = StoreProductImage::findOrFail($id);
        $this->fileDelete($productImage->driver, $productImage->image);
        $productImage->delete();
        return back()->with('success', 'Product image has been deleted');
    }

    public function productDelete($id)
    {
        $product = StoreProduct::with(['orderDetails'])->findOrFail($id);
        if (count($product->orderDetails) > 0) {
            return back()->with('error', 'Product has lot of orders');
        }

        $previousStore = ProductStoreMap::where('product_id', $id)->get();
        foreach ($previousStore as $item) {
            $item->delete();
        }
        $previousAttr = ProductAttrMap::where('product_id', $id)->get();
        foreach ($previousAttr as $item) {
            $item->delete();
        }
        $productImage = StoreProductImage::where('product_id', $id)->get();
        if (!empty($productImage)) {
            foreach ($productImage as $image) {
                $this->fileDelete($image->driver, $image->image);
                $image->delete();
            }
        }
        $product->delete();
        return back()->with('success', 'Product Delete Successfully');
    }
}