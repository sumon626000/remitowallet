@extends('admin.layouts.app')
@section('page_title', __('Service Control'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.dashboard')  }}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Service Control')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Service Control')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9 seo-setting">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title h4">@lang('Service Control')</h2>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.service.control') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                <div class="row">

                                    @php
                                        $services = [
                                            'transfer' => 2,
                                            'request' => 3,
                                            'exchange' => 3,
                                            'redeem' => 4,
                                            'escrow' => 5,
                                            'voucher' => 6,
                                            'deposit' => 7,
                                            'store' => 7,
                                            'payout' => 8,
                                            'invoice' => 9,
                                            'virtual_card' => 10,
                                            'bill_payment' => 11,
                                            'qr_payment' => 17,
                                            'qr_payments' => 1787,
                                        ];
                                        if (module_exists('Agent')) {
                                            $services['cash_in'] = 31;
                                            $services['cash_out'] = 32;
                                        }
                                        if (module_exists('Merchant')) {
                                            $services['make_payment'] = 33;
                                        }
                                    @endphp

                                    @foreach ($services as $service => $permission)
                                        @if (checkPermission($permission))
                                            <div class="col-md-6">
                                            <label class="row form-check form-switch mb-4" for="{{ $service }}">
                                                <span class="col-8 col-sm-9 ms-0">
                                                    <span class="d-block text-dark">
                                                        @lang(str_headline($service)) @lang('Service')
                                                    </span>
                                                    <span class="d-block fs-5">
                                                        @lang("To enable or disable this service, users must have access to all modules related to")
                                                        {{ str_headline($service) }}
                                                    </span>
                                                </span>
                                                <span class="col-4 col-sm-3 text-end">
                                                    <input type='hidden' value='0' name='{{ $service }}'>
                                                    <input class="form-check-input @error($service) is-invalid @enderror"
                                                           type="checkbox" name="{{ $service }}" id="{{ $service }}"
                                                           value="1" @checked($basicControl->$service == 1)
                                                    >
                                                </span>
                                                @error($service)
                                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                                @enderror
                                            </label>
                                            </div>
                                        @endif
                                    @endforeach

                                </div>

                                <div class="d-flex justify-content-end mt-4">
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




