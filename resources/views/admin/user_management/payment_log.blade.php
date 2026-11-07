@extends('admin.layouts.app')
@section('page_title',__('View User Payment'))
@section('content')
    <div class="content container-fluid">
        <div class="row justify-content-lg-center">
            <div class="col-lg-10 laptop-width">
                @include('admin.user_management.components.header_user_profile')
                <div class="card">
                    <div class="card-header card-header-content-md-between">
                        <div class="mb-2 mb-md-0">
                            <div class="input-group input-group-merge navbar-input-group">
                                <div class="input-group-prepend input-group-text">
                                    <i class="bi-search"></i>
                                </div>
                                <input type="search" id="datatableSearch" class="search form-control form-control-sm"
                                       placeholder="@lang('Search payment')" autocomplete="off">
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                        </div>
                    </div>

                    <div class=" table-responsive datatable-custom  ">
                        <table id="datatable"
                               class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                               "columnDefs": [{
                                  "targets": [0, 7],
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
                                <th>@lang('Trx Number')</th>
                                <th>@lang('Method')</th>
                                <th>@lang('Amount')</th>
                                <th>@lang('Charge')</th>
                                <th>@lang('Payable Amount')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Date')</th>
                                <th>@lang('Action')</th>
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
                                    <div class="tom-select-custom">
                                        <select id="datatableEntries"
                                                class="js-select form-select form-select-borderless w-auto"
                                                autocomplete="off"
                                                data-hs-tom-select-options='{
                                            "searchInDropdown": false,
                                            "hideSearch": true
                                          }'>
                                            <option value="5">5</option>
                                            <option value="10" selected>10</option>
                                            <option value="15">15</option>
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
        </div>
    </div>

    @include('admin.user_management.components.payment_information_modal')
    @include('admin.user_management.components.update_balance_modal')
    @include('admin.user_management.components.login_as_user')
    @include('admin.user_management.components.block_profile_modal')

    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">@lang('Filter Payment List')</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-3">
            <form id="filter_form" action="">
                <div class="row">
                    <div class="mb-4">
                        <span class="text-cap text-body">@lang('Transaction ID')</span>
                        <div class="row">
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" placeholder="D1548555500"
                                       id="filter_trx_id" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm mb-4">
                        <small class="text-cap text-body">@lang('Status')</small>
                        <div class="tom-select-custom">
                            <select
                                class="js-select js-datatable-filter form-select form-select-sm"
                                id="filter_status"
                                data-target-column-index="4"
                                data-hs-tom-select-options='{ "hideSearch": true, "searchInDropdown": false, "dropdownWidth": "100%" }'>
                                <option value="all"
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-secondary"></span>@lang("All Status")</span>'>
                                    @lang('All Status')
                                </option>
                                <option value="1"
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-success"></span>@lang("Success")</span>'>
                                    @lang('Success')
                                </option>
                                <option value="2"
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-warning"></span>@lang("Pending")</span>'>
                                    @lang('Pending')
                                </option>
                                <option value="3"
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-danger"></span>@lang("Cancel")</span>'>
                                    @lang('Cancel')
                                </option>
                                <option value="4"
                                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-danger"></span>@lang("Failed")</span>'>
                                    @lang('Failed')
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12 mb-4">
                        <span class="text-cap text-body">@lang('Method')</span>
                        <div class="tom-select-custom">
                            <select class="js-select form-select" id="filter_gateway">
                                <option value="all"
                                        data-option-template='<span class="d-flex align-items-center"><img class="avatar avatar-xss avatar-circle me-2" src="{{ asset("assets/upload/gateway/all_gateway.png")  }}" alt="..." /><span class="text-truncate">@lang("All Gateway")</span></span>'
                                @forelse($methods as $method)
                                    <option value="@lang($method->id)"
                                            data-option-template='<span class="d-flex align-items-center"><img class="avatar avatar-xss avatar-circle me-2" src="{{getFile($method->driver, $method->image)}}" alt="Flag" /><span class="text-truncate">{{ $method->name }}</span></span>'>
                                        @lang($method->name)
                                    </option>
                                @empty
                                @endforelse
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-sm-12 mb-4">
                        <span class="text-cap text-body">@lang('Date Range')</span>
                        <div class="input-group mb-3 custom">
                            <input type="text" id="filter_date_range"
                                   class="js-flatpickr form-control"
                                   placeholder="Select dates"
                                   data-hs-flatpickr-options='{ "dateFormat": "d/m/Y", "mode": "range" }'
                                   aria-describedby="flatpickr_filter_date_range">
                            <span class="input-group-text" id="flatpickr_filter_date_range">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </span>
                        </div>

                    </div>
                </div>

                <div class="row gx-2 mt-4">
                    <div class="col">
                        <button type="button" id="clear_filter" class="btn btn-white w-100">@lang('Clear Filters')</button>
                    </div>
                    <div class="col">
                        <button type="button" class="btn btn-primary w-100" id="filter_button">
                            <i class="bi-search"></i> @lang('Apply')
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection


<x-assets :datatable="true" />

@push('script')
    <script>
        $(document).on('ready', function () {
            let url = "{{ route("admin.user.payment.search", $user->id) }}";
            initDataTable(url,
                {!! json_encode(['trx','method','amount','charge','payable','status','date','action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_trx_id: $('#filter_trx_id').val(),
                    filter_gateway: $('#filter_gateway').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });
    </script>
@endpush


