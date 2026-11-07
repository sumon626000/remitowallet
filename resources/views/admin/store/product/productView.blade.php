@extends('admin.layouts.app')
@section('page_title',__('Product Details'))
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
                                                           href="javascript:void(0);">@lang('Product Details')</a>
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
                                <h4 class="card-title mt-2">@lang("Product Details")</h4>
                            </div>
                            <div class="card-body mt-2">
                                <form>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="StoreName">@lang('Store')</label>
                                                <div class="tom-select-custom tom-select-custom-with-tags">
                                                    <select class="js-select form-select" autocomplete="off" multiple>
                                                        @forelse($product->productStores as $productStore)
                                                            <option
                                                                selected>{{optional($productStore->store)->name}}</option>
                                                        @empty
                                                        @endforelse
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="Category">@lang('Category')</label>
                                                <input type="text" class="form-control" name="plan_name"
                                                       value="{{optional($product->category)->name}}"
                                                       id="Category"
                                                       autocomplete="off">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="ProductName">@lang('Product Name')</label>
                                                <input type="text" class="form-control" name="plan_name"
                                                       value="{{$product->name}}"
                                                       id="ProductName"
                                                       autocomplete="off">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="Price">@lang('Price')</label>
                                                <div class="input-group mb-3">
                                                    <input type="text" class="form-control" name="plan_name"
                                                           value="{{$product->price}}"
                                                           id="Price"
                                                           autocomplete="off">
                                                    <span class="input-group-text"
                                                          id="basic-addon2">{{optional(optional($product->user)->storeCurrency)->code}}</span>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="StoreName">@lang('Attribute Name')</label>
                                                <div class="tom-select-custom tom-select-custom-with-tags">
                                                    <select class="js-select form-select" autocomplete="off" multiple>
                                                        @foreach($product->productAttrs as $productAttr)
                                                            <option
                                                                selected>{{optional($productAttr->attribute)->name}}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="SKU">@lang('SKU')</label>
                                                <input type="text" class="form-control" name="plan_name"
                                                       value="{{$product->sku}}"
                                                       id="SKU"
                                                       autocomplete="off">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="Tag">@lang('Tag')</label>
                                                <input type="text" class="form-control" name="plan_name"
                                                       value="{{$product->tag}}"
                                                       id="Tag"
                                                       autocomplete="off">
                                            </div>

                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">@lang('Thumbnail Image')</label>
                                                <div class="col-md-4 mb-3 mb-md-0">
                                                    <label class="form-check form-check-dashed" for="logoUploader">
                                                        <img id="otherImg"
                                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                             src="{{ getFile($product->driver,$product->thumbnail, true) }}"
                                                             alt="@lang("File Storage Logo")"
                                                             data-hs-theme-appearance="default">

                                                        <img id="otherImg"
                                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                             src="{{ getFile($product->driver,$product->thumbnail, true) }}"
                                                             alt="@lang("File Storage Logo")"
                                                             data-hs-theme-appearance="dark">
                                                    </label>
                                                </div>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="ShortDescription">@lang('Description')</label>
                                                <textarea class="form-control" rows="7"
                                                          name="description"
                                                          readonly>{{$product->description}}</textarea>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label"
                                                       for="ShortDescription">@lang('Instruction')</label>
                                                <textarea class="form-control" rows="7"
                                                          name="description"
                                                          readonly>{{$product->instruction}}</textarea>
                                            </div>


                                            <label class="row form-check form-switch my-4"
                                                   for="Status">
                                            <span class="col-8 col-sm-9 ms-0">
                                              <span class="d-block text-dark">@lang("Status")</span>
                                              <span
                                                  class="d-block fs-5">@lang("The product visibility of the buyers and buyers have access to place order the product.")</span>
                                            </span>
                                                <span class="col-4 col-sm-3 text-end">
                                                    <input type="hidden" value="0" name="itinerary_planning"/>
                                                    <input
                                                        class="form-check-input @error('itinerary_planning') is-invalid @enderror"
                                                        type="checkbox" name="itinerary_planning"
                                                        id="Status" value="1" {{$product->status == '1' ? 'checked':''}}>
                                                </span>
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header d-flex justify-content-between">
                                <h4 class="card-title mt-2">@lang("Product Images")</h4>
                            </div>
                            <div class="card-body">
                                <!-- Gallery -->
                                <div id="fancyboxGallery" class="js-fancybox row justify-content-sm-center gx-3">
                                    @forelse($product->productImages as $image)
                                        <div class="col-6 col-sm-4 col-md-3 mb-3 mb-lg-5">
                                            <!-- Card -->
                                            <div class="card card-sm">
                                                <img class="card-img-top"
                                                     src="{{ getFile($image->driver,$image->image, true) }}"
                                                     alt="Image Description">
                                            </div>
                                        </div>
                                    @empty
                                    @endforelse
                                </div>
                            </div>
                        </div>
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
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 500
            })
        })();
    </script>
@endpush
