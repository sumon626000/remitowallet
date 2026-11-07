@extends(userLayout())
@section('title',__('Pay Bill List'))

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Bill')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Pay Bill List')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <h4 class="card-header-title">@lang('Pay Bill List')</h4>

                            <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">

                                <x-btn.info route="user.pay.bill" icon="receipt-cutoff">@lang('Pay Bill')</x-btn.info>
                                <x-offcanvas.filter-btn/>
                                <x-offcanvas.layout id="filterOffcanvas" title="Filter Bill list">
                                    <x-offcanvas.filter-field label="Category" name="category" type="text" placeholder="Airtime" actoff/>
                                    <x-offcanvas.filter-field label="Type" name="type" type="text" placeholder="Airtel Bangladesh" actoff/>
                                    <x-offcanvas.filter-field label="Minimum Amount" name="min" type="text" placeholder="10" actoff/>
                                    <x-offcanvas.filter-field label="Maximum Amount" name="max" type="text" placeholder="100" actoff/>

                                    <x-offcanvas.filter-field label="Status" name="status" type="select"
                                          :options="[
                                                'generate' => 'Generate',
                                                'pending' => 'Pending',
                                                'payment_completed' => 'Payment Done',
                                                'bill_completed' => 'Bill Completed',
                                                'bill_return' => 'Bill Return'
                                            ]"
                                    />
                                    <x-offcanvas.filter-field label="Date" name="created_at" type="date" />
                                </x-offcanvas.layout>
                            </div>

                        </div>

                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table
                                class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
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
                                    <th>@lang('SL')</th>
                                    <th>@lang('Category')</th>
                                    <th>@lang('Type')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Charge')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created time')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody class="js-sortable">
                                @forelse($bills as $key => $value)
                                    <tr>
                                        <td data-label="@lang('SL')">{{loopIndex($bills) + $key}}</td>
                                        <td data-label="@lang('Category')">{{ __(str_replace('_',' ',ucfirst($value->category_name))) }}</td>
                                        <td data-label="@lang('Type')">{{ __($value->type) }}</td>
                                        <td data-label="@lang('Amount')" class="fw-semibold">
                                            {{ (getAmount($value->amount,2)).' '.__($value->currency) }}
                                        </td>
                                        <td data-label="@lang('Charge')">
                                            <span class="text-danger">{{ (getAmount($value->charge,2)).' '.__($value->currency) }}</span>
                                        </td>
                                        <td data-label="@lang('Status')">
                                            {!! $value->getStatus() !!}
                                        </td>
                                        <td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
                                        <td data-label="@lang('Action')">
                                            @if($value->status == 0)
                                                <a href="{{ route('user.pay.bill.confirm',$value->utr) }}"
                                                   target="_blank" class="btn btn-xs btn-outline-primary">
                                                    <i class="bi-check-circle me-1"></i> @lang('Confirm')
                                                </a>
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    @include('empty')
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            {{ $bills->appends($_GET)->links('user.partials.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

<x-assets/>
