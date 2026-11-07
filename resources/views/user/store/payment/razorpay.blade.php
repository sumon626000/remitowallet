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
									<form action="{{$data->url}}" method="{{$data->method}}">
										<script src="{{$data->checkout_js}}"
												@foreach($data->val as $key=>$value)
													data-{{$key}}="{{$value}}"
											@endforeach >
										</script>
										<input class="cmn--btn" type="hidden" custom="{{$data->custom}}" name="hidden">
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
@push('extra_scripts')
	<script>
		$(document).ready(function () {
			$('input[type="submit"]').addClass("cmn--btn");
		})
	</script>
@endpush
