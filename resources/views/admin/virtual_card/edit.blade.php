@extends('admin.layouts.app')
@section('page_title', __('Edit Card Providers'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Card Providers')</li>
                            <li class="breadcrumb-item active"
                                aria-current="page">@lang('Edit ' . $virtualCardMethod->name)</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Edit ' . $virtualCardMethod->name)</h1>
                </div>
            </div>
        </div>

        <div class="row payment_method">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between">
                            <h3 class="card-title mt-2">@lang('Edit ' . $virtualCardMethod->name)</h3>
                        </div>
                        <div class="card-body mt-2">
                            <form action="{{route('admin.virtual.cardUpdate',$virtualCardMethod->id)}}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('put')
                                <div class="row mb-4">
                                    <div class="col-sm-4">
                                        <label for="nameLabel" class="form-label">@lang('Name')</label>
                                        <input type="text" class="form-control  @error('name') is-invalid @enderror"
                                               name="name" id="nameLabel"
                                               placeholder="Name" aria-label="Name" autocomplete="off"
                                               value="@lang($virtualCardMethod->name)">
                                        @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    @if($virtualCardMethod->currencies)
                                        <div class="col-sm-4">
                                            <label for="currencyLabel"
                                                   class="form-label">@lang('Currency')</label>
                                            <div class="tom-select-custom tom-select-custom-with-tags">
                                                <select class="js-select form-select"
                                                        name="currency[]" autocomplete="off"
                                                        multiple required>
                                                    @foreach($virtualCardMethod->currencies as $key => $currency)
                                                        @foreach($currency as $curKey => $singleCurrency)
                                                            <option value="{{ $curKey }}"
                                                                    {{ in_array($curKey,$virtualCardMethod->currency) == true ? 'selected' : '' }}>{{ trans($curKey) }}</option>
                                                        @endforeach
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('currency')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    @endif

                                    <div class="col-sm-4">
                                        <label for="currencyLabel"
                                               class="form-label">@lang('Debit Currency')</label>
                                        <div class="tom-select-custom tom-select-custom-with-tags">
                                            <select class="js-select form-select"
                                                    name="debit_currency" autocomplete="off" required>
                                                @foreach($virtualCardMethod->currencies as $key => $currency)
                                                    @foreach($currency as $curKey => $singleCurrency)
                                                        <option value="{{ $curKey }}"
                                                                {{ old('debit_currency', $virtualCardMethod->debit_currency) == $curKey ? 'selected' : '' }} data-fiat="{{ $key }}">{{ trans($curKey) }}</option>
                                                    @endforeach
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('debit_currency')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="card">
                                    <div class="card-header">
                                        <h4 class="card-header-title">@lang('Parameters')</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            @foreach ($virtualCardMethod->parameters as $key => $parameter)
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

                                        @if($virtualCardMethod->extra_parameters)
                                            <div class="row">
                                                @foreach($virtualCardMethod->extra_parameters as $key => $param)
                                                    <div class="col-sm-6 mb-4">
                                                        <div class="form-group">
                                                            <label class="form-label"
                                                                   for="{{ $key }}">{{ __(strtoupper(str_replace('_',' ', $key))) }}</label>
                                                            <input type="text"
                                                                   value="{{$param}}"
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
                                        <label class="form-label" for="descriptionArea">@lang("Information Box")</label>
                                        <textarea id="descriptionArea" class="form-control" name="info_box"
                                                  placeholder="Description"
                                                  rows="3">{{$virtualCardMethod->info_box}}</textarea>
                                        <span class="invalid-feedback">
                                            @error('info_box') @lang($message) @enderror
                                        </span>
                                    </div>
                                </div>


                                <div class="row my-lg-5">
                                    <div class="col-md-3">
                                        <label class="form-check form-check-dashed" for="logoUploader">
                                            <img id="logoImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2 rounded"
                                                 src="{{ getFile($virtualCardMethod->driver, $virtualCardMethod->image, true) }}"
                                                 alt="Image Description" data-hs-theme-appearance="default">
                                            <img id="logoImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2 rounded"
                                                 src="{{ getFile($virtualCardMethod->driver, $virtualCardMethod->image, true) }}"
                                                 alt="Image Description" data-hs-theme-appearance="dark">
                                            <span class="d-block">@lang("Browse your file here")</span>
                                            <input type="file" class="js-file-attach form-check-input" name="image"
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
                                <div class="card supported_currency_card mt-lg-5">
                                    <div class="card-header">
                                        <h4 class="card-header-title">@lang('Supported Currencies Configuration')</h4>
                                    </div>
                                    <div class="table-responsive position-relative">
                                        <table
                                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                            id="supported_currency_table">
                                            <thead class="thead-light">
                                            <tr>
                                                @if($virtualCardMethod->add_fund_parameter)
                                                    @foreach($virtualCardMethod->add_fund_parameter as $key => $currency)
                                                        <th scope="col">@lang('Currency Symbol')</th>
                                                        @foreach($currency as $key1 => $parameter)
                                                            <th scope="col">{{ __(ucfirst(str_replace('_',' ', $parameter->field_level))) }}</th>
                                                        @endforeach
                                                        @break
                                                    @endforeach
                                                @endif
                                            </tr>
                                            </thead>
                                            <tbody class="add_table_row">
                                            @if($virtualCardMethod->add_fund_parameter)
                                                @foreach($virtualCardMethod->add_fund_parameter as $key => $currency)
                                                    <tr class="USD-row">
                                                        <td>
                                                            <div class="mb-1">
                                                                <input type="text" class="form-control"
                                                                       name=""
                                                                       placeholder="Currency Symbol"
                                                                       aria-label="Currency Symbol"
                                                                       value="{{$key}}"
                                                                       autocomplete="off">
                                                            </div>
                                                        </td>

                                                        @foreach($currency as $key1 => $parameter)
                                                            <td>
                                                                <div class="input-group mb-1">
                                                                    <input type="text"
                                                                           class="form-control"
                                                                           name="fund[{{$key}}][{{$key1}}]"
                                                                           aria-label="Amount (to the nearest dollar)"
                                                                           value="{{$parameter->field_value}}"
                                                                           autocomplete="off">
                                                                    <span
                                                                        class="input-group-text">{{$parameter->field_level == 'Percent Charge' ? '%':$key}}</span>
                                                                </div>
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
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
        </div>
    </div>

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

