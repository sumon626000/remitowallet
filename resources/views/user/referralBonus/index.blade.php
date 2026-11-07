@extends(userLayout())
@section('title', __('Commission History'))

@section('content')

    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('History')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Commission History')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <h4 class="card-header-title">@lang('Commission History')</h4>

                            <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                                <x-offcanvas.filter-btn/>
                                <x-offcanvas.layout id="filterOffcanvas" >
                                    <x-offcanvas.filter-field label="Transaction ID" name="utr" type="text"
                                                placeholder="C2591074000" atcoff/>
                                    <x-offcanvas.filter-field label="Sender" name="sender" type="text"
                                                placeholder="Steve" atcoff/>
                                    <x-offcanvas.filter-field label="Minimum Amount" name="min" type="text"
                                                placeholder="10" atcoff/>
                                    <x-offcanvas.filter-field label="Maximum Amount" name="max" type="text"
                                                placeholder="100" atcoff/>
                                    <x-offcanvas.filter-field label="Date" name="created_at" type="date" />
                                </x-offcanvas.layout>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                data-hs-datatables-options='{
                                   "order": [],
                                   "info": {
                                     "totalQty": "#datatableEntriesInfoTotalQty"
                                   },
                                   "ordering":false,
                                   "pageLength": 40,
                                   "entries": "#datatableEntries",
                                   "isResponsive": false,
                                   "isShowPaging": false,
                                   "pagination": "datatableWithPaginationPagination"
                                 }'>
                                <thead class="thead-light">
                                <tr>
                                    <th>@lang('From')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Title')</th>
                                    <th>@lang('Level')</th>
                                    <th>@lang('Transaction ID')</th>
                                    <th>@lang('Transaction At')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($commissionEntries as $key => $value)
                                    <tr>
                                        <td data-label="@lang('Sender')">
                                            <a class="d-flex align-items-center me-2" href="javascript:;">
                                                <div class="flex-shrink-0">
                                                    {!! $value->sender?->profilePicture() !!}
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="text-hover-primary mb-0"> {{ $value->sender?->name }} </h5>
                                                    <span class="fs-6 text-body"> {{ '@'.$value->sender?->username }}</span>
                                                </div>
                                            </a>
                                        </td>

                                        <td data-label="@lang('Amount')" class="text-success fw-semibold">
                                            {{ (currencyPosition($value->commission_amount)) }}
                                        </td>
                                        <td data-label="@lang('Title')">{{ __($value->title) }}</td>
                                        <td data-label="@lang('Level')">
                                            <span class="badge bg-soft-dark text-dark">{{ __($value->level) }}</span>
                                        </td>
                                        <td data-label="@lang('Transaction ID')">{{ __($value->utr) }}</td>
                                        <td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
                                    </tr>
                                @empty
                                    @include('empty')
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            {{ $commissionEntries->appends($_GET)->links('user.partials.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

<x-assets/>
