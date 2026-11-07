@extends('admin.layouts.app')
@section('page_title',__('Store Details'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0);">@lang('Dashboard')</a></li>
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0);">@lang('Store Details')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@yield('page_title')</h1>
                </div>
            </div>
        </div>
        <div class="content container-fluid">
            <div class="row justify-content-lg-center">
                <div class="col-lg-12">
                    <div class="d-grid gap-3 gap-lg-5">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mt-2">@lang("Store Details")</h4>
                            </div>
                            <div class="card-body mt-2">
                                <form>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="StoreName">@lang('Store Name')</label>
                                                <input type="text" class="form-control" name="plan_name"
                                                       value="{{$store->name}}"
                                                       id="StoreName"
                                                       autocomplete="off">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="StoreLink">@lang('Store Link')</label>
                                                <input type="text" class="form-control" name="plan_name"
                                                       value="{{url('/')}}"
                                                       id="StoreLink"
                                                       autocomplete="off">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">@lang('Store Image')</label>
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    <label class="form-check form-check-dashed" for="logoUploader">
                                                        <img id="otherImg"
                                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                             src="{{ getFile($store->driver,$store->image, true) }}"
                                                             alt="@lang("File Storage Logo")"
                                                             data-hs-theme-appearance="default">

                                                        <img id="otherImg"
                                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                             src="{{ getFile($store->driver,$store->image, true) }}"
                                                             alt="@lang("File Storage Logo")"
                                                             data-hs-theme-appearance="dark">
                                                    </label>
                                                </div>

                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="ShortDescription">@lang('Short Description')</label>
                                                <textarea class="form-control"
                                                          name="description"
                                                          readonly>{{$store->short_description}}</textarea>
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <label class="row form-check form-switch my-4"
                                                   for="ShippingCharge">
                                            <span class="col-8 col-sm-9 ms-0">
                                              <span class="d-block text-dark">@lang("Shipping Charge")</span>
                                              <span
                                                  class="d-block fs-5">@lang("The store offers buyers free shipping, eliminating delivery charges on eligible orders for added convenience and savings.")</span>
                                            </span>
                                                <span class="col-4 col-sm-3 text-end">
                                                    <input type="hidden" value="0" name="real_time_assistant"/>
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox" name="real_time_assistant"
                                                        id="ShippingCharge" value="1" {{$store->shipping_charge == '1' ? 'checked':''}}>
                                                </span>
                                            </label>

                                            <label class="row form-check form-switch my-4"
                                                   for="DeliveryNote">
                                            <span class="col-8 col-sm-9 ms-0">
                                              <span class="d-block text-dark">@lang("Delivery Note")</span>
                                              <span
                                                  class="d-block fs-5">@lang("The store offers buyers to delivery note when buyers place orders.")</span>
                                            </span>
                                                <span class="col-4 col-sm-3 text-end">
                                                    <input type="hidden" value="0" name="itinerary_planning"/>
                                                    <input
                                                        class="form-check-input @error('itinerary_planning') is-invalid @enderror"
                                                        type="checkbox" name="itinerary_planning"
                                                        id="DeliveryNote" value="1" {{$store->delivery_note == 'enable' ? 'checked':''}}>
                                                </span>
                                            </label>

                                            <label class="row form-check form-switch my-4"
                                                   for="Status">
                                            <span class="col-8 col-sm-9 ms-0">
                                              <span class="d-block text-dark">@lang("Status")</span>
                                              <span
                                                  class="d-block fs-5">@lang("The store visibility of the buyers and buyers have access to place orders.")</span>
                                            </span>
                                                <span class="col-4 col-sm-3 text-end">
                                                    <input type="hidden" value="0" name="itinerary_planning"/>
                                                    <input
                                                        class="form-check-input @error('itinerary_planning') is-invalid @enderror"
                                                        type="checkbox" name="itinerary_planning"
                                                        id="Status" value="1" {{$store->checked == '1' ? 'checked':''}}>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
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
        (function () {
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 500
            })
        })();
    </script>
@endpush
