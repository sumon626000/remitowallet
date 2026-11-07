@extends(userLayout())
@section('title',__('Voucher payment'))

@section('content')

    <div class="content {{ containerClass() }}">
        <div class="page-header border-0 mb-0">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Voucher")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("Voucher payment")</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Voucher payment')</h4>
                    </div>

                    <div class="card-body">
                        <div class="list-group list-group-flush list-group-no-gutters showCharge">
                            <x-list-item label="Payable amount" value="{{ currencyPosition($voucher->transfer_amount,$voucher->currency_id) }}" class="text-info fw-semibold"/>
                            @if($voucher->sender_id == auth()->id())
                                <x-list-item label="Total charge" class="text-danger"
                                             value="{{ currencyPosition($voucher->charge,$voucher->currency_id) }}" />
                                <x-list-item label="Receivable Amount"  class="text-success"
                                             value="{{ currencyPosition($voucher->received_amount,$voucher->currency_id) }}"/>
                            @endif
                            <x-list-item label="Note" value="{{ __($voucher->note) }}" />
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 ms-2">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <span class="d-block">{{ __('Status') }}</span>
                                            </div>
                                            <div class="col-auto">
                                                {!! $voucher->getStatus() !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3 ">
                            <div class="col-md-12">
                                @if($voucher->status == 1)
                                    @if($voucher->receiver_id == Auth::id())
                                        <button type="submit" class="btn btn-success btn-sm btn-block confirmButton"
                                                name="status" value="2"
                                                data-bs-toggle="modal" data-bs-target="#confirmModal"
                                                data-text="@lang('Accept this request?')">
                                            <i class="fa fa-check-circle"></i> @lang('Accept')
                                        </button>
                                    @endif
                                    <button type="submit" class="btn btn-soft-danger btn-sm btn-block confirmButton"
                                            name="status" value="5"
                                            data-bs-toggle="modal" data-bs-target="#confirmModal"
                                            data-text="@lang('Cancel this request?')">
                                        <i class="fa fa-times-circle"></i> @lang('Cancel')
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


	<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
		 aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title text-danger" id="exampleModalLabel">@lang('Confirmation !')</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
				</div>
				<div class="modal-body">
					<p>@lang('Are you sure you want to') <span id="action-text"></span></p>
				</div>
				<div class="modal-footer">
					<form action="{{ route('user.voucher.paymentView', $voucher->utr) }}" method="post">
						@csrf
						<button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn btn-primary" id="confirmButton" name="status" value="6">
							@lang('Confirmed')
						</button>
					</form>
				</div>
			</div>
		</div>
	</div
@endsection

@push('script')
	<script>
		'use strict';
		$(document).on('click', '.confirmButton', function (e) {
			let statusValue = $(this).val();
			let text = $(this).data('text');
			$('#confirmButton').val(statusValue);
			$('#action-text').text(text);
		});
	</script>
@endpush
