@extends('layouts.storeMaster')
@section('page_title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
@section('section')
	<div class="main-content pt-100 pb-100 publicView">
		<section class="section">
			<div class="container-fluid" id="container-wrapper">
				<div class="d-flex justify-content-center">
					<div class="card mb-4 card-primary shadow">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold card-title">{{ __('Pay with ').__(optional($deposit->gateway)->name) }}</h6>
						</div>
						<div class="card-body">
							<form
								action="{{ route('ipn', [optional($deposit->gateway)->code ?? 'mercadopago', $deposit->trx_id]) }}"
								method="POST">
								<script src="https://www.mercadopago.com.co/integrations/v1/web-payment-checkout.js"
										data-preference-id="{{ $data->preference }}">
								</script>
							</form>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection
