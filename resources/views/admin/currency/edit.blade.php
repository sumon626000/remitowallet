@extends('admin.layouts.app')
@section('page_title', __('Edit Currency'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0)">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Edit Currency')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Edit Currency')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Edit Currency')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title h4">@lang('Edit Currency')</h2>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.currency.update',$currency) }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row mb-4">
                                    <label for="nameLabel"
                                           class="col-sm-3 col-form-label form-label">@lang("Name")</label>
                                    <div class="col-sm-9">
                                        <input type="text"
                                               class="form-control @error('name') is-invalid @enderror"
                                               name="name" id="nameLabel"
                                               placeholder="@lang("eg:- US Dollar")"
                                               value="{{ $currency->name }}"
                                               autocomplete="off">
                                        @error('name')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="Code"
                                           class="col-sm-3 col-form-label form-label">@lang("Code")</label>
                                    <div class="col-sm-9">
                                        <input type="text"
                                               class="form-control code @error('code') is-invalid @enderror"
                                               name="code" id="Code"
                                               placeholder="@lang("eg:- USD")"
                                               value="{{ $currency->code }}"
                                               autocomplete="off">
                                        @error('code')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="Symbol"
                                           class="col-sm-3 col-form-label form-label">@lang("Symbol")</label>
                                    <div class="col-sm-9">
                                        <input type="text"
                                               class="form-control @error('symbol') is-invalid @enderror"
                                               name="symbol" id="Symbol"
                                               placeholder="@lang("eg:- $")"
                                               value="{{ $currency->symbol }}"
                                               autocomplete="off">
                                        @error('symbol')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="ExchangeRate"
                                           class="col-sm-3 col-form-label form-label">@lang("Exchange Rate")</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <span
                                                class="input-group-text">1 {{ __(basicControl()->base_currency) }}</span>
                                            <input type="text"
                                                   class="form-control @error('exchange_rate') is-invalid @enderror"
                                                   name="exchange_rate" id="ExchangeRate"
                                                   placeholder="@lang("eg: 1.00")"
                                                   value="{{ getAmount($currency->exchange_rate) }}"
                                                   autocomplete="off">
                                            <span class="input-group-text rate">.00</span>
                                            @error('exchange_rate')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <label for="CurrencyType"
                                           class="col-sm-3 col-form-label form-label">@lang("Currency Type")</label>
                                    <div class="col-sm-9">
                                        <div class="tom-select-custom">
                                            <select
                                                class="js-select form-select @error('currency_type') is-invalid @enderror"
                                                id="Currency Type" name="currency_type">
                                                <option
                                                    value="0" {{ ($currency->currency_type == 0) ? 'selected' : '' }}>@lang('Crypto')</option>
                                                <option
                                                    value="1" {{ ($currency->currency_type == 1) ? 'selected' : '' }}>@lang('Fiat')</option>
                                            </select>
                                        </div>
                                        @error('currency_type')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-2">
                                    <div class="col-sm-12">
                                        <div class="list-group list-group-lg list-group-flush list-group-no-gutters">
                                            <div class="list-group-item">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <div class="row align-items-center">
                                                            <div class="col">
                                                                <span class="d-block text-dark">@lang("Status")</span>
                                                                <p class="fs-5 text-body mb-0">@lang("Empower users to display their currency preferences by enabling a currency status feature.")</p>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="form-check form-switch">
                                                                    <input type='hidden' value='0' name='is_active'>
                                                                    <input
                                                                        class="form-check-input @error('is_active') is-invalid @enderror"
                                                                        type="checkbox"
                                                                        name="is_active"
                                                                        id="is_active"
                                                                        value="1" {{$currency->is_active ? 'checked':''}}>
                                                                    <label class="form-check-label"
                                                                           for="is_active"></label>
                                                                    @error('is_active')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End List Item -->

                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-md-4 mb-3 mb-md-0">
                                        <label class="form-check form-check-dashed" for="logoUploader">
                                            <img id="flagImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                 src="{{ getFile($currency->driver,$currency->logo) }}"
                                                 alt="@lang("Flag")"
                                                 data-hs-theme-appearance="default">

                                            <img id="flagImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                 src="{{ getFile($currency->driver,$currency->logo) }}"
                                                 alt="@lang("Flag")" data-hs-theme-appearance="dark">
                                            <span class="d-block">@lang("Browse your file here")</span>
                                            <input type="file" class="js-file-attach form-check-input"
                                                   name="logo" id="logoUploader"
                                                   data-hs-file-attach-options='{
                                                      "textTarget": "#flagImg",
                                                      "mode": "image",
                                                      "targetAttr": "src",
                                                      "allowTypes": [".png", ".jpeg", ".jpg", ".svg"]
                                                   }'>
                                        </label>
                                        @error("logo")
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
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
    <script src="{{ asset("assets/admin/js/hs-file-attach.min.js") }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        setCurrencyCode();

        function setCurrencyCode() {
            $('.rate').text($('.code').val());
        }

        $(document).on('input', '.code', function () {
            setCurrencyCode();
        })
        $(document).ready(function () {
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250,
            })
            new HSFileAttach('.js-file-attach')
        })
    </script>
@endpush
