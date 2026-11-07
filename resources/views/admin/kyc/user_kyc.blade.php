@extends('admin.layouts.app')
@section('page_title', __('User KYC List'))
@section('content')
    <div class="content container-fluid">
        <x-page-header menu="KYC Management" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Approved KYC"
                :value="$userKYCRecord['approvedKYC'] ?? 0"
                :total="$userKYCRecord['totalKYC'] ?? 0"
                :percentage="$userKYCRecord['approvedKYCPercentage'] ?? 0"
                icon="bi-check-circle"
                color="success"
            />
            <x-stats-card
                title="Pending KYC"
                :value="$userKYCRecord['pendingKYC'] ?? 0"
                :total="$userKYCRecord['totalKYC'] ?? 0"
                :percentage="$userKYCRecord['pendingKYCPercentage'] ?? 0"
                icon="bi-hourglass"
                color="warning"
            />
            <x-stats-card
                title="Rejected KYC"
                :value="$userKYCRecord['rejectedKYC'] ?? 0"
                :total="$userKYCRecord['totalKYC'] ?? 0"
                :percentage="$userKYCRecord['rejectedKYCPercentage'] ?? 0"
                icon="bi-slash-circle"
                color="danger"
            />
            <x-stats-card
                title="This Month KYC"
                :value="$userKYCRecord['thisMonthKYC'] ?? 0"
                :total="$userKYCRecord['totalKYC'] ?? 0"
                :percentage="$userKYCRecord['thisMonthKYCPercentage'] ?? 0"
                icon="bi-calendar3"
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
                                       placeholder="@lang('Search KYC')" autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>
                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" title="Filter" datatable>
                                <x-offcanvas.filter-field label="Verification Type" name="" type="text"
                                      id="filter_verification_type" placeholder="Passport"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                    :options="[
                                        '0'=>'Pending', '1'=>'Approved', '2'=>'Rejected',
                                    ]"
                                    :indicators="[
                                        '0' => 'bg-warning', '1' => 'bg-success', '2' => 'bg-danger'
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
                                       "pageLength": 15,
                                       "isResponsive": false,
                                       "isShowPaging": false,
                                       "pagination": "datatablePagination"
                                     }'>
                            <thead class="thead-light">
                            <tr>
                                <th scope="col">@lang('No.')</th>
                                <th scope="col">@lang('Name')</th>
                                <th scope="col">@lang('Verification Type')</th>
                                <th scope="col">@lang('Status')</th>
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
                                            <option value="15" selected>15</option>
                                            <option value="20">20</option>
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
            let url = "{{ route("admin.kyc.search", $status) }}";
            initDataTable(url,
                {!! json_encode(['no','name','verification','status','action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_trx_id: $('#filter_verification_type').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });
    </script>
@endpush

