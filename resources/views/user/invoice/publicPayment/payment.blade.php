@extends(template().'layouts.app')
@section('title',__('Invoice Payment'))
@section('content')

	<div class="main-content pt-100 pb-100 publicView">
		<section class="section">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-4">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold card-title">@lang('Select Payment Method')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('invoice.public.payment', $invoice->has_slug) }}" method="post">
									@csrf
									<div class="row">
										<input type="hidden" name="currency" id="currency"
											   value="{{ $invoice->currency_id }}">
										<input type="hidden" name="amount" id="amount"
											   value="{{ $invoice->grand_total }}">
									</div>
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<div class="row payment-method-input">
													@foreach($methods as $key => $method)
														<div class="col-md-3  col-sm-3 col-4">
															<div class="form-check form-check-inline ">
																<input class="form-check-input methodId" type="radio"
																	   name="methodId" id="{{ $key }}" value="{{ $method->id }}"
                                                                    {{ old('methodId') == $method->id || $key == 0 ? ' checked' : ''}}>
																<label class="form-check-label" for="{{ $key }}">
																	<img src="{{ getFile($method->driver,$method->image) }}">
																</label>
															</div>
														</div>
													@endforeach
												</div>
											</div>
										</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<button type="submit" id="submit" class="mt-3 ms-lg-3 cmn--btn"
													disabled>@lang('Pay Now')</button>
										</div>
									</div>
								</form>
							</div>
						</div>
					</div>
					<div class="col-lg-4">
						<div class="card mb-4 card-primary shadow">
							<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
								<h6 class="m-0 font-weight-bold card-title">@lang('Details')</h6>
							</div>
							<div class="card-body showCharge">
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection

@push('script')
	<script>
		'use strict';
		var invoiceCharge = "{{getAmount($invoiceCharge)}}";
		$(document).ready(function () {
			$('[data-toggle="tooltip"]').tooltip();
			let amountField = $('#amount');
			let amountStatus = false;

			let amount = amountField.val();
			let currency_id = $('#currency').val();
			let transaction_type_id = "{{ config('transactionType.deposit') }}";
			let methodId = $("input[type='radio'][name='methodId']:checked").val();

			checkAmount(amount, currency_id, transaction_type_id, methodId)

			function clearMessage(fieldId) {
				$(fieldId).removeClass('is-valid')
				$(fieldId).removeClass('is-invalid')
				$(fieldId).closest('div').find(".invalid-feedback").html('');
				$(fieldId).closest('div').find(".is-valid").html('');
			}

			// for amount limit check
			$(document).on('click, change, input', "#amount, #charge_from, #currency, .methodId", function (e) {
				let methodId = $("input[type='radio'][name='methodId']:checked").val();
				if (!isNaN(amount) && amount > 0) {
					checkAmount(amount, currency_id, transaction_type_id, methodId)
				} else {
					clearMessage(amountField)
					$('.showCharge').html('')
				}
			});

			function checkAmount(amount, currency_id, transaction_type_id, methodId) {
				$.ajax({
					method: "GET",
					url: "{{ route('deposit.checkAmount') }}",
					dataType: "json",
					data: {
						'amount': amount,
						'currency_id': currency_id,
						'transaction_type_id': transaction_type_id,
						'methodId': methodId,
					}
				})
					.done(function (response) {
						let amountField = $('#amount');
                        clearMessage(amountField)
                        var overallPay = (parseFloat(response.payable_amount) + parseFloat(invoiceCharge));

						if (response.status) {
							amountStatus = true;
							$(amountField).addClass('is-valid')
							$(amountField).closest('div').find(".valid-feedback").html(response.message)
							showCharge(response, overallPay)
						} else {
							amountStatus = false;
                            $('.showCharge').html('Transaction Not Available with this method');
							$(amountField).addClass('is-invalid')
							$(amountField).closest('div').find(".invalid-feedback").html(response.message);
						}

                        submitButton()
					});
			}

			function submitButton() {
				if (amountStatus) {
					$("#submit").removeAttr("disabled");
				} else {
					$("#submit").attr("disabled", true);
				}
			}

			function showCharge(response,overallPay) {
				let txnDetails = `
					<ul class="list-group">
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Requested Amount') }}</span>
							<span> {{ (getAmount($invoice->grand_total)) }} {{ __(optional($invoice->currency)->code) }}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Transfer Charge') }}</span>
							<span class="text-danger">  ${response.charge} {{ __(optional($invoice->currency)->code) }}</span>
						</li>
						@if($invoice->charge_pay == 1)
							<li class="list-group-item d-flex justify-content-between">
								<span>{{ __('Invoice Charge') }}</span>
								<span class="text-danger"> {{ (getAmount($invoice->charge)) }} {{ __(optional($invoice->currency)->code) }}</span>
							</li>
						@endif
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Payable Amount') }}</span>
							<span class="text-info"> ${overallPay} {{ __(optional($invoice->currency)->code) }}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Received') }}</span>
							<span class="text-info"> ${response.amount} {{ __(optional($invoice->currency)->code) }}</span>
						</li>

					</ul>
					`
				$('.showCharge').html(txnDetails)
			}
		});
	</script>
@endpush
