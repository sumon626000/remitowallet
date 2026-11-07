@extends('admin.layouts.app')
@section('page_title',__('Transaction'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0);">@lang('Dashboard')</a></li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Transaction Log')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Transaction Log')</h1>
                </div>
            </div>
        </div>

        @if(request('type'))
            <x-user-type.alert/>
        @endif

        <div class="card">
            <div class="card-header card-header-content-md-between">
                <div class="mb-2 mb-md-0">
                    <div class="input-group input-group-merge navbar-input-group">
                        <div class="input-group-prepend input-group-text">
                            <i class="bi-search"></i>
                        </div>
                        <input type="search" id="datatableSearch" class="search form-control form-control-sm"
                               placeholder="@lang('Search transaction')" aria-label="@lang('Search transaction')"
                               autocomplete="off">
                        <a class="input-group-append input-group-text display-none" href="javascript:void(0)">
                            <i id="clearSearchResultsIcon" class="bi-x"></i>
                        </a>
                    </div>
                </div>

                <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">

                    <x-user-type.filter/>

                    <x-offcanvas.filter-btn/>
                    <x-offcanvas.layout id="filterOffcanvas" title="Filter Transactions" datatable>
                        <x-offcanvas.filter-field label="Transaction ID" name="trx_id" type="text"
                                                  id="filter_trx_id" placeholder="T1522258500"/>
                        <x-offcanvas.filter-field label="Date" name="created_at" type="date" mode="range"/>
                    </x-offcanvas.layout>

                </div>
            </div>

            <div class=" table-responsive datatable-custom  ">
                <table id="datatable"
                       class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                       "columnDefs": [{
                          "targets": [0, 5],
                          "orderable": false
                        }],
                       "order": [],
                       "info": {
                         "totalQty": "#datatableWithPaginationInfoTotalQty"
                       },
                       "search": "#datatableSearch",
                       "entries": "#datatableEntries",
                       "pageLength": 15,
                       "isResponsive": false,
                       "isShowPaging": false,
                       "pagination": "datatablePagination"
                     }'>
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('No.')</th>
                        <th>@lang('Transaction Id')</th>
                        <th>@lang('User')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Charge')</th>
                        <th>@lang('Remarks')</th>
                        <th>@lang('Date-time')</th>
                    </tr>
                    </thead>

                    <tbody>

                    </tbody>
                </table>
            </div>

            <div class="card-footer">
                <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                    <div class="col-sm mb-2 mb-sm-0">
                        <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                            <span class="me-2">@lang('Showing:')</span>
                            <!-- Select -->
                            <div class="tom-select-custom">
                                <select id="datatableEntries"
                                        class="js-select form-select form-select-borderless w-auto" autocomplete="off"
                                        data-hs-tom-select-options='{
                                            "searchInDropdown": false,
                                            "hideSearch": true
                                          }'>
                                    <option value="10">10</option>
                                    <option value="15" selected>15</option>
                                    <option value="20">20</option>
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

@endsection


<x-assets :datatable="true" />

@push('script')
    <script>
        $(document).on('ready', function () {

            let url = "{{ route('admin.transaction.search',$type) }}";
            initDataTable(url,
                {!! json_encode(['no', 'trx', 'user', 'amount', 'charge', 'remarks', 'date-time']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_trx_id: $('#filter_trx_id').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });

        });

    </script>
@endpush




