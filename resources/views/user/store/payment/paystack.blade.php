@extends('layouts.storeMaster')
@section('page_title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
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
									<h4 class="my-3">@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h4>
									<button type="button" class="cmn--btn" id="btn-confirm">@lang('Pay Now')</button>
									<form
										action="{{ route('ipn', [optional($deposit->gateway)->code, $deposit->trx_id]) }}"
										method="POST">
										@csrf
										<script src="//js.paystack.co/v1/inline.js"
												data-key="{{ $data->key }}"
												data-email="{{ $data->email }}"
												data-amount="{{$data->amount}}"
												data-currency="{{$data->currency}}"
												data-ref="{{ $data->ref }}"
												data-custom-button="btn-confirm">
										</script>
									</form>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection

