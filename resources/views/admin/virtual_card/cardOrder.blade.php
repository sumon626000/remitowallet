@extends('admin.layouts.app')
@section('page_title', __('Requested Card List'))
@section('content')
    <div class="content container-fluid">
        <x-page-header menu="Virtual Card" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Approved Request"
                :value="$cardOrders['approveRequest'] ?? 0"
                :total="$cardOrders['totalRequest'] ?? 0"
                :percentage="$cardOrders['approveRequestPercentage'] ?? 0"
                icon="bi-check-circle"
                color="success"
            />
            <x-stats-card
                title="Pending Request"
                :value="$cardOrders['pendingRequest'] ?? 0"
                :total="$cardOrders['totalRequest'] ?? 0"
                :percentage="$cardOrders['pendingRequestPercentage'] ?? 0"
                icon="bi-hourglass"
                color="warning"
            />
            <x-stats-card
                title="Resubmit Request"
                :value="$cardOrders['reSubmitRequest'] ?? 0"
                :total="$cardOrders['totalRequest'] ?? 0"
                :percentage="$cardOrders['reSubmitRequestPercentage'] ?? 0"
                icon="bi-arrow-clockwise"
                color="info"
            />
            <x-stats-card
                title="Rejected Request"
                :value="$cardOrders['rejectedRequest'] ?? 0"
                :total="$cardOrders['totalRequest'] ?? 0"
                :percentage="$cardOrders['rejectedRequestPercentage'] ?? 0"
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
                                       placeholder="@lang('Search Request')" autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">

                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" title="Filter Voucher List" datatable>
                                <x-offcanvas.filter-field label="User" name="name" type="text"
                                                          id="filter_name" placeholder="demouser"/>
                                <x-offcanvas.filter-field label="Methods" name="method" type="select" id="filter_method"
                                      :options="$virtualCardMethods->pluck('name', 'id')->toArray()"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                    :options="[
                                        '0'=>'Pending',
                                        '1'=>'Approved',
                                        '2'=>'Rejected',
                                        '3'=>'Re-Submit'
                                    ]"
                                    :indicators="[
                                        '0' => 'bg-warning',
                                        '1' => 'bg-success',
                                        '2' => 'bg-danger',
                                        '3' => 'bg-info',
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
                                          "targets": [0, 5],
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
                                <th scope="col">@lang('Provider')</th>
                                <th scope="col">@lang('Currency')</th>
                                <th scope="col">@lang('User')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Requested At')</th>
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

<x-assets :datatable="true" :counter="true"/>

@push('script')
    <script>
        $(document).on('ready', function () {
            let url = "{{ route('admin.virtual.cardOrder.search') }}";
            initDataTable(url,
                {!! json_encode(['provider','currency','user','status','requested_at','action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    name: $('#filter_name').val(),
                    filterMethod: $('#filter_method').val(),
                    filterStatus: $('#filter_status').val(),
                    filterDate: $('#filter_date_range').val()
                });
            });
        });
    </script>
@endpush
