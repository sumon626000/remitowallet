@extends(userLayout())
@section('title', trans('Store Category List'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Store" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Active Category"
                :value="$categories['activeCategory'] ?? 0"
                :total="$categories['totalCategory'] ?? 0"
                :percentage="$categories['activeCategoryPercentage'] ?? 0"
                icon="bi-check2-circle"
                color="success"
            />
            <x-stats-card
                title="In-Active Category"
                :value="$categories['inactiveCategory'] ?? 0"
                :total="$categories['totalCategory'] ?? 0"
                :percentage="$categories['inactiveCategoryPercentage'] ?? 0"
                icon="bi-slash-circle"
                color="danger"
            />
            <x-stats-card
                title="Today Created Category"
                :value="$categories['todayCategory'] ?? 0"
                :total="$categories['totalCategory'] ?? 0"
                :percentage="$categories['todayCategoryPercentage'] ?? 0"
                icon="bi-calendar-day"
                color="info"
            />
            <x-stats-card
                title="This Month Created Category"
                :value="$categories['thisMonthCategory'] ?? 0"
                :total="$categories['totalCategory'] ?? 0"
                :percentage="$categories['thisMonthCategoryPercentage'] ?? 0"
                icon="bi-calendar-month"
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
                                       placeholder="@lang('Search Category')"
                                       aria-label="@lang('Search Category')"
                                       autocomplete="off">
                                <a class="input-group-append input-group-text" href="javascript:void(0)">
                                    <i id="clearSearchResultsIcon" class="bi-x d-none"></i>
                                </a>
                            </div>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <a href="javascript:void(0)" class="btn btn-sm btn-primary"
                               data-bs-target="#addCategory" data-bs-toggle="modal">@lang('Add Category')
                            </a>

                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas" datatable>
                                <x-offcanvas.filter-field label="Name" name="name" type="text" id="filter_name" placeholder="Smart Electronics"/>
                                <x-offcanvas.filter-field label="Status" name="status" type="select"
                                    :options="[
                                        '1' => 'Active',
                                        '0' => 'Inactive'
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
                                <th scope="col">@lang('SL.')</th>
                                <th scope="col">@lang('Name')</th>
                                <th scope="col">@lang('Active Products')</th>
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
        @include('user.store.category.modal')
    </div>
@endsection

<x-assets :datatable="true" :counter="true"/>

@push('script')
    <script>
        $(document).on('ready', function () {
            let url = "{{ route('user.category.list.search') }}";
            initDataTable(url,
                {!! json_encode([ 'sl', 'name', 'active_products', 'status', 'created_at', 'action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_name: $('#filter_name').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });


        $(document).on('click', '.editCategory', function () {
            let name = $(this).data('name');
            let status = $(this).data('status');
            let route = $(this).data('route');

            $('.editName').val(name);
            $('.editFormAction').attr('action', route);
            if (status == 1) {
                $('.editStatus').attr('checked', true);
            }

        })

        $(document).on('click', '.deleteCategory', function () {
            var route = $(this).data('route');
            $('.deleteCategoryForm').attr('action', route);
        })

    </script>
@endpush
