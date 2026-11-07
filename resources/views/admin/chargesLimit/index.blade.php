@extends('admin.layouts.app')
@section('page_title', __('Charges & Limits'))

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Basic control')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Charges & Limits')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Charges & Limits')</h1>
                </div>
            </div>
        </div>

        <x-notification-box
            message="Configure currency for payment methods, setting limits, fees, charges, status and others."
        >
            <a href="https://www.youtube.com/playlist?list=PLnF6jF6M8lnLFVi7egLm_yxqGL0fXrYiE" target="_blank"
               class="btn btn-white btn-sm" >
                <i class="bi-youtube text-danger"></i> @lang('Video guide')
            </a>

            <button type="button" class="btn btn-soft-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#currencySettingsGuideModal">
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
                        <!-- Header -->
                        <div class="card-header">
                            <div class="row justify-content-between align-items-center flex-grow-1">
                                <div class="col-12 col-md">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-header-title">@lang('Charges & Limits')</h5>
                                    </div>
                                </div>

                                <div class="col-auto">
                                    <form>
                                        <div class="input-group input-group-merge input-group-flush">
                                            <div class="input-group-prepend input-group-text">
                                                <i class="bi-search"></i>
                                            </div>
                                            <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search here...">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Header -->

                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                   data-hs-datatables-options='{
                                       "order": [],
                                       "ordering": false,
                                       "search": "#datatableWithSearchInput",
                                       "isResponsive": false,
                                       "isShowPaging": false,
                                       "pageLength": 20,
                                       "pagination": "datatableWithSearchPagination"
                                     }'>
                                <thead class="thead-light">
                                <tr>
                                    <th>@lang('Name')</th>
                                    <th>@lang('Transaction Type')</th>
                                    <th>@lang('Min/Max Limit')</th>
                                    <th>@lang('Payment Method')</th>
                                    <th>@lang('Charge') %</th>
                                    <th>@lang('Fixed Charge')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($chargesLimits as $key => $chargesLimit)
                                    <tr>
                                        <td>
                                            <a class="d-flex align-items-center"
                                               href="javascript:void(0)">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-circle">
                                                        <img class="avatar-img"
                                                             src="{{ getFile(optional($chargesLimit->currency)->driver,optional($chargesLimit->currency)->logo) }}"
                                                             alt="currency-image">
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <span class="h5 text-inherit">
                                                        @lang(optional($chargesLimit->currency)->name)
                                                    </span>
                                                </div>
                                            </a>
                                        </td>
                                        <td>{{ __(ucfirst(array_search($chargesLimit->transaction_type_id, config('transactionType')))) }}</td>
                                        <td>{{ getAmount($chargesLimit->min_limit) }}
                                            / {{ getAmount($chargesLimit->max_limit) }}</td>
                                        <td>{{ __(optional($chargesLimit->gateway)->name ?? __('N/A')) }}</td>
                                        <td>{{ getAmount($chargesLimit->percentage_charge) }}</td>
                                        <td>{{ getAmount($chargesLimit->fixed_charge) }}</td>
                                        <td>
                                            @if($chargesLimit->is_active)
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
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-white btn-sm"
                                                   href="{{ route('admin.charge.edit',$chargesLimit) }}">
                                                    <i class="bi-pencil-square me-1"></i> @lang('Edit')
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    @include('empty')
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer">
                            <div class="d-flex justify-content-center justify-content-sm-end">
                                <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="currencySettingsGuideModal" tabindex="-1" aria-labelledby="currencySettingsGuideLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="currencySettingsGuideLabel">💱 @lang('Currency Settings Guide')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p>@lang('This section allows you to manage all configuration aspects of a currency in') {{ basicControl()->site_title }}.</p>

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
                        <li><strong>Conversion Rate:</strong> Set how 1 unit of your system's base currency converts to this currency</li>
                        <li><strong>Minimum Deposit Limit:</strong> The smallest amount allowed per transaction</li>
                        <li><strong>Maximum Deposit Limit:</strong> The highest amount allowed per transaction</li>
                        <li><strong>Fixed Charge:</strong> A constant fee applied to every transaction using this method</li>
                        <li><strong>Percentage Charge:</strong>  A percentage-based fee taken from the transaction amount</li>
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

<x-assets :datatable="true" :flatpickr="false" :tomselect="false"/>

@push('script')
    <script>
        'use strict'
        $(document).ready(function () {
            initNormalDataTable();
        });
    </script>
@endpush
