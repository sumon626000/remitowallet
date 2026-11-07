@extends('admin.layouts.app')
@section('page_title','Voucher List')
@section('content')
    <div class="content container-fluid">
        <x-page-header menu="Voucher" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Payment Done Voucher"
                :value="$vouchers['paymentDoneVoucher'] ?? 0"
                :total="$vouchers['totalVoucher'] ?? 0"
                :percentage="$vouchers['paymentDoneVoucherPercentage'] ?? 0"
                icon="bi-check2-circle"
                color="success"
            />
            <x-stats-card
                title="Generated Voucher"
                :value="$vouchers['generatedVoucher'] ?? 0"
                :total="$vouchers['totalVoucher'] ?? 0"
                :percentage="$vouchers['generatedVoucherPercentage'] ?? 0"
                icon="bi-gear"
                color="secondary"
            />
            <x-stats-card
                title="Canceled Voucher"
                :value="$vouchers['cancelVoucher'] ?? 0"
                :total="$vouchers['totalVoucher'] ?? 0"
                :percentage="$vouchers['cancelVoucherPercentage'] ?? 0"
                icon="bi-x-circle"
                color="danger"
            />

            <div class="col-sm-6 col-lg-3 mb-3 mb-lg-4">
                <div class="card card-hover-shadow h-100 stats-card">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="flex-grow-1">
                                <h6 class="card-subtitle mb-3">@lang("Last 30 Days Profit")</h6>
                                <h3 class="card-title ">{{ currencyPosition($vouchers['profit_30_days']??0) }}</h3>
                                <div class="d-flex align-items-center">
                                    <span class="d-block fs-6">@lang("total") {{ currencyPosition($vouchers['total_profit']??0) }}</span>
                                    <span class="badge bg-soft-info text-info ms-2">
                                        <i class="bi-graph-up"></i> {{fractionNumber($vouchers['profit_percentage_30_days'])??0}}%
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
                                       placeholder="@lang('Search Voucher')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" title="Filter Voucher List" datatable>
                                <x-offcanvas.filter-field label="Transaction ID" name="trx_id" type="text"
                                                          id="filter_trx_id" placeholder="V1522258500"/>
                                <x-offcanvas.filter-field label="Currency" name="currency_id" type="select"
                                                          id="filter_currency" :options="$currencies->pluck('code', 'id')->toArray()"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                    :options="[
                                        '1'=>'Generated',
                                        '2'=>'Payment Done',
                                        '5'=>'Canceled',
                                    ]"
                                    :indicators="[
                                        '1' => 'bg-secondary',
                                        '2' => 'bg-success',
                                        '5' => 'bg-danger'
                                    ]"
                                />
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
                                <th scope="col">@lang('Transaction ID')</th>
                                <th scope="col">@lang('Sender')</th>
                                <th scope="col">@lang('Receiver')</th>
                                <th scope="col">@lang('Amount')</th>
                                <th scope="col">@lang('Charge')</th>
                                <th scope="col">@lang('Status')</th>
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
@endsection

<x-assets :datatable="true" :counter="true"/>

@push('script')
    <script>
        $(document).on('ready', function () {
            let url = "{{ route('admin.voucher.search') }}";
            initDataTable(url,
                {!! json_encode(['transaction_id','sender','receiver','amount','charge','status','transaction_at']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_trx_id: $('#filter_trx_id').val(),
                    filter_currency: $('#filter_currency').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });
    </script>
@endpush
