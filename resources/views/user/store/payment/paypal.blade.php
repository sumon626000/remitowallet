@extends('layouts.storeMaster')
@section('page_title', __('Pay with PAYPAL'))
@section('content')
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
									<h4 class="my-2">@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h4>
									<div id="paypal-button-container"></div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection
@push('script')
	<script src="https://www.paypal.com/sdk/js?client-id={{ $data->cleint_id }}"></script>
	<script>
		$(document).ready(function () {
			paypal.Buttons({
				createOrder: function (data, actions) {
					return actions.order.create({
						purchase_units: [
							{
								description: "{{ $data->description }}",
								custom_id: "{{ $data->custom_id }}",
								amount: {
									currency_code: "{{ $data->currency }}",
									value: "{{ $data->amount }}",
									breakdown: {
										item_total: {
											currency_code: "{{ $data->currency }}",
											value: "{{ $data->amount }}"
										}
									}
								}
							}
						]
					});
				},
				onApprove: function (data, actions) {
					return actions.order.capture().then(function (details) {
						var trx = "{{ $data->custom_id }}";
						window.location = '{{ url('payment/paypal') }}/' + trx + '/' + details.id
					});
				}
			}).render('#paypal-button-container');
		})
	</script>
@endpush
