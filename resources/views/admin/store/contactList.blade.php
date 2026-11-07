@extends('admin.layouts.app')
@section('page_title', __('Contact List'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Contact List')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Contact List')</h1>
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
                                       placeholder="@lang('Search Contact')" aria-label="@lang('Search Contact')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                        </div>
                    </div>
                    <div class=" table-responsive datatable-custom">
                        <table id="datatable"
                               class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                       "columnDefs": [{
                                          "targets": [0, 4],
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
                                <th scope="col">@lang('Sender Name')</th>
                                <th scope="col">@lang('Store')</th>
                                <th scope="col">@lang('Message')</th>
                                <th scope="col">@lang('User')</th>
                                <th scope="col">@lang('Time')</th>
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
            <h5 class="offcanvas-title">@lang('Filter Contact List')</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>

        <div class="offcanvas-body p-3">
            <form id="filter_form" action="">
                <div class="row">
                    <div class="mb-4">
                        <span class="text-cap text-body">@lang('Sender Name')</span>
                        <div class="row">
                            <div class="col-12">
                                <input type="text" class="form-control form-control-sm" placeholder="Jhon Doe"
                                       id="name_filter_input" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm mb-4">
                        <small class="text-cap text-body">@lang('Store')</small>
                        <div class="tom-select-custom">
                            <select
                                class="js-select js-datatable-filter form-select form-select-sm"
                                id="filter_store"
                                data-target-column-index="4" data-hs-tom-select-options='{
                                                                  "placeholder": "Any currency",
                                                                  "searchInDropdown": false,
                                                                  "hideSearch": true,
                                                                  "dropdownWidth": "10rem"
                                                                }'>
                                <option value="all">@lang('All Stores')</option>
                                @if(!empty($stores))
                                    @foreach($stores as $store)
                                        <option value="{{$store->id}}">
                                            {{$store->name}}
                                        </option>
                                    @endforeach
                                @endif
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
                    url: "{{ route("admin.contact.list.search")}}",
                },

                columns: [
                    {data: 'sender_name', name: 'sender_name'},
                    {data: 'store', name: 'store'},
                    {data: 'message', name: 'message'},
                    {data: 'user', name: 'user'},
                    {data: 'time', name: 'time'},
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
                let name = $('#name_filter_input').val();
                let filterStore = $('#filter_store').val();
                let filterDate = $('#filter_date_range').val();

                const datatable = HSCore.components.HSDatatables.getItem(0);
                datatable.ajax.url("{{ route("admin.contact.list.search") }}" + "?name=" + name +
                    "&filterDate=" + filterDate + "&filterStore=" + filterStore).load();
            });

            $.fn.dataTable.ext.errMode = 'throw';
            $(document).on('click', '#datatableCheckAll', function () {
                $('input:checkbox').not(this).prop('checked', this.checked);
            });

        });
    </script>


@endpush
