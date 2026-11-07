@extends(userLayout())
@section('title',trans('Fund History'))
@section('content')

    <div class="content {{ containerClass() }}">
        <x-page-header menu="History" />

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <h4 class="card-header-title mb-md-0 mb-3">@lang('Fund History')</h4>

                            <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                                <x-btn.info route="fund.initialize" icon="wallet" class="btn btn-primary btn-sm">@lang('Add Fund')</x-btn.info>
                                <x-offcanvas.filter-btn/>

                                <x-offcanvas.layout id="filterOffcanvas">
                                    <x-offcanvas.filter-field label="Transaction Id" name="trx_id" type="text" placeholder="D2591074000" atcoff/>
                                    <x-offcanvas.filter-field label="Minimum Amount" name="min" type="text" placeholder="10" atcoff/>
                                    <x-offcanvas.filter-field label="Maximum Amount" name="max" type="text" placeholder="100" atcoff/>
                                    <x-offcanvas.filter-field label="Currency" name="currency_id" type="select"
                                                              :options="$currencies->pluck('code', 'id')->toArray()" />
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
                                    <th>@lang('Pay Amount')</th>
                                    <th>@lang('Charge')</th>
                                    <th>@lang('Receive Wallet')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created time')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody class="js-sortable">
                                @forelse($funds as $key => $value)
                                    <tr>
                                        <td data-label="@lang('SL')">{{ $loop->index + 1 }}</td>
                                        <td>
                                            <a class="d-flex align-items-center me-2 cursor-unset" href="javascript:void(0)">
                                                <div class="flex-shrink-0">
                                                    {!! $value?->picture() !!}
                                                </div>
                                                <div class="flex-grow-1 ms-3">
                                                    <h5 class="text-hover-primary mb-0">{{ $value->gateway?->name }}</h5>
                                                </div>
                                            </a>
                                        </td>
                                        <td data-label="@lang('Transaction ID')">{{ __($value->trx_id) }}</td>

                                        <td data-label="@lang('Pay Amount')" class="amount-highlight">
                                            {{ currencyPosition($value->amount,$value->currency_id) }}
                                        </td>
                                        <td data-label="@lang('Charge')" class="text-danger">
                                            {{ currencyPosition($value->charge,$value->currency_id) }}
                                        </td>

                                        <td data-label="@lang('Receive Wallet')">
                                            {{currencyPosition($value->receive_amount,$value->receive_wallet?->id)}} <span class="badge bg-soft-dark text-dark ms-1">{{$value->receive_wallet?->name}}</span>
                                        </td>
                                        <td data-label="@lang('Status')">
                                            {!! $value->getStatus() !!}
                                        </td>
                                        <td data-label="@lang('Created time')"> {{ dateTime($value->created_at)}} </td>
                                        <td data-label="@lang('Action')">
                                            @if($value->status == 0)
                                                <a href="{{ route('deposit.confirm',$value->trx_id) }}" target="_blank"
                                                   class="btn btn-soft-primary btn-xs"><i class="bi-check-circle me-1"></i> @lang('Confirm')</a>
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
                            {{ $funds->appends($_GET)->links('user.partials.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

<x-assets/>

