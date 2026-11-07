@extends(userLayout())
@section('title',__('Order Details'))

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Order Details')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Order-') {{$order->order_number}}</h1>
                    <span class="badge bg-soft-success text-success ms-sm-3">
                        <span class="legend-indicator bg-success"></span>@lang('Paid')
                    </span>
                    @if($order->stage == null)
                        <span class="badge bg-soft-warning text-warning ms-2 ms-sm-3">
                          <span class="legend-indicator bg-warning"></span>@lang('New Arrival')
                        </span>
                    @elseif($order->stage == 1)
                        <span class="badge bg-soft-info text-info ms-2 ms-sm-3">
                          <span class="legend-indicator bg-info"></span>@lang('Processing')
                        </span>
                    @elseif($order->stage == 2)
                        <span class="badge bg-soft-dark text-dark ms-2 ms-sm-3">
                          <span class="legend-indicator bg-dark"></span>@lang('On Shipping')
                        </span>
                    @elseif($order->stage == 3)
                        <span class="badge bg-soft-primary text-primary ms-2 ms-sm-3">
                          <span class="legend-indicator bg-primary"></span>@lang('Out For Delivery')
                        </span>
                    @elseif($order->stage == 4)
                        <span class="badge bg-soft-success text-success ms-2 ms-sm-3">
                          <span class="legend-indicator bg-success"></span>@lang('Delivered')
                        </span>
                    @elseif($order->stage == 5)
                        <span class="badge bg-soft-danger text-danger ms-2 ms-sm-3">
                          <span class="legend-indicator bg-danger"></span>@lang('Cancel')
                        </span>
                    @endif
                    <span class="ms-2 ms-sm-3">
                <i class="bi-calendar-week"></i> {{dateTime($order->created_at)}}
              </span>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Order Details') <span
                                class="badge bg-soft-dark text-dark rounded-circle ms-1">{{count($orderDetails)}}</span>
                        </h4>
                        <div class="dropdown">
                            <a class="text-body" href="javascript:;" id="moreOptionsDropdown"
                               data-bs-toggle="dropdown" aria-expanded="false">
                                @lang('More options') <i class="bi-chevron-down"></i>
                            </a>

                            <div class="dropdown-menu mt-1" aria-labelledby="moreOptionsDropdown">
                                <a class="dropdown-item processing" data-bs-toggle="modal"
                                   data-bs-target="#stageChange" href="javascript:void(0)">
                                    <i class="fa-light fa-spinner dropdown-item-icon"></i> @lang('Processing')
                                </a>
                                <a class="dropdown-item on-shipping" data-bs-toggle="modal"
                                   data-bs-target="#stageChange" href="javascript:void(0)">
                                    <i class="fa-light fa-shipping-fast dropdown-item-icon"></i> @lang('On Shipping')
                                </a>
                                <a class="dropdown-item out-for-delivery" data-bs-toggle="modal"
                                   data-bs-target="#stageChange" href="javascript:void(0)">
                                    <i class="fa-light fa-dolly-flatbed dropdown-item-icon"></i> @lang('Out For Delivery')
                                </a>
                                <a class="dropdown-item delivered" data-bs-toggle="modal"
                                   data-bs-target="#stageChange" href="javascript:void(0)">
                                    <i class="fa-light fa-handshake dropdown-item-icon"></i> @lang('Delivered')
                                </a>
                                <a class="dropdown-item cancel" data-bs-toggle="modal"
                                   data-bs-target="#stageChange" href="javascript:void(0)">
                                    <i class="fa-light fa-undo dropdown-item-icon"></i> @lang('Cancel')
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="card-body">
                        @foreach($orderDetails as $key => $item)
                            <!-- Media -->

                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    <div class="avatar avatar-xl">
                                        <img class="img-fluid"
                                             src="{{getFile(optional($item->product)->driver,optional($item->product)->thumbnail)}}"
                                             alt="...">
                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <div class="row">
                                        <div class="col-md-6 mb-3 mb-md-0">
                                            <a class="h5 d-block" href="javascript:;">{{ optional($item->product)->name }}</a>
                                            <div class="fs-6 text-body">
                                                <span>@lang('SKU:')</span>
                                                <span class="fw-semibold">{{ optional($item->product)->sku }}</span>
                                            </div>
                                            @foreach($item->attr ?? [] as $attr)
                                                <div class="fs-6 text-body">
                                                    <span>{{optional($attr->attrName)->name}}</span>
                                                    <span class="fw-semibold">{{$attr->name}}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                        <div class="col col-md-2 align-self-center">
                                            <h5>{{optional(optional(optional($item->order)->user)->storeCurrency)->symbol}}{{ getAmount($item->price,2) }}</h5>
                                        </div>
                                        <div class="col col-md-2 align-self-center">
                                            <h5>{{ $item->quantity }}</h5>
                                        </div>
                                        <div class="col col-md-2 align-self-center text-end">
                                            <h5>{{optional(optional(optional($item->order)->user)->storeCurrency)->symbol}}{{getAmount($item->price * $item->quantity,2)}}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <hr>
                        @endforeach

                        <div class="row justify-content-md-end mb-3">
                            <div class="col-md-8 col-lg-7">
                                <dl class="row text-sm-end">
                                    <dt class="col-sm-6">@lang('Amount paid:')</dt>
                                    <dd class="col-sm-6">{{optional($item->order->user->storeCurrency)->symbol}}{{ getAmount($item->total_price,2) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mb-3 mb-lg-0">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Customers')</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label for="vendorLabel" class="form-label">{{ __('Full Name') }}</label>
                            <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                   placeholder="eg. Nike" aria-label="eg. Nike" value="{{$order->fullname??'N/A'}}">
                        </div>
                        <div class="mb-4">
                            <label for="vendorLabel" class="form-label">{{ __('Email') }}</label>
                            <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                   placeholder="eg. Nike" aria-label="eg. Nike" value="{{$order->email??'N/A'}}">
                        </div>
                        <div class="mb-4">
                            <label for="vendorLabel" class="form-label">{{ __('Phone') }}</label>
                            <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                   placeholder="eg. Nike" aria-label="eg. Nike" value="{{$order->phone??'N/A'}}">
                        </div>
                        <div class="mb-4">
                            <label for="vendorLabel" class="form-label">{{ __('Alt. Phone') }}</label>
                            <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                   placeholder="eg. Nike" aria-label="eg. Nike"
                                   value="{{$order->alt_phone??'N/A'}}">
                        </div>
                        @if($order->shipping_id )
                            <div class="mb-4">
                                <label for="vendorLabel" class="form-label">{{ __('Shipping Address') }}</label>
                                <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                       placeholder="eg. Nike" aria-label="eg. Nike"
                                       value="{{optional($order->shipping)->address??'N/A'}}">
                            </div>
                        @endif
                        <div class="mb-4">
                            <label for="vendorLabel" class="form-label">{{ __('Details Address') }}</label>
                            <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                   placeholder="eg. Nike" aria-label="eg. Nike"
                                   value="{{$order->detailed_address??'N/A'}}">
                        </div>
                        <div class="mb-4">
                            <label for="vendorLabel" class="form-label">{{ __('Amount') }}</label>
                            <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                   placeholder="eg. Nike" aria-label="eg. Nike"
                                   value="{{getAmount($order->total_amount,2)}} {{optional($order->user->storeCurrency)->code}}">
                        </div>
                        @if($order->shipping_charge > 0)
                            <div class="mb-4">
                                <label for="vendorLabel" class="form-label">{{ __('Shipping Charge') }}</label>
                                <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                       placeholder="eg. Nike" aria-label="eg. Nike"
                                       value="{{getAmount($order->shipping_charge,2)}} {{optional($order->user->storeCurrency)->code}}">
                            </div>
                        @endif
                        <div class="mb-4">
                            <label for="vendorLabel" class="form-label">{{ __('Total Amount') }}</label>
                            <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                   placeholder="eg. Nike" aria-label="eg. Nike"
                                   value="{{getAmount($order->total_amount+$order->shipping_charge,2)}} {{optional($order->user->storeCurrency)->code}}">
                        </div>
                        <div class="mb-4">
                            <label for="vendorLabel" class="form-label">{{ __('Order Note') }}</label>
                            <input type="text" class="form-control" name="vendor" id="vendorLabel"
                                   placeholder="eg. Nike" aria-label="eg. Nike"
                                   value="{{$order->order_note??'N/A'}}">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="stageChange" data-bs-backdrop="static" tabindex="-1" role="dialog"
         aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalHeader">@lang('Stage Change Confirmation')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteModalBody">@lang('Are you want to change those orders stage?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <form action="{{route('user.single.stage.change',$order->id)}}" method="post">
                        @csrf
                        <input type="hidden" name="stage" class="stage" value="">
                        <button type="submit" class="btn btn-soft-primary">@lang('Yes')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-lib')
@endpush

@push('js-lib')
@endpush

@push('script')
    <script>
        'use strict';
        $(document).on('click', '.processing', function () {
            $('.stage').val('processing');
        });
        $(document).on('click', '.on-shipping', function () {
            $('.stage').val('on-shipping');
        });
        $(document).on('click', '.out-for-delivery', function () {
            $('.stage').val('out-for-delivery');
        });
        $(document).on('click', '.delivered', function () {
            $('.stage').val('delivered');
        });
        $(document).on('click', '.cancel', function () {
            $('.stage').val('cancel');
        });

    </script>
@endpush

