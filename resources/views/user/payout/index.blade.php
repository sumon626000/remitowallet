@extends(userLayout())
@section('title',__('Payout List'))

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Payout')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Payout List')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <h4 class="card-header-title">@lang('Payout List')</h4>

                            <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                                <x-btn.info route="user.payout" icon="wallet2">@lang('Request Payout')</x-btn.info>
                                <x-offcanvas.filter-btn/>
                                <x-offcanvas.layout >
                                    <x-offcanvas.filter-field label="Transaction" name="transaction" type="text" placeholder="P1159900078" actoff/>
                                    <x-offcanvas.filter-field label="Status" name="status" type="select"
                                        :options="[
                                            '0' => 'Pending',
                                            '1' => 'Generate',
                                            '2' => 'Payout Done',
                                            '3' => 'Cancelled'
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
                                    <th>@lang('Method')</th>
                                    <th>@lang('Transaction ID')</th>
                                    <th>@lang('Amount')</th>
                                    <th>@lang('Charge')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created time')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody class="js-sortable">
                                @forelse($payouts as $key => $value)
                                    <tr>
                                        <td data-label="@lang('SL')">{{ $loop->index + 1 }}</td>
                                        <td>
                                            <a class="d-flex align-items-center me-2 cursor-unset" href="javascript:void(0)">
                                                <div class="flex-shrink-0">
                                                   {!! $value->picture() !!}
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="text-hover-primary mb-0">{{ $value->method?->name }}</h5>
                                                </div>
                                            </a>
                                        </td>
                                        <td data-label="@lang('Transaction ID')">{{ __($value->trx_id) }}</td>

                                        <td data-label="@lang('Amount')" class="amount-highlight">
                                            {{ currencyPosition($value->amount,$value->currency_id) }}
                                        </td>
                                        <td data-label="@lang('Charge')" class="text-danger">
                                            {{ currencyPosition($value->charge,$value->currency_id) }}
                                        </td>
                                        <td data-label="@lang('Status')">
                                            {!! $value->getStatus() !!}
                                        </td>
                                        <td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
                                        <td data-label="@lang('Action')">
                                            @if($value->status == 0)
                                                <a href="{{ route('user.payout.confirm',$value->trx_id) }}" target="_blank"
                                                   class="btn btn-xs btn-outline-primary">
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
                            {{ $payouts->appends($_GET)->links('user.partials.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


<!--push-assets-->
<x-assets/>
