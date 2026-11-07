@extends('layouts.storeMaster')
@section('page_title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
@section('content')

	<script src="https://js.stripe.com/v3/"></script>
	<style>
		.StripeElement {
			box-sizing: border-box;
			height: 40px;
			padding: 10px 12px;
			border: 1px solid transparent;
			border-radius: 4px;
			background-color: white;
			box-shadow: 0 1px 3px 0 #e6ebf1;
			-webkit-transition: box-shadow 150ms ease;
			transition: box-shadow 150ms ease;
		}

		.StripeElement--focus {
			box-shadow: 0 1px 3px 0 #cfd7df;
		}

		.StripeElement--invalid {
			border-color: #fa755a;
		}

		.StripeElement--webkit-autofill {
			background-color: #fefde5 !important;
		}
	</style>

	<div class="main-content">
		<section class="section">
			<div class="section-header">
				<h1>{{ __('Pay with ').__(optional($deposit->gateway)->name) }}</h1>
				<div class="section-header-breadcrumb">
					<div class="breadcrumb-item active">
						<a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
					</div>
					<div class="breadcrumb-item">{{ __('Pay with ').__(optional($deposit->gateway)->name) }}</div>
				</div>
			</div>
			<div class="row justify-content-center">
				<div class="col-md-5">
					<div class="card card-primary shadow">
						<div class="card-body">
							<div class="row justify-content-center">
								<div class="col-md-3">
									<img
										src="{{getFile(config('filelocation.gateway.path').optional($deposit->gateway)->image)}}"
										class="card-img-top gateway-img">
								</div>
								<div class="col-md-6">
									<h5 class="my-3">@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h5>
									<button type="button"
											class="btn btn-primary"
											id="pay-button">@lang('Pay Now')
									</button>
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
	@if($data->environment == 'test' || $deposit->mode == 1)
		<script type="text/javascript"
				src="https://app.sandbox.midtrans.com/snap/snap.js"
				data-client-key="{{ $data->client_key }}"></script>
	@else
		<script type="text/javascript"
				src="https://app.midtrans.com/snap/snap.js"
				data-client-key="{{ $data->client_key }}"></script>
	@endif

	<script defer>
		var payButton = document.getElementById('pay-button');
		payButton.addEventListener('click', function () {
			window.snap.pay("{{ $data->token }}", {
				onSuccess: function (result) {
					let route = '{{ route('ipn', ['midtrans']) }}/';
					window.location.href = route + result.order_id;
				},
				onPending: function (result) {
					let route = '{{ route('ipn', ['midtrans']) }}/';
					window.location.href = route + result.order_id;
				},
				onError: function (result) {
					window.location.href = '{{ route('failed') }}';
				},
				onClose: function () {
					window.location.href = '{{ route('failed') }}';
				}
			});
		});
	</script>
@endpush

