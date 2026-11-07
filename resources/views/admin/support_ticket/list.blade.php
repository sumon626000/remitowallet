@extends('admin.layouts.app')
@section('page_title', __(ucfirst(@$status)).' '. __('Tickets'))
@section('content')
    <div class="content container-fluid">
        <x-page-header menu="Ticket Panel" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Answered Ticket"
                :value="$ticketRecord['answerTicket'] ?? 0"
                :total="$ticketRecord['totalTicket'] ?? 0"
                :percentage="$ticketRecord['answerTicketPercentage'] ?? 0"
                icon="bi-check-circle"
                color="success"
            />
            <x-stats-card
                title="Open Ticket"
                :value="$ticketRecord['openTicket'] ?? 0"
                :total="$ticketRecord['totalTicket'] ?? 0"
                :percentage="$ticketRecord['openTicketPercentage'] ?? 0"
                icon="bi-envelope-open"
                color="warning"
            />
            <x-stats-card
                title="Customer Replied Ticket"
                :value="$ticketRecord['repliedTicket'] ?? 0"
                :total="$ticketRecord['totalTicket'] ?? 0"
                :percentage="$ticketRecord['repliedTicketPercentage'] ?? 0"
                icon="bi-envelope-exclamation"
                color="info"
            />
            <x-stats-card
                title="Closed Ticket"
                :value="$ticketRecord['closedTicket'] ?? 0"
                :total="$ticketRecord['totalTicket'] ?? 0"
                :percentage="$ticketRecord['closedTicketPercentage'] ?? 0"
                icon="bi-envelope-x"
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
                                <input type="search" id="datatableSearch"
                                       class="search form-control form-control-sm"
                                       placeholder="@lang('Search tickets')"
                                       aria-label="@lang('Search tickets')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>
                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" title="Filter" datatable>
                                <x-offcanvas.filter-field label="Subject" name="filter_subject" type="text"
                                                          id="filter_subject" placeholder="Login Problem"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                    :options="[
                                        '0'=>'Open', '1'=>'Answered', '2'=>'Customer Reply', '3'=>'Closed',
                                    ]"
                                    :indicators="[
                                        '0' => 'bg-warning', '1' => 'bg-success', '2' => 'bg-info', '3' => 'bg-danger'
                                    ]"
                                />
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
                                <th scope="col">@lang('Sl')</th>
                                <th scope="col">@lang('User')</th>
                                <th scope="col">@lang('Subject')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Last Reply')</th>
                                <th scope="col">@lang('Action')</th>
                            </tr>
                            </thead>

                            <tbody>

                            </tbody>
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
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush


@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        $(document).on('ready', function () {

            HSCore.components.HSFlatpickr.init('.js-flatpickr')
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250,
            })

            HSCore.components.HSDatatables.init($('#datatable'), {
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route("admin.ticket.search", $status) }}",
                },

                columns: [
                    {data: 'no', name: 'no'},
                    {data: 'username', name: 'username'},
                    {data: 'subject', name: 'subject'},
                    {data: 'status', name: 'status'},
                    {data: 'lastReply', name: 'lastReply'},
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
                let subject = $('#filter_subject').val();
                let filterStatus = $('#filter_status').val();
                let filterDate = $('#filter_date_range').val();

                const datatable = HSCore.components.HSDatatables.getItem(0);
                datatable.ajax.url("{{ route("admin.ticket.search", $status) }}" + "?subject=" + subject +
                    "&filterDate=" + filterDate + "&filterStatus=" + filterStatus).load();
            });

            $.fn.dataTable.ext.errMode = 'throw';

        });

    </script>
@endpush



