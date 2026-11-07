@extends('admin.layouts.app')
@section('page_title', __('Merchant Configuration'))

@section('content')
    <div class="content container-fluid">

        <x-page-header menu="Basic Control" submenu="Merchant settings"/>

        <x-notification-box
            message="Configure merchant commission structures, manage operations, define transaction charges, and set minimum, maximum, daily, and monthly transaction limits."
        >
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
                                        <h5 class="card-header-title">@lang('Merchant Configuration')</h5>
                                    </div>
                                </div>

                                <div class="col-auto">
                                    <form>
                                        <div class="input-group input-group-merge input-group-flush">
                                            <div class="input-group-prepend input-group-text">
                                                <i class="bi-search"></i>
                                            </div>
                                            <input id="datatableWithSearchInput" type="search" class="form-control"
                                                   placeholder="Search here...">
                                        </div>
                                    </form>
                                </div>

                                <div class="col-auto">
                                    <a href="{{ route('admin.merchant.settings.create') }}"
                                       class="btn btn-sm btn-primary">
                                        <i class="bi-plus-circle"> @lang('Create')</i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- End Header -->

                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table
                                class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
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
                                    <th>@lang('Currency')</th>
                                    <th>@lang('Fixed Charge')</th>
                                    <th>@lang('Percent Charge') %</th>
                                    <th>@lang('Auto Withdraw')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($settings as $key => $item)
                                    <tr>
                                        <td>
                                            <a class="d-flex align-items-center"
                                               href="javascript:void(0)">
                                                <div class="flex-shrink-0">
                                                    <div class="avatar avatar-circle">
                                                        <img class="avatar-img" src="{{ $item->currency?->getImage() }}"
                                                             alt="currency-image">
                                                    </div>
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <span class="h5 text-inherit">
                                                        @lang(optional($item->currency)->name)
                                                    </span>
                                                </div>
                                            </a>
                                        </td>

                                        <td>{{ currencyPosition($item->fixed_charge,$item->currency_id) }}</td>
                                        <td>{{ fractionNumber($item->percent_charge) }}%</td>
                                        <td>
                                            <span
                                                class="badge {{ ($item->auto_withdraw) ? 'bg-info' : 'bg-secondary' }}">
                                                {{ ($item->auto_withdraw)? 'Yes' : 'No' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($item->status)
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
                                                   href="{{ route('admin.merchant.settings.edit',$item->id) }}">
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
