@extends(userLayout())
@section('title',__('Invoice History'))

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
                    <h1 class="page-header-title">@lang('Invoice History')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <h4 class="card-header-title">@lang('Invoice List')</h4>

                            <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                                <x-btn.info route="user.invoice.create" icon="receipt">
                                    @lang('Create Invoice')</x-btn.info>
                                @include('user.invoice.searchForm')
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
                                    <th>@lang('Type')</th>
                                    <th>@lang('Invoice To')</th>
                                    <th>@lang('Invoice ID')</th>
                                    <th title="@lang('Requested Amount')">@lang('Amount')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Created time')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody class="js-sortable">
                                @forelse($invoices as $key => $value)
                                    <tr>
                                        <td data-label="@lang('Type')">
                                            {!! renderAmountTypeIcon($value->sender_id) !!}
                                        </td>

                                        <td data-label="@lang('Invoice To')">{{ __($value->customer_email) }}</td>

                                        <td data-label="@lang('Invoice ID')">
                                            @php $text = $value->has_slug; @endphp
                                            <span class="short-text">{{ Str::limit($text, 15) }}</span>
                                            <span class="full-text d-none">{{ $text }}</span>

                                            <a href="javascript:void(0);" class="copy-text text-body small icon icon-xs icon-soft-secondary"
                                               onclick="copyTextIcon(this)"><i class="bi-clipboard"></i></a>
                                        </td>

                                        <td data-label="@lang('Amount')" class="amount-highlight">
                                            {{ currencyPosition($value->grand_total,$value->currency?->id) }}
                                        </td>

                                        <td data-label="@lang('Status')">
                                            {!! $value->getStatus() !!}
                                        </td>
                                        <td data-label="@lang('Created time')"> {{ dateTime($value->created_at,'d/m/Y')}} </td>
                                        <td data-label="@lang('Action')">

                                            <div class="btn-group " role="group">
                                                <a href="{{ route('user.invoice.view', $value->id) }}"
                                                   class="btn btn-white btn-sm edit_user_btn" title="view">
                                                    <i class="bi-eye me-1"></i> @lang('View')
                                                </a>
                                                <div class="btn-group ">
                                                    <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                                    <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown" style="">
                                                        @if($value->status != 'paid' && $value->status != 'rejected')
                                                            <a class="dropdown-item reminder" href="javascript:void(0)"
                                                               data-bs-target="#reminder" data-bs-toggle="modal"
                                                               data-id="{{$value->id}}" title="reminder"
                                                            >
                                                                <i class="bi-clock dropdown-item-icon"></i> @lang('Reminder')
                                                            </a>
                                                        @endif
                                                        <a class="dropdown-item" href="{{ route('downloadPdf', $value->id) }}">
                                                            <i class="bi-download dropdown-item-icon"></i> @lang('Download')
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>

                                        </td>
                                    </tr>
                                @empty
                                    @include('empty')
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer d-flex justify-content-end">
                            {{ $invoices->appends($_GET)->links('user.partials.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="reminder" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form action="{{route('user.invoiceReminder')}}" method="post">
                    @csrf
                    <input type="hidden" name="invoiceId" value="" class="invId">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">@lang('Confirmation !')</h5>
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>@lang('Do you want to send reminder for this invoice?')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary" id="cancelButton">@lang('Confirmed')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

<x-assets/>


@push('script')
    <script>
		"use strict";
		$(document).on('click', '.reminder', function () {
			$('.invId').val($(this).data('id'));
		})
	</script>
@endpush
