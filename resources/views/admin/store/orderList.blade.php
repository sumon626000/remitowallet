@extends('admin.layouts.app')
@section('page_title',__('Order List'))
@section('content')
    <div class="content container-fluid">
        <x-page-header menu="Store" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="New Arrived Orders"
                :value="$orders['newArriveOrder'] ?? 0"
                :total="$orders['totalOrder'] ?? 0"
                :percentage="$orders['newArriveOrderPercentage'] ?? 0"
                icon="bi-cart-plus"
                color="warning"
            />
            <x-stats-card
                title="Processing Orders"
                :value="$orders['processingOrder'] ?? 0"
                :total="$orders['totalOrder'] ?? 0"
                :percentage="$orders['processingOrderPercentage'] ?? 0"
                icon="bi-cpu"
                color="info"
            />
            <x-stats-card
                title="Delivered Orders"
                :value="$orders['deliveredOrder'] ?? 0"
                :total="$orders['totalOrder'] ?? 0"
                :percentage="$orders['deliveredOrderPercentage'] ?? 0"
                icon="bi-bag-check"
                color="success"
            />
            <x-stats-card
                title="Canceled Orders"
                :value="$orders['cancelOrder'] ?? 0"
                :total="$orders['totalOrder'] ?? 0"
                :percentage="$orders['cancelOrderPercentage'] ?? 0"
                icon="bi-x-circle"
                color="danger"
            />
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
                                       placeholder="@lang('Search Orders')" aria-label="@lang('Search Orders')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" title="Filter Order List" datatable>
                                <x-offcanvas.filter-field label="Order Number" type="text" id="filter_order_no"/>
                                <x-offcanvas.filter-field label="Gateway" type="select" id="filter_gateway"
                                                          :options="$gateways->pluck('name','id')->toArray()"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                      :options="[
                                        '-1' => 'New Arrival',
                                        '1' => 'Processing',
                                        '2' => 'On Shipping',
                                        '3' => 'Out For Delivery',
                                        '4' => 'Delivered',
                                        '5' => 'Canceled'
                                    ]"
                                    :indicators="[
                                        '-1' => 'bg-warning',
                                        '1' => 'bg-info',
                                        '2' => 'bg-dark',
                                        '3' => 'bg-primary',
                                        '4' => 'bg-success',
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
                                          "targets": [0, 8],
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
                                <th scope="col">@lang('Order Number')</th>
                                <th scope="col">@lang('Amount')</th>
                                <th scope="col">@lang('Shipping Charge')</th>
                                <th scope="col">@lang('Email')</th>
                                <th scope="col">@lang('Store')</th>
                                <th scope="col">@lang('Gateway')</th>
                                <th scope="col">@lang('User')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Action')</th>
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

@push('css-lib')
    <script src="{{ asset("assets/admin/js/hs-file-attach.min.js") }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush


@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';

        $(document).on('ready', function () {
            new HSFileAttach('.js-file-attach')
            HSCore.components.HSSortable.init('.js-sortable')
            HSCore.components.HSFlatpickr.init('.js-flatpickr')
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250,
            })

            HSCore.components.HSDatatables.init($('#datatable'), {
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route("admin.order.list.search")}}",
                },

                columns: [
                    {data: 'order_number', name: 'order_number'},
                    {data: 'amount', name: 'amount'},
                    {data: 'shipping_charge', name: 'shipping_charge'},
                    {data: 'email', name: 'email'},
                    {data: 'store', name: 'store'},
                    {data: 'gateway', name: 'gateway'},
                    {data: 'user', name: 'user'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action'},
                ],
                language: {
                    zeroRecords: `<div class="text-center p-4">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                    <p class="mb-0">No data to show</p>
                    </div>`,
                    processing: `<div><div></div><div></div><div></div><div></div></div>`
                },
            })

            document.getElementById("filter_button").addEventListener("click", function () {
                let name = $('#filter_order_no').val();
                let filterGateway = $('#filter_gateway').val();
                let filterStage = $('#filter_status').val();
                let filterDate = $('#filter_date_range').val();

                const datatable = HSCore.components.HSDatatables.getItem(0);
                datatable.ajax.url("{{ route("admin.order.list.search") }}" + "?name=" + name +
                    "&filterDate=" + filterDate + "&filterGateway=" + filterGateway + "&filterStage=" + filterStage).load();
            });

            $.fn.dataTable.ext.errMode = 'throw';
            $(document).on('click', '#datatableCheckAll', function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

        });
    </script>


@endpush
