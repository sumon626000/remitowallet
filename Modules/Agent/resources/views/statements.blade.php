@extends(userLayout())
@section('title', trans('Agent Statements'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="History"/>

        <div class="card">
            <div class="card-header card-header-content-md-between">
                <div class="mb-2 mb-md-0">
                    <div class="input-group input-group-merge navbar-input-group">
                        <div class="input-group-prepend input-group-text">
                            <i class="bi-search"></i>
                        </div>
                        <input type="search" id="datatableSearch"
                               class="search form-control form-control-sm"
                               placeholder="@lang('Search...')"
                               autocomplete="off">
                        <a class="input-group-append input-group-text display-none" href="javascript:void(0)">
                            <i id="clearSearchResultsIcon" class="bi-x"></i>
                        </a>
                    </div>
                </div>

                <x-offcanvas.filter-btn/>
                <x-offcanvas.layout datatable>
                    <x-offcanvas.filter-field label="Transaction ID" type="text" id="filter_trx_id"/>
                    <x-offcanvas.filter-field label="Date" name="created_at" type="date" mode="range" />
                </x-offcanvas.layout>
            </div>

            <div class=" table-responsive datatable-custom  ">
                <table id="datatable"
                       class="js-datatable table table-thead-bordered table-nowrap table-align-middle card-table table-sm"
                       data-hs-datatables-options='{
                       "columnDefs": [{
                          "targets": [0, 4],
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
                        <th>@lang('Type')</th>
                        <th>@lang('Transaction Id')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Commission')</th>
                        <th>@lang('participant')</th>
                        <th>@lang('At')</th>
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
                            <!-- Select -->
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
@endsection

<x-assets :datatable="true" />

@push('script')
    <script>
        $(document).on('ready', function () {
            let url = "{{ route('agent.statement.search') }}";
            initDataTable(url,
                {!! json_encode(['type','trx_id', 'amount', 'commission', 'participant', 'at']) !!}
            );
            document.getElementById("filter_button").addEventListener("click", function () {
                let filterTransactionId = $('#filter_trx_id').val();
                let filterDate = $('#filter_date_range').val();
                const datatable = HSCore.components.HSDatatables.getItem(0);
                datatable.ajax.url(url + "?filterTransactionID=" + filterTransactionId +
                    "&filterDate=" + filterDate).load();
            });
        });
    </script>
@endpush
