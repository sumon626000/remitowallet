@extends('admin.layouts.app')
@section('page_title',__('Agent Management'))
@section('content')
    <div class="content container-fluid">

        <x-page-header menu="Agent" :submenu="ucfirst($state ?? null)"  :statBtn="true">
            <x-slot name="push_header_button">
                <a class="btn btn-info btn-sm me-2" href="{{ route('admin.users.add','agent') }}">
                    <i class="bi-person-plus-fill"></i> @lang('Add Agent')
                </a>
            </x-slot>
        </x-page-header>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Total members"
                :value="$userRecord['totalUser'] ?? 0"
                :total="$userRecord['totalUserWithTrashed'] ?? 0"
                :percentage="$userRecord['totalUserPercentage'] ?? 0"
                icon="bi-people"
                color="info"
            />
            <x-stats-card
                title="Active members"
                :value="$userRecord['activeUser'] ?? 0"
                :total="$userRecord['totalUser'] ?? 0"
                :percentage="$userRecord['activeUserPercentage'] ?? 0"
                icon="bi-person-check"
                color="success"
            />
            <x-stats-card
                title="Today Join"
                :value="$userRecord['todayJoin'] ?? 0"
                :total="$userRecord['totalUser'] ?? 0"
                :percentage="$userRecord['todayJoinPercentage'] ?? 0"
                icon="bi-calendar-check"
                color="info"
            />
            <x-stats-card
                title="Deactivate members"
                :value="$userRecord['deactivateUser'] ?? 0"
                :total="$userRecord['totalUser'] ?? 0"
                :percentage="$userRecord['deactivateUserPercentage'] ?? 0"
                icon="bi-slash-circle"
                color="danger"
            />
        </div>

        <!-- Card -->
        <div class="card">
            <div class="card-header card-header-content-md-between">
                <div class="mb-2 mb-md-0">
                    <div class="input-group input-group-merge input-group-flush">
                        <div class="input-group-prepend input-group-text">
                            <i class="bi-search"></i>
                        </div>
                        <input id="datatableSearch" type="search" class="form-control" placeholder="Search..."
                               aria-label="Search" autocomplete="off">
                    </div>
                </div>

                <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                    <div id="datatableCounterInfo">
                        <div class="d-flex align-items-center">
                            <span class="fs-5 me-3">
                              <span id="datatableCounter">0</span>
                              @lang('Selected')
                            </span>
                            <a class="btn btn-outline-danger btn-sm" href="javascript:void(0)" data-bs-toggle="modal"
                               data-bs-target="#userDeleteMultipleModal">
                                <i class="bi-trash"></i> @lang('Delete')
                            </a>
                        </div>
                    </div>
                    <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                        <x-offcanvas.filter-btn/>
                    </div>
                </div>
            </div>

            <div class=" table-responsive datatable-custom  ">
                <table id="datatable"
                       class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                       data-hs-datatables-options='{
                       "columnDefs": [{
                          "targets": [0, 6],
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
                        <th class="table-column-pe-0">
                            <div class="form-check">
                                <input class="form-check-input check-all tic-check" type="checkbox" name="check-all"
                                       id="datatableCheckAll">
                                <label class="form-check-label" for="datatableCheckAll"></label>
                            </div>
                        </th>
                        <th class="table-column-ps-0">@lang('Full Name')</th>
                        <th>@lang('Email-Phone')</th>
                        <th>@lang('Country')</th>
                        <th>@lang('Status')</th>
                        <th>@lang('Last Login')</th>
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
                            <span class="text-secondary me-2">of</span>
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

    @include('admin.user_management.components.multiple_user_delete_modal')
    @include('admin.user_management.components.login_as_user')
    @include('admin.user_management.components.update_balance_modal')

    <div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">@lang('Filter User List')</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-3">
            <form id="filter_form" action="">
            <div class="row">
                <div class="col-sm-12 mb-4">
                    <small class="text-cap text-body">@lang("User Status")</small>
                    <div class="tom-select-custom">
                        <select
                            class="js-select js-datatable-filter form-select form-select-sm"
                            id="select_status"
                            data-target-column-index="4" data-hs-tom-select-options='{
                                                          "placeholder": "Any status",
                                                          "searchInDropdown": false,
                                                          "hideSearch": true,
                                                          "dropdownWidth": "10rem"
                                                        }'>
                            <option value="all"
                                    data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-secondary"></span>@lang("All Status")</span>'>
                                @lang("All Status")
                            </option>
                            <option value="1"
                                    data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-success"></span>Active</span>'>
                                @lang("Active")
                            </option>
                            <option value="0"
                                    data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-danger"></span>Inactive</span>'>
                                @lang("Inactive")
                            </option>
                        </select>
                    </div>
                </div>

                <small class="col-sm-7 text-cap text-body">@lang('Email Verification')</small>
                <div class="col-sm-5 mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               id="emailFilterCheckAll">
                        <label class="form-check-label" for="emailFilterCheckAll">
                            @lang('Verified')
                        </label>
                    </div>
                </div>

                <small class="col-sm-7 text-cap text-body">@lang('SMS Verification')</small>
                <div class="col-sm-5 mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               id="SMSFilterCheckAll">
                        <label class="form-check-label" for="SMSFilterCheckAll">
                            @lang('Verified')
                        </label>
                    </div>
                </div>

                <small class="col-sm-7 text-cap text-body">@lang('2FA Security')</small>
                <div class="col-sm-5 mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox"
                               id="TwoFaFilterCheckAll">
                        <label class="form-check-label" for="TwoFaFilterCheckAll">
                            @lang('Verified')
                        </label>
                    </div>
                </div>

                <div class="col-12 mb-4">
                    <span class="text-cap text-body">@lang("Name")</span>
                    <input type="text" class="form-control" id="username_filter_input"
                           autocomplete="off">
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

<x-assets :datatable="true" :select="true" :flatpickr="false" :counter="true"/>

@push('script')
    <script>

        $(document).on('ready', function () {

            initDataTable("{{ route('admin.agents.search',$state) }}",
                {!! json_encode(['checkbox', 'name', 'email-phone','country', 'status', 'last login', 'action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter("{{ route('admin.agents.search',$state) }}", {
                    filterStatus : $('#select_status').val(),
                    filterName : $('#username_filter_input').val(),
                    filterSelectedLocation : $('#location_filter_select option:selected').val(),
                    emailVerifiedFilter : $('#emailFilterCheckAll').is(':checked') ? 1 : '',
                    SMSVerifiedFilter : $('#SMSFilterCheckAll').is(':checked') ? 1 : '',
                    TwoFaFilter : $('#TwoFaFilterCheckAll').is(':checked') ? 1 : '',
                });
            });

            $(document).on('click', '#datatableCheckAll', function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

            $(document).on('change', ".row-tic", function () {
                let length = $(".row-tic").length;
                let checkedLength = $(".row-tic:checked").length;
                if (length == checkedLength) {
                    $('#check-all').prop('checked', true);
                } else {
                    $('#check-all').prop('checked', false);
                }
            });

            $(document).on('click', '.delete-multiple', function (e) {
                e.preventDefault();
                let all_value = [];
                $(".row-tic:checked").each(function () {
                    all_value.push($(this).attr('data-id'));
                });
                let strIds = all_value;
                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    url: "{{ route('admin.user.delete.multiple') }}",
                    data: {strIds: strIds},
                    datatType: 'json',
                    type: "post",
                    success: function (data) {
                        location.reload();
                    },
                });
            });

        });

        $(document).on('ready', function () {

            $(document).on('click', '.loginAccount', function () {
                let route = $(this).data('route');
                $('.loginAccountAction').attr('action', route)
            });

            $(document).on('click', '.addBalance', function () {
                $('.setBalanceRoute').attr('action', $(this).data('route'));
                let id = $(this).data('id');
                $('#showBalance').html('');
                axios.get("{{ route('admin.users.getBalance', '') }}/" + id)
                    .then(response => {
                        if (response.data.status) {
                            let balances = response.data.balances;
                            let dynamic = [];
                            balances.forEach(function (item, index) {
                                dynamic += `<li class="list-group-item text-dark">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span>${item.currency}</span>
                                                    <span>${item.value}</span>
                                                </div>
                                            </li>`;
                            });

                            $('#showBalance').html(dynamic);
                        }
                    })
                    .catch(error => {
                        // Handle errors
                    });
            });

        });

    </script>

@endpush




