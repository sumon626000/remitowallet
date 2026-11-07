@extends('admin.layouts.app')
@section('page_title', __('Dispute List'))
@section('content')
    <div class="content container-fluid">
        <x-page-header menu="Dispute" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Solved Dispute"
                :value="$disputes['solvedDispute'] ?? 0"
                :total="$disputes['totalDispute'] ?? 0"
                :percentage="$disputes['solvedDisputePercentage'] ?? 0"
                icon="bi-check2-circle"
                color="success"
            />
            <x-stats-card
                title="Open Dispute"
                :value="$disputes['openDispute'] ?? 0"
                :total="$disputes['totalDispute'] ?? 0"
                :percentage="$disputes['openDisputePercentage'] ?? 0"
                icon="bi-question-circle"
                color="primary"
            />
            <x-stats-card
                title="Closed Dispute"
                :value="$disputes['closeDispute'] ?? 0"
                :total="$disputes['totalDispute'] ?? 0"
                :percentage="$disputes['closeDisputePercentage'] ?? 0"
                icon="bi-x-circle"
                color="danger"
            />
            <x-stats-card
                title="Today Dispute"
                :value="$disputes['todayDispute'] ?? 0"
                :total="$disputes['totalDispute'] ?? 0"
                :percentage="$disputes['todayDisputePercentage'] ?? 0"
                icon="bi-calendar-check"
                color="info"
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
                                       placeholder="@lang('Search Dispute')" aria-label="@lang('Search Dispute')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" title="Filter Dispute List" datatable>
                                <x-offcanvas.filter-field label="Dispute ID" name="trx_id" type="text"
                                                          id="filter_trx_id" placeholder="D454DFE78"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                    :options="[ '0' => 'Open', '1' => 'Solved', '2' => 'Closed' ]"
                                    :indicators="[ '0' => 'bg-primary', '1' => 'bg-success', '2'=>'bg-danger']"
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
                                <th scope="col">@lang('Dispute ID')</th>
                                <th scope="col">@lang('Name')</th>
                                <th scope="col">@lang('Claimer')</th>
                                <th scope="col">@lang('Defender')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Created At')</th>
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
            let url = "{{ route('admin.dispute.search') }}";
            initDataTable(url,
                {!! json_encode([ 'dispute_id', 'name', 'claimer', 'defender', 'status', 'created_at','action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_trx_id: $('#filter_trx_id').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });
    </script>
@endpush

