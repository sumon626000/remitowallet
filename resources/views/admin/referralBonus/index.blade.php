@extends('admin.layouts.app')
@section('page_title', __('Commission List'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Referral Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Commission List')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header card-header-content-md-between">
                        <div class="mb-2 mb-md-0">
                            <div class="input-group input-group-merge navbar-input-group">
                                <div class="input-group-prepend input-group-text">
                                    <i class="bi-search"></i>
                                </div>
                                <input type="search" id="datatableSearch" class="search form-control form-control-sm"
                                       placeholder="@lang('Search...')" autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>
                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                        </div>
                    </div>

                    <div class="table-responsive datatable-custom">
                        <table id="datatable"
                               class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                       "columnDefs": [{
                                          "targets": [0, 6],
                                          "orderable": false
                                        }],
                                        "ordering": false,
                                       "order": [],
                                       "info": {
                                         "totalQty": "#datatableWithPaginationInfoTotalQty"
                                       },
                                       "search": "#datatableSearch",
                                       "entries": "#datatableEntries",
                                       "pageLength": 20,
                                       "isResponsive": false,
                                       "isShowPaging": false,
                                       "pagination": "datatablePagination"
                                     }'>
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">@lang('Profit From')</th>
                                <th scope="col">@lang('Profit To')</th>
                                <th scope="col">@lang('Amount')</th>
                                <th scope="col">@lang('Level')</th>
                                <th scope="col">@lang('Title')</th>
                                <th scope="col">@lang('Transaction ID')</th>
                                <th scope="col">@lang('Transaction At')</th>
                            </tr>
                            </thead>
                        </table>
                    </div>

                    <div class="card-footer">
                        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                            <div class="col-sm mb-2 mb-sm-0">
                                <div
                                    class="d-flex justify-content-center justify-content-sm-start align-items-center">
                                    <span class="me-2">@lang('Showing:')</span>
                                    <div class="tom-select-custom">
                                        <select id="datatableEntries"
                                                class="js-select form-select form-select-borderless w-auto"
                                                autocomplete="off"
                                                data-hs-tom-select-options='{
                                                        "searchInDropdown": false,
                                                        "hideSearch": true
                                                      }'>
                                            <option value="10">10</option>
                                            <option value="15">15</option>
                                            <option value="20" selected>20</option>
                                            <option value="30">30</option>
                                        </select>
                                    </div>
                                    <span class="text-secondary me-2">@lang('of')</span>
                                    <span id="datatableWithPaginationInfoTotalQty"></span>
                                </div>
                            </div>
                            <div class="col-sm-auto">
                                <div class="d-flex  justify-content-center justify-content-sm-end">
                                    <nav id="datatablePagination" aria-label="Activity pagination"></nav>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">@lang('Filter Commission List')</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-3">
            <form id="filter_form">
                <div class="mb-3">
                    <span class="text-cap text-body">@lang('Transaction Id')</span>
                    <div class="row">
                        <div class="col-12">
                            <input type="text" class="form-control" id="trx_id" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="text-cap text-body">@lang('Sender Email')</span>
                    <div class="row">
                        <div class="col-12">
                            <input type="text" class="form-control" id="filter_one" autocomplete="off">
                        </div>
                    </div>
                </div>
                <div class="mb-3">
                    <span class="text-cap text-body">@lang('Receiver Email')</span>
                    <div class="row">
                        <div class="col-12">
                            <input type="text" class="form-control" id="filter_two" autocomplete="off">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 mb-4">
                        <span class="text-cap text-body">@lang('Date Range')</span>
                        <div class="input-group mb-3 custom">
                            <input type="text" id="filter_date_range" class="js-flatpickr form-control"
                                   placeholder="Select dates"
                                   data-hs-flatpickr-options='{ "dateFormat": "d/m/Y", "mode": "range" }'
                                   aria-describedby="flatpickr_filter_date_range">

                            <span class="input-group-text" id="flatpickr_filter_date_range">
                                    <i class="bi bi-arrow-counterclockwise"></i>
                                </span>
                        </div>
                    </div>
                </div>

                <div class="row gx-2">
                    <div class="col">
                        <div class="d-grid">
                            <button type="button" id="clear_filter" class="btn btn-white">@lang('Clear Filters')</button>
                        </div>
                    </div>
                    <div class="col">
                        <div class="d-grid">
                            <button type="button" class="btn btn-primary" id="filter_button"><i class="bi-search"></i> @lang('Apply')</button>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

@endsection

<x-assets :datatable="true" />

@push('script')
    <script>
        'use strict';

        $(document).on('ready', function () {

            initDataTable("{{ route('admin.commission.search') }}",
                {!! json_encode(['sender', 'receiver', 'amount', 'level', 'title', 'transaction_id', 'transaction_at']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter("{{ route('admin.commission.search') }}", {
                    trx_id: $('#trx_id').val(),
                    filter_one: $('#filter_one').val(),
                    filter_two: $('#filter_two').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });
    </script>

@endpush

