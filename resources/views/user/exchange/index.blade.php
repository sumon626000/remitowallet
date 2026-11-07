@extends(userLayout())
@section('title', trans('Exchange History'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="History" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Complete Exchange"
                :value="$exchanges['completeExchange'] ?? 0"
                :total="$exchanges['totalExchange'] ?? 0"
                :percentage="$exchanges['completeExchangePercentage'] ?? 0"
                icon="bi-check2-circle"
                color="success"
            />
            <x-stats-card
                title="Pending Exchange"
                :value="$exchanges['pendingExchange'] ?? 0"
                :total="$exchanges['totalExchange'] ?? 0"
                :percentage="$exchanges['pendingExchangePercentage'] ?? 0"
                icon="bi-hourglass"
                color="warning"
            />
            <x-stats-card
                title="Today Created Exchange"
                :value="$exchanges['todayExchange'] ?? 0"
                :total="$exchanges['totalExchange'] ?? 0"
                :percentage="$exchanges['todayExchangePercentage'] ?? 0"
                icon="bi-calendar-check"
                color="info"
            />
            <x-stats-card
                title="This Month Created Exchange"
                :value="$exchanges['thisMonthExchange'] ?? 0"
                :total="$exchanges['totalExchange'] ?? 0"
                :percentage="$exchanges['thisMonthExchangePercentage'] ?? 0"
                icon="bi-calendar3"
                color="primary"
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
                                       placeholder="@lang('Search Exchange')"
                                       aria-label="@lang('Search Exchange')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <x-btn.info route="user.exchange.initialize"
                                        icon="currency-exchange">@lang('Exchange Money') </x-btn.info>

                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" datatable>
                                <x-offcanvas.filter-field label="Transaction ID" name="trx_id" type="text"
                                                          id="filter_trx_id" placeholder="E1122555245"/>

                                <x-offcanvas.filter-field label="Currency From" type="select" id="filter_currency_from"
                                                          :options="$currencies->pluck('code', 'id')->toArray()"/>
                                <x-offcanvas.filter-field label="Currency To" type="select" id="filter_currency_to"
                                                          :options="$currencies->pluck('code', 'id')->toArray()"/>

                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                                          :options="['0' => 'Pending', '1' => 'Success']"
                                                          :indicators="[ '0'=>'bg-warning', '1'=>'bg-success']"/>
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
                                <th scope="col">@lang('Exchange')</th>
                                <th scope="col">@lang('Exchange Rate')</th>
                                <th scope="col">@lang('Charge')</th>
                                <th scope="col">@lang('Status')</th>
                                <th scope="col">@lang('Exchange At')</th>
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
            let url = "{{ route('user.exchange.search') }}";
            initDataTable(url,
                {!! json_encode([ 'transaction_id', 'exchange', 'exchange_rate','charge', 'status','exchange_at' , 'action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_trx_id: $('#filter_trx_id').val(),
                    filter_currency_from: $('#filter_currency_from').val(),
                    filter_currency_to: $('#filter_currency_to').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });
    </script>
@endpush
