@extends('admin.layouts.app')
@section('page_title', __('Edit Bill Providers'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0);">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Bill Providers')</li>
                            <li class="breadcrumb-item active"
                                aria-current="page">@lang('Edit ' . $billMethod->methodName)</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Edit ' . $billMethod->methodName)</h1>
                </div>
            </div>
        </div>

        <div class="row payment_method">
            <div class="col-lg-8">
                <div class="d-grid gap-3 gap-lg-5 ">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h3 class="card-title mt-2">@lang('Edit ' . $billMethod->methodName)</h3>
                            @if(config('billservices.'.$billMethod->code))
                                <a class="btn btn-primary btn-sm" href="#" title="@lang('Service Importer')"
                                   data-bs-target="#importService"
                                   data-bs-toggle="modal">
                                    @lang('Services') <span class="d-none d-sm-inline-block">@lang('importer')</span>
                                    <i class="bi-box-arrow-up-right ms-1"></i>
                                </a>
                            @endif
                        </div>
                        <div class="card-body mt-2">
                            <form action="{{ route('admin.bill.method.save',$billMethod->id) }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="row mb-4">
                                    <div class="col-sm-12">
                                        <label for="nameLabel" class="form-label">@lang('Method Name')</label>
                                        <input type="text"
                                               class="form-control  @error('methodName') is-invalid @enderror"
                                               name="methodName" id="nameLabel"
                                               placeholder="Name" aria-label="Name" autocomplete="off"
                                               value="@lang($billMethod->methodName)">
                                        @error('methodName')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-header-title">@lang('Parameters')</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach ($billMethod->parameters as $key => $parameter)
                                                <div class="col-sm-6 mb-4">
                                                    <div class="form-group">
                                                        <label class="form-label"
                                                               for="{{ $key }}">{{ __(strtoupper(str_replace('_',' ', $key))) }}</label>
                                                        <input type="text" name="{{ $key }}"
                                                               value="{{ old($key, $parameter) }}"
                                                               id="{{ $key }}"
                                                               class="form-control @error($key) is-invalid @enderror">
                                                        <div class="invalid-feedback">
                                                            @error($key) @lang($message) @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        @if($billMethod->extra_parameters)
                                            <div class="row">
                                                @foreach($billMethod->extra_parameters as $key => $param)
                                                    <div class="col-sm-6 mb-4">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                   for="{{ $key }}">{{ __(strtoupper(str_replace('_',' ', $key))) }}</label>
                                                            <input type="text" name="{{ $key }}"
                                                                   value="{{ old($key, route($param, $billMethod->code )) }}"
                                                                   id="{{ $key }}"
                                                                   class="form-control">
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>
                                </div>


                                <div class="row">
                                    <div class="col-md-12 mt-4">
                                        <label class="form-label" for="descriptionArea">@lang("Description")</label>
                                        <textarea id="descriptionArea" class="form-control" name="description"
                                                  placeholder="Description"
                                                  rows="3">{{$billMethod->description}}</textarea>
                                        <span class="invalid-feedback">
                                            @error('description') @lang($message) @enderror
                                        </span>
                                    </div>
                                </div>


                                <div class="row my-lg-5">
                                    <div class="col-md-3">
                                        <label class="form-check form-check-dashed" for="logoUploader">
                                            <img id="logoImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2 rounded"
                                                 src="{{ getFile($billMethod->driver, $billMethod->logo, true) }}"
                                                 alt="Image Description" data-hs-theme-appearance="default">
                                            <img id="logoImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2 rounded"
                                                 src="{{ getFile($billMethod->driver, $billMethod->logo, true) }}"
                                                 alt="Image Description" data-hs-theme-appearance="dark">
                                            <span class="d-block">@lang("Browse your file here")</span>
                                            <input type="file" class="js-file-attach form-check-input" name="logo"
                                                   id="logoUploader" data-hs-file-attach-options='{
                                                      "textTarget": "#logoImg",
                                                      "mode": "image",
                                                      "targetAttr": "src",
                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                   }'>
                                        </label>
                                        <span class="invalid-feedback d-block">
                                            @error('image') @lang($message) @enderror
                                        </span>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-start mt-4">
                                    <button type="submit" class="btn btn-primary">@lang('Save changes')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h3 class="card-title mt-2">@lang('Conversion Rate')</h3>
                        </div>
                        <div class="card-body mt-2">
                            <form action="{{ route('admin.bill.service.rate',$billMethod->id) }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                @if($isoCodes)
                                    @foreach($isoCodes as $key => $value)
                                        @if($billMethod->convert_rate)
                                            @foreach($billMethod->convert_rate as $key1 => $rate)
                                                @php
                                                    if($key == $key1){
                                                     $rate = $rate;
                                                     break;
                                                    }else{
                                                        $rate = 1;
                                                    }
                                                @endphp
                                            @endforeach
                                        @endif
                                        <div class="row mb-4">
                                            <div class="col-sm-12">
                                                <div class="input-group mb-3">
                                                    <span
                                                        class="input-group-text">1 {{basicControl()->base_currency}}</span>
                                                    <input type="text" class="form-control"
                                                           name="convert_rate[{{$key}}]"
                                                           value="{{$rate??1}}"
                                                           aria-label="Amount (to the nearest dollar)">
                                                    <span class="input-group-text">{{$key}}</span>
                                                </div>
                                                <span class="invalid-feedback">
                                                   @error('convert_rate') @lang($message) @enderror
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="d-flex justify-content-start mt-4">
                                    <button type="submit" class="btn btn-primary">@lang('Save changes')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(config('billservices.'.$billMethod->code))
        <div id="importService" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="primary-header-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold titleShow"
                            id="primary-header-modalLabel">@lang('Confirm Import Service')</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{route('admin.bill.fetch.service',$billMethod->id)}}" method="get">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <lable class="font-weight-bold">@lang('Select Category:')</lable>
                                    <div class="tom-select-custom tom-select-custom-with-tags mt-2">
                                        <select class="js-select form-select" id="api-service" name="api_service" required>
                                            <option selected="" disabled="">@lang('Select Service')</option>
                                            @foreach(config('billservices.'.$billMethod->code) as $key => $service)
                                                <option value="{{$key}}">{{$service['name']}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('api_service')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                            @if($billMethod->code == 'bloc')
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <lable class="font-weight-bold">@lang('Select Operators:')</lable>
                                        <select class="form-control" id="dynamic-operators" name="operator_id" required>

                                        </select>
                                        @error('operator')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn-primary">@lang('Yes')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        (function () {
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 500
            })
            new HSFileAttach('.js-file-attach')
        })();
    </script>
@endpush

