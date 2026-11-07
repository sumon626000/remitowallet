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
										<button class="mt-3 cmn--btn"
												onclick="payWithMonnify()">@lang('Pay Now')</button>
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
	<script type="text/javascript" src="//sdk.monnify.com/plugin/monnify.js"></script>
	<script type="text/javascript">
		'use strict';

		function payWithMonnify() {
			MonnifySDK.initialize({
				amount: {{ $data->amount }},
				currency: "{{ $data->currency }}",
				reference: "{{ $data->ref }}",
				customerName: "{{$data->customer_name }}",
				customerEmail: "{{$data->customer_email }}",
				customerMobileNumber: "{{ $data->customer_phone }}",
				apiKey: "{{ $data->api_key }}",
				contractCode: "{{ $data->contract_code }}",
				paymentDescription: "{{ $data->description }}",
				isTestMode: true,
				onComplete: function (response) {
					if (response.paymentReference) {
						window.location.href = '{{ route('ipn', ['monnify', $data->ref]) }}';
					} else {
						window.location.href = '{{ route('failed') }}';
					}
				},
				onClose: function (data) {
				}
			});
		}
	</script>
@endpush
