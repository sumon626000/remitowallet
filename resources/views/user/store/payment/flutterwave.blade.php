@extends('layouts.storeMaster')
@section('page_title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
@section('content')
	<div class="main-content">
		<div class="main-content pt-100 pb-100 publicView">
			<section class="section">
				<div class="container-fluid" id="container-wrapper">
					<div class="d-flex justify-content-center">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold card-title">{{ __('Pay with ').__(optional($deposit->gateway)->name) }}</h6>
							</div>
							<div class="card-body">
								<div class="row justify-content-center">
									<div class="col-md-3">
										<img
											src="{{getFile(config('filelocation.gateway.path').optional($deposit->gateway)->image)}}"
											class="card-img-top gateway-img" alt="..">
									</div>
									<div class="col-md-6">
										<h4 class="my-3">@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h4>
										<button type="button" class="cmn--btn" id="btn-confirm"
												onClick="payWithRave()">@lang('Pay Now')</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</div>
	</div>
@endsection
@push('script')
	<script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>
	<script>
		'use strict';
		let btn = document.querySelector("#btn-confirm");
		btn.setAttribute("type", "button");
		const API_publicKey = "{{ $data->API_publicKey }}";

		function payWithRave() {
			let x = getpaidSetup({
				PBFPubKey: API_publicKey,
				customer_email: "{{ $data->customer_email }}",
				amount: "{{ $data->amount }}",
				customer_phone: "{{ $data->customer_phone }}",
				currency: "{{ $data->currency }}",
				txref: "{{ $data->txref }}",
				onclose: function () {
				},
				callback: function (response) {
					let txref = response.tx.txRef;
					let status = response.tx.status;
					window.location = '{{ url('payment/flutterwave') }}/' + txref + '/' + status;
				}
			});
		}
	</script>
@endpush
