@extends('layouts.storeMaster')
@section('page_title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
@push('extra_styles')
	<link href="{{ asset('assets/dashboard/css/card-js.min.css') }}" rel="stylesheet" type="text/css"/>
@endpush
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
							<form class="form-horizontal" id="example-form" action="{{ route('ipn', [optional($deposit->gateway)->code ?? '', $deposit->trx_id]) }}" method="post">
								<fieldset>
									<legend>@lang('Your Card Information')</legend>
									<div class="card-js form-group">
										<input class="card-number form-control" name="card_number" placeholder="@lang('Enter your card number')" autocomplete="off" required>
										<input class="name form-control" id="the-card-name-id" name="card_name" placeholder="@lang('Enter the name on your card')" autocomplete="off" required>
										<input class="expiry form-control" autocomplete="off" required>
										<input class="expiry-month" name="expiry_month">
										<input class="expiry-year" name="expiry_year">
										<input class="cvc form-control" name="card_cvc" autocomplete="off" required>
									</div>
									<button type="submit" class="mt-3 cmn--btn">@lang('Pay Now')</button>
								</fieldset>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection

@push('extra_scripts')
	<script src="{{ asset('assets/dashboard/js/card-js.min.js') }}"></script>
@endpush
