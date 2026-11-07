@extends(template().'layouts.app')
@section('title',__('Payment'))
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
								<form action="{{ route('public.qr.Payment', $user->qr_link) }}" method="post">
									@csrf
									<input type="hidden" name="amount" class="amount" value="0">
									<input type="hidden" name="email" class="email" value="">
									<div class="row">
										<div class="col-md-12">
											<div class="form-group">
												<div class="row payment-method-input">
													@foreach($methods as $key => $method)
														<div class="col-md-3  col-sm-3 col-4">
															<div class="form-check form-check-inline ">
																<input class="form-check-input methodId" type="radio"
																	   name="methodId" id="{{ $key }}"
																	   value="{{ $method->id }}" {{ old('methodId') == $method->id || $key == 0 ? ' checked' : ''}}>
																<label class="form-check-label" for="{{ $key }}">
																	<img
																		src="{{ getFile($method->driver,$method->image) }}">
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
							<div class="card-body qr-box">
								<div class="row">
                                    <x-tooltip class="amount-info d-none"/>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend">
												<span class="input-group-text">{{$user->qrCurrency?->symbol}}</span>
											</div>
											<input type="text" class="form-control inputAmount"
                                                   value="" id="amount" placeholder="@lang('Amount')">
										</div>
										@error('amount')
										<span class="text-danger">{{$message}}</span>
										@enderror
									</div>
									<div class="col-md-6 mb-3">
										<div class="input-group">
											<div class="input-group-prepend">
												<span class="input-group-text">@</span>
											</div>
											<input type="email" class="form-control inputEmail" value=""
												   placeholder="@lang('Email')">
										</div>
										@error('email')
										<span class="text-danger">{{$message}}</span>
										@enderror
									</div>
								</div>
								<div class="showCharge">

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
	<script>
		'use strict';
		var currencyId = '{{$user->qr_currency_id}}';
        var qrCurrSymbol = "{{optional($user->qrCurrency)->symbol}}"

		$(document).ready(function () {
			$(document).on('keyup', '.inputEmail', function () {
				$('.email').val($('.inputEmail').val());
			})

			$(document).on('keyup', '.inputAmount', function () {
				$('[data-toggle="tooltip"]').tooltip();
				let amountStatus = false;

				let amount = $(this).val();
				$('.amount').val(amount);
				$('.email').val($('.inputEmail').val());
				let currency_id = currencyId;
				let transaction_type_id = "{{ config('transactionType.deposit') }}";
				let methodId = $("input[type='radio'][name='methodId']:checked").val();
				checkAmount(amount, currency_id, transaction_type_id, methodId)
			})

			$('[data-toggle="tooltip"]').tooltip();
			let amountStatus = false;
			let amountField = 0;
			let amount = 0;
			$('.amount').val(amount);
			$('.email').val($('.inputEmail').val());
			let currency_id = currencyId;
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
			$(document).on('click', ".methodId", function (e) {

				$('[data-toggle="tooltip"]').tooltip();
				let amountStatus = false;

				let amount = $('.inputAmount').val();
				$('.amount').val(amount);
				amount = parseFloat(amount);
				$('.email').val($('.inputEmail').val());
				let currency_id = currencyId;
				let transaction_type_id = "{{ config('transactionType.deposit') }}";
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
                        var overallPay = parseFloat(response.payable_amount).toFixed(2);

                        if (response.status) {
							amountStatus = true;
							$(amountField).addClass('is-valid')
							$(amountField).closest('div').find(".valid-feedback").html(response.message)
							showCharge(response, overallPay)
						} else {
							amountStatus = false;
                            $('.showCharge').html(response.message);
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

			function showCharge(response, overallPay) {

                let minLimit = formatAmount(response.min_limit, response.currency_limit);
                let maxLimit = formatAmount(response.max_limit, response.currency_limit);
                let tooltipContent = `@lang('Min'): ${qrCurrSymbol}${minLimit} | @lang('Max'): ${qrCurrSymbol}${maxLimit} `;
                updateTooltip('.amount-info', tooltipContent);

                let txnDetails = `
					<ul class="list-group">

						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Transfer Charge') }}</span>
							<span class="text-danger"> ${qrCurrSymbol} ${response.charge}</span>
						</li>

						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Payable Amount') }}</span>
							<span class="text-info">  ${qrCurrSymbol} ${overallPay}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Receivable Amount') }}</span>
							<span class="text-info"> ${qrCurrSymbol}  ${response.amount}</span>
						</li>

					</ul>
					`
				$('.showCharge').html(txnDetails)
			}
		});
	</script>
@endpush
