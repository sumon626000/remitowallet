@extends('admin.layouts.app')
@section('page_title','QR Payment List')
@section('content')
    <div class="content container-fluid">

        <x-page-header menu="QR Payment" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Complete Payment"
                :value="$qrPayments['completePayment'] ?? 0"
                :total="$qrPayments['totalPayment'] ?? 0"
                :percentage="$qrPayments['completePaymentPercentage'] ?? 0"
                icon="bi-check2-circle"
                color="success"
            />
            <x-stats-card
                title="Pending Payment"
                :value="$qrPayments['pendingPayment'] ?? 0"
                :total="$qrPayments['totalPayment'] ?? 0"
                :percentage="$qrPayments['pendingPaymentPercentage'] ?? 0"
                icon="bi-hourglass"
                color="warning"
            />
            <x-stats-card
                title="This Month Created Payment"
                :value="$qrPayments['thisMonthPayment'] ?? 0"
                :total="$qrPayments['totalPayment'] ?? 0"
                :percentage="$qrPayments['thisMonthPaymentPercentage'] ?? 0"
                icon="bi-calendar-month"
                color="primary"
            />
            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-4">
                <div class="card card-hover-shadow h-100 stats-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle mb-3">@lang("Last 30 Days Profit")</h6>
                                <h3 class="card-title ">{{ currencyPosition($qrPayments['profit_30_days']??0) }}</h3>
                                <div class="d-flex align-items-center">
                                    <span class="d-block fs-6">@lang("total") {{ currencyPosition($qrPayments['total_profit']??0) }}</span>
                                    <span class="badge bg-soft-info text-info ms-2">
                                        <i class="bi-graph-up"></i> {{fractionNumber($qrPayments['profit_percentage_30_days'])??0}}%
                                    </span>
                                </div>
                            </div>
                            <span class="icon icon-soft-info icon-lg icon-circle ms-3">
                                <i class="bi-cash fs-1"></i>
                            </span>
                        </div>
                    </div>
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
                                       placeholder="@lang('Search Payment')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" title="Filter QR Payment List" datatable>
                                <x-offcanvas.filter-field label="Sender Email" name="sender_email" type="text"
                                                          id="filter_email" placeholder="doe@mail.net"/>
                                <x-offcanvas.filter-field label="Gateway" type="select" id="filter_gateway"
                                                          :options="$gateways->pluck('name','id')->toArray()"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                      :options="['0'=>'Pending', '1'=>'Complete']"
                                      :indicators="[ '0'=>'bg-warning', '1'=>'bg-success']"/>
                                <x-offcanvas.filter-field label="Date" name="created_at" type="date" mode="range"/>
                            </x-offcanvas.layout>
                        </div>
                    </div>
                    <div class=" table-responsive datatable-custom">
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
                                <th scope="col">@lang('Sender Email')</th>
                                <th scope="col">@lang('Receiver')</th>
                                <th scope="col">@lang('Amount')</th>
                                <th scope="col">@lang('Charge')</th>
                                <th scope="col">@lang('Gateway')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Send At')</th>
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
@endsection

<x-assets :datatable="true" :counter="true"/>

@push('script')
    <script>
        $(document).on('ready', function () {
            let url = "{{ route('admin.qr.payment.search') }}";
            initDataTable(url,
                {!! json_encode([ 'sender', 'receiver', 'amount', 'charge', 'gateway', 'status','send_at']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_email: $('#filter_email').val(),
                    filter_gateway: $('#filter_gateway').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });
    </script>
@endpush

