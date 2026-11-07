@extends(userLayout())
@section('title', trans('Request Money List'))
@section('content')
    <div class="content {{ containerClass() }}">

        <x-page-header menu="History" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Complete Request Money"
                :value="$requestMoney['completeRequest'] ?? 0"
                :total="$requestMoney['totalRequest'] ?? 0"
                :percentage="$requestMoney['completeRequestPercentage'] ?? 0"
                icon="bi-check2-circle"
                color="success"
            />
            <x-stats-card
                title="Pending Request Money"
                :value="$requestMoney['pendingRequest'] ?? 0"
                :total="$requestMoney['totalRequest'] ?? 0"
                :percentage="$requestMoney['pendingRequestPercentage'] ?? 0"
                icon="bi-hourglass"
                color="warning"
            />
            <x-stats-card
                title="Canceled Request Money"
                :value="$requestMoney['cancelRequest'] ?? 0"
                :total="$requestMoney['totalRequest'] ?? 0"
                :percentage="$requestMoney['cancelRequestPercentage'] ?? 0"
                icon="bi-x-circle"
                color="danger"
            />
            <x-stats-card
                title="Today Request Money"
                :value="$requestMoney['todayRequest'] ?? 0"
                :total="$requestMoney['totalRequest'] ?? 0"
                :percentage="$requestMoney['todayRequestPercentage'] ?? 0"
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
                                <input type="search" id="datatableSearch"
                                       class="search form-control form-control-sm"
                                       placeholder="@lang('Search Request Money')"
                                       aria-label="@lang('Search Request Money')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">

                            <x-btn.info route="user.requestMoney.initialize"
                                        icon="cash-coin">@lang('Request Money') </x-btn.info>
                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" title="Filter Request Money List" datatable>
                                <x-offcanvas.filter-field label="Transaction ID" name="trx_id" type="text"
                                                          id="filter_trx_id" placeholder="R1200252511"/>
                                <x-offcanvas.filter-field label="Currency" name="currency_id" type="select"
                                                          id="filter_currency"
                                                          :options="$currencies->pluck('code', 'id')->toArray()"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                                          :options="['0' => 'Pending', '1' => 'Success', '2'=>'Canceled']"
                                                          :indicators="[ '0'=>'bg-warning', '1'=>'bg-success','2'=>'bg-danger']"/>
                                <x-offcanvas.filter-field label="Date" name="created_at" type="date" mode="range"/>
                            </x-offcanvas.layout>
                        </div>

                    </div>

                    <div class=" table-responsive datatable-custom">
                        <table id="datatable"
                               class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                               data-hs-datatables-options='{
                                       "columnDefs": [{
                                          "targets": [0, 7],
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
                                <th scope="col">@lang('Request From / To')</th>
                                <th scope="col">@lang('Transaction ID')</th>
                                <th scope="col">@lang('Requested Amount')</th>
                                <th scope="col">@lang('Charge')</th>
                                <th scope="col">@lang('Charge Pay')</th>
                                <th scope="col">@lang('Requester E-Mail')</th>
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

    <div class="modal fade" id="cancelModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
         aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalHeader">@lang('Confirmation!')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="deleteModalBody">@lang('Are you want to cancel this request?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <a href="" class="btn btn-soft-danger" id="cancelButton">@lang('Confirmed')</a>
                </div>
            </div>
        </div>
    </div>
@endsection

<x-assets :datatable="true" :counter="true"/>

@push('script')
    <script>
        $(document).on('ready', function () {
            let url = "{{ route('user.requestMoney.search') }}";
            initDataTable(url,
                {!! json_encode([ 'participant', 'transaction_id', 'amount','charge','charge_pay', 'receiver_mail', 'status',
                    'request_at','action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_trx_id: $('#filter_trx_id').val(),
                    filter_currency: $('#filter_currency').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });

        $(document).on('click', '.cancelButton', function () {
            let route = $(this).data('route');
            $('#cancelButton').attr('href', route);
        });

    </script>
@endpush
