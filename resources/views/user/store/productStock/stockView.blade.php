@extends(userLayout())
@section('title',__('Product Stock View'))

@section('content')
    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Product Stock')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Product Stock View')</h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Product Stock View')</h4>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="d-flex align-items-center mb-5">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-lg avatar-circle">
                                        <img class="avatar-img" src="{{getFile($product->driver,$product->thumbnail)}}" alt="...">
                                    </div>
                                </div>

                                <div class="flex-grow-1 mx-3">
                                    <div class="d-flex mb-1">
                                        <h3 class="mb-0 me-3">{{$product->name}}</h3>
                                    </div>

                                    <span class="fs-6">{{$product->sku}}</span>
                                </div>
                            </div>
                        </div>
                        @forelse($productStocks as $stock)
                            <div class="row mt-3">
                                @forelse($stock->product_attributes as $item)
                                    <div class="col-md-4">
                                        <label class="form-label">{{optional($item->attrName)->name}}</label>
                                        <select class="form-control">
                                            <option>{{$item->name}}</option>
                                        </select>
                                    </div>
                                @empty
                                @endforelse
                                <div class="col-md-4">
                                    <label class="form-label">@lang('Quantity')</label>
                                    <input type="text" value="{{$stock->quantity}}" class="form-control" readonly>
                                </div>
                            </div>
                        @empty
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        (function () {
            HSCore.components.HSTomSelect.init('.js-select')
        })();
    </script>
@endpush

