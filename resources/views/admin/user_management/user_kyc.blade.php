@extends('admin.layouts.app')
@section('page_title',__('View Profile'))
@section('content')
    <div class="content container-fluid">
        <div class="row justify-content-lg-center">
            <div class="col-lg-10 laptop-width">

                @include('admin.user_management.components.header_user_profile')

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card">
                            <div class="card-header card-header-content-md-end">
                                <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">

                                    <x-offcanvas.filter-btn/>
                                    <x-offcanvas.layout id="filterOffcanvas" title="Filter Redeem Code List" datatable>
                                        <x-offcanvas.filter-field label="VERIFICATION TYPE" name="vt" type="text"
                                              id="filter_verification_type" placeholder="NID"/>

                                        <x-offcanvas.filter-field label="Status" name="status" type="select"
                                              :options="['0' => 'Pending', '1' => 'Verified', '2'=>'Rejected']"
                                              :indicators="[ '0'=>'bg-warning', '1'=>'bg-success','2'=>'bg-danger']"
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
                                        <th scope="col">@lang('Verification Type')</th>
                                        <th scope="col">@lang('Status')</th>
                                        <th scope="col">@lang('Date')</th>
                                        <th scope="col">@lang('Action')</th>
                                    </tr>
                                    </thead>

                                    <tbody>

                                    </tbody>
                                </table>
                            </div>

                            <div class="card-footer">
                                <div
                                    class="row justify-content-center justify-content-sm-between align-items-sm-center">
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
                                                    <option value="5">5</option>
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
                </div>
            </div>
        </div>
    </div>

    @include('admin.user_management.components.update_balance_modal')

@endsection

<x-assets :datatable="true"/>

@push('script')
    <script>
        $(document).on('ready', function () {
            let url = "{{ route("admin.user.kyc.search", $user->id) }}";
            initDataTable(url,
                {!! json_encode(['no','verification_type','status','date','action']) !!}
            );

            document.getElementById("filter_button").addEventListener("click", function () {
                applyFilter(url, {
                    filter_verification_type: $('#filter_verification_type').val(),
                    filter_status: $('#filter_status').val(),
                    filter_date: $('#filter_date_range').val()
                });
            });
        });
    </script>
@endpush









