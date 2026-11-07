@extends('admin.layouts.app')
@section('page_title', __('Currencies'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Currencies')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Currencies')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Currencies')</h1>
                </div>
            </div>
        </div>

        <x-notification-box
            message="Configure currency for payment methods, setting limits, fees, charges, status and others."
        >
            <a href="https://www.youtube.com/playlist?list=PLnF6jF6M8lnLFVi7egLm_yxqGL0fXrYiE" target="_blank"
               class="btn btn-white btn-sm">
                <i class="bi-youtube text-danger"></i> @lang('Video guide')
            </a>

            <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal"
                    data-bs-target="#currencySettingsGuideModal">
                <i class="bi bi-info-circle"></i> @lang('Read instruction')
            </button>
        </x-notification-box>

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h2 class="card-title h4 mt-3">@lang("Currencies")</h2>
                            <a href="{{ route('admin.currency.create') }}"
                               class="btn btn-primary btn-sm">@lang("Add New")
                            </a>
                        </div>
                        <div class="table-responsive position-relative">
                            <table
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                id="supported_currency_table">
                                <thead class="thead-light">
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Code')</th>
                                    <th>@lang('Rate')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Type')</th>
                                    <th scope="col">@lang('Action')</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($currencies as $key => $currency)
                                    <tr>
                                        <td><a class="d-flex align-items-center"
                                               href="javascript:void(0)">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-circle">
                                                        <img class="avatar-img"
                                                             src="{{ getFile($currency->driver, $currency->logo) }}"
                                                             alt="Image Description">
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="h5 text-inherit">
                                                        @lang($currency->name)
                                                    </span>
                                                </div>
                                            </a></td>
                                        <td>@lang($currency->code)</td>
                                        <td><span
                                                class="font-weight-bold">{{ __($currency->symbol) }}</span>{{ (getAmount($currency->exchange_rate)) }}
                                        </td>
                                        <td>
                                            @if($currency->is_default)
                                                <span class="badge bg-soft-primary text-primary">
                                                    <span class="legend-indicator bg-primary"></span>@lang('Default')
                                                </span>
                                            @elseif($currency->is_active)
                                                <span class="badge bg-soft-success text-success">
                                                    <span class="legend-indicator bg-success"></span>@lang('Active')
                                                </span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">
                                                    <span class="legend-indicator bg-danger"></span>@lang('Inactive')
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($currency->currency_type == 0)
                                                <span class="badge bg-soft-warning text-warning">
                                                    <span class="legend-indicator bg-warning"></span>@lang('Crypto')
                                                </span>
                                            @elseif($currency->currency_type == 1)
                                                <span class="badge bg-soft-dark text-dark">
                                                    <span class="legend-indicator bg-dark"></span>@lang('Fiat')
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-white btn-sm"
                                                   href="{{ route('admin.currency.edit',$currency) }}">
                                                    <i class="bi-pencil-square me-1"></i> @lang('Edit')
                                                </a>
                                                <div class="btn-group">
                                                    <button type="button"
                                                            class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty"
                                                            id="productsEditDropdown1" data-bs-toggle="dropdown"
                                                            aria-expanded="false"></button>

                                                    <div class="dropdown-menu dropdown-menu-end mt-1"
                                                         aria-labelledby="productsEditDropdown1">
                                                        <a href="{{ route('admin.charge.chargeEdit',[config('transactionType.transfer'),$currency->id]) }}"
                                                           class="dropdown-item">
                                                            <i class="fas fa-wave-square dropdown-item-icon"></i> @lang('Transfer')
                                                        </a>
                                                        <a href="{{ route('admin.charge.chargeEdit',[config('transactionType.request'),$currency->id]) }}"
                                                           class="dropdown-item">
                                                            <i class="fas fa-search-dollar dropdown-item-icon"></i> @lang('Request')
                                                        </a>
                                                        <a href="{{ route('admin.charge.chargeEdit',[config('transactionType.exchange'),$currency->id]) }}"
                                                           class="dropdown-item">
                                                            <i class="fas fa-exchange-alt dropdown-item-icon"></i> @lang('Exchange')
                                                        </a>
                                                        <a href="{{ route('admin.charge.chargeEdit',[config('transactionType.redeem'),$currency->id]) }}"
                                                           class="dropdown-item">
                                                            <i class="fas fa-gift dropdown-item-icon"></i> @lang('Redeem')
                                                        </a>
                                                        <a href="{{ route('admin.charge.chargeEdit',[config('transactionType.escrow'),$currency->id]) }}"
                                                           class="dropdown-item">
                                                            <i class="fas fa-handshake dropdown-item-icon"></i> @lang('Escrow')
                                                        </a>
                                                        <a href="{{ route('admin.charge.chargeEdit',[config('transactionType.voucher'),$currency->id]) }}"
                                                           class="dropdown-item">
                                                            <i class="fas fa-file-invoice-dollar dropdown-item-icon"></i> @lang('Voucher')
                                                        </a>
                                                        <a href="{{ route('admin.charge.chargeEdit',[config('transactionType.invoice'),$currency->id]) }}"
                                                           class="dropdown-item">
                                                            <i class="fas fa-file-invoice dropdown-item-icon"></i> @lang('Invoice')
                                                        </a>
                                                        <a href="{{ route('admin.charge.payment.method',[$currency->id]) }}"
                                                           class="dropdown-item">
                                                            <i class="fas fa-credit-card dropdown-item-icon"></i> @lang('Payment Methods')
                                                        </a>

                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <div class="text-center p-4">
                                            <img class="dataTables-image mb-3"
                                                 src="{{ asset('assets/admin/img/oc-error.svg') }}"
                                                 alt="Image Description" data-hs-theme-appearance="default">
                                            <img class="dataTables-image mb-3"
                                                 src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                                                 alt="Image Description" data-hs-theme-appearance="dark">
                                            <p class="mb-0">@lang("No data to show")</p>
                                        </div>
                                    </tr>
                                @endforelse
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="currencySettingsGuideModal" tabindex="-1" aria-labelledby="currencySettingsGuideLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="currencySettingsGuideLabel">💱 @lang('Currency Settings Guide')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p>@lang('This section allows you to manage all configuration aspects of a currency in') {{ basicControl()->site_title }}
                        .</p>

                    <hr>

                    <h6>1. @lang('Basic Currency Info')</h6>
                    <ul class="row gap-1">
                        <li><strong>Name:</strong> Full name of the currency (e.g., US Dollar)</li>
                        <li><strong>Code:</strong> Currency code (e.g., USD, BTC)</li>
                        <li><strong>Rate:</strong> Conversion rate against the base currency</li>
                        <li><strong>Status:</strong> Enable/Disable</li>
                        <li><strong>Type:</strong> Fiat or Crypto</li>
                    </ul>

                    <hr>

                    <h6>2. Per-Service Configuration</h6>
                    <p>Each currency can be configured per service using the common set of options below:</p>
                    <ul class="row gap-1">
                        <li><strong>Min Limit:</strong> Minimum amount allowed for the service</li>
                        <li><strong>Max Limit:</strong> Maximum amount allowed for the service</li>
                        <li><strong>Charge Fixed:</strong> Flat fee applied per transaction</li>
                        <li><strong>Charge Percentage:</strong> Fee calculated as a percentage of the transaction</li>
                        <li><strong>Status:</strong> Enable or disable the service for this currency</li>
                    </ul>
                    <p class="mt-2">These configurations apply individually to each service such as:</p>
                    <h6>Transfer, Request, Exchange, Redeem, Escrow, Invoice, Voucher</h6>

                    <hr>

                    <h6>3. @lang('Payment Method Assignment')</h6>
                    <p>Control which payment methods support each currency:</p>
                    <ul class="row gap-1">
                        <li>Assign currencies to specific manual or automated gateways</li>
                        <li>Disable unsupported currencies for selected methods</li>
                        <li>Optionally add gateway-specific fees per currency</li>
                    </ul>

                    <p>
                        Each payment method can be configured using the following fields:
                    </p>
                    <ul class="row gap-1">
                        <li><strong>Conversion Rate:</strong> Set how 1 unit of your system's base currency converts to
                            this currency
                        </li>
                        <li><strong>Minimum Deposit Limit:</strong> The smallest amount allowed per transaction</li>
                        <li><strong>Maximum Deposit Limit:</strong> The highest amount allowed per transaction</li>
                        <li><strong>Fixed Charge:</strong> A constant fee applied to every transaction using this method
                        </li>
                        <li><strong>Percentage Charge:</strong> A percentage-based fee taken from the transaction amount
                        </li>
                        <li><strong>Status:</strong> Enable or disable the method for this currency</li>
                    </ul>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

@endsection
