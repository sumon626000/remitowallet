@extends('layouts.storeMaster')
@section('page_title', __('Checkout'))
@section('content')
	<!-- checkout section -->
	<section class="checkout-section">
		<div class="container">
			<div class="row g-4 g-lg-5">
				<div class="col-lg-8">
					<h4>@lang('Checkout Info')</h4>
					<form action="">
						<div class="row g-4">
							<div class="input-box col-md-12">
								<label for="">@lang('Full Name')</label>
								<input class="form-control name" name="name" type="text" value="{{old('name')}}"
									   placeholder="" required/>
								<span class="text-danger nameError"></span>
							</div>
							<div class="input-box col-md-6">
								<label for="">@lang('Email')</label>
								<input class="form-control email" name="email" type="email" value="{{old('email')}}"
									   placeholder="" required/>
								<span class="text-danger emailError"></span>
							</div>
							<div class="input-box col-md-6">
								<label for="">@lang('Phone')</label>
								<input class="form-control phone" name="phone" type="text" value="{{old('phone')}}"
									   placeholder="" required/>
								<span class="text-danger phoneError"></span>
							</div>
							<div class="input-box col-md-6">
								<label for="">@lang('Alt. Phone (Optional)')</label>
								<input class="form-control altPhone" name="altPhone" type="text"
									   value="{{old('altPhone')}}"
									   placeholder=""/>
							</div>
							<div class="input-box col-md-6">
								<label for="">@lang('Select City')</label>
								<select class="form-select shipping" name="shippingId"
										aria-label="Default select example" required>
									<option selected="" disabled>@lang('Select City')</option>
									@forelse($shippingAdds as $address)
										<option value="{{$address->id}}"
												{{old('shippingId') == $address->id ? 'selected':''}}
												data-charge="{{$address->charge}}">{{$address->address}}</option>
									@empty
									@endforelse
								</select>
								<span class="text-danger shippingError"></span>
							</div>
							<div class="input-box col-md-12">
								<label for="">@lang('Detailed Address')</label>
								<input class="form-control detailAddress" name="detailAddress" type="text"
									   value="{{old('detailAddress')}}" placeholder="" required/>
								<span class="text-danger detailAddressError"></span>
							</div>

							<div class="col-md-12">
								<h4>@lang('Payment Method')</h4>
								<div class="payment-box mb-4">
									<div class="payment-options">
										<div class="row g-2">
											@forelse($gateways as $key => $gateway)
												<div class="col-4 col-sm-3 col-md-2">
													<input type="radio" class="btn-check gatewayClick"
														name="methodId" value="{{$gateway->id}}"
														id="pay_opt_{{$key}}"
														autocomplete="off" {{$key == 0 ?'checked':''}}/>
													<label class="btn btn-primary" for="pay_opt_{{$key}}">
														<img class="img-fluid" src="{{ $gateway->gatewayImg() }}"
															 alt="{{$gateway->name}}"/>
														<img src="{{asset('assets/store/img/icon/check.png')}}"
															 alt="..." class="check"/>
                                                    </label>
												</div>
											@empty
											@endforelse
										</div>
									</div>
									<span class="text-danger methodError"></span>
								</div>
							</div>
							<div class="input-box col-md-12">
								<div class="form-check">
									<input class="form-check-input" name="termAgree" type="checkbox" value=""
										   id="tandc" required/>
									<label class="form-check-label" for="tandc">
										@lang('I agree to Terms & Conditions, Refund Policy and Privacy Policy of '){{ basicControl()->site_title }}
									</label>
								</div>
							</div>
							@if($store->delivery_note == 'enable')
								<div class="input-box col-12">
									<label for="">@lang('Order Notes (Optional)')</label>
									<textarea class="form-control orderNote"
										placeholder="@lang('Note about your order, eg special notes for delivery.')"
										cols="30" rows="3" name="orderNote">{{old('orderNote')}}</textarea>
								</div>
							@endif
							<div class="input-box col-12">
								<button type="button" class="btn-custom w-100" id="submit">@lang('place order')</button>
							</div>
						</div>
					</form>
				</div>
				<div class="col-lg-4">
					<div class="side-bar">
						<div class="side-box">
							<h4>@lang('Checkout Summary')</h4>
                            <label class="form-label mt-3" for="currency">
                                @lang('Select Currency')
                                <span data-bs-toggle="tooltip" data-bs-placement="top" data-bs-title="Kindly choose the currency through which you'd like to payment using the gateway.">
                                            <i class="fa-regular fa-circle-question"></i></span>
                            </label>
                            <select class="form-select" id="currency" name="currency">
                                <option selected disabled>{{ trans('Select a supported currency') }}</option>
                            </select>
                            <span class="invalid-feedback d-block"></span>

                            <ul class="mt-3">
								<li>@lang('Subtotal') <span class="subtotal"></span></li>
								@if($store->shipping_charge == 1)
									<li>@lang('Shipping') <span class="shippingCharge">0</span></li>
								@endif
								<li>@lang('Transfer Charge') <span class="transferCharge">0</span></li>
								<li>@lang('Payable Total') <span class="payable">0</span></li>

							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>
@endsection

@push('extra_scripts')
	<script>
		'use strict'
		var cartItem = [];
		var subtotal = 0;
		var name = '', email = '', phone = '', altPhone = '', shippingId = '', detailAddress = '',
			methodId = '{{$gateways[0]->id}}',
			orderNote = ''
		var shippingCharge = 0, amountStatus = '', shippingChargeStatus = '{{$store->shipping_charge}}'
		var currencySymbol = '{{currencySymbol($link)}}';
		var currencyId = $('#currency').val();
        var transaction_type_id = "{{ config('transactionType.deposit') }}";

		cartItem = JSON.parse(sessionStorage.getItem('shoppingCart'))
		$.each(cartItem, function (key, value) {
			var obj = value
			subtotal += obj.price * obj.count;
		});
		subtotal = subtotal.toFixed(2);
		$('.subtotal').text(`${currencySymbol}${subtotal}`);
		$('.payable').text(`${currencySymbol}${subtotal}`);
        supportCurrency(methodId);

		$(document).on('change', '.shipping', function () {
			shippingCharge = $(this).find(':selected').attr('data-charge');
			shippingCharge = parseFloat(shippingCharge).toFixed(2)
			$('.shippingCharge').text(`${currencySymbol}${shippingCharge}`);
			if (shippingChargeStatus == 0) {
				shippingCharge = 0;
			}
			triggerChargeCalculation();
		})

        $(document).on('change', '#currency', function () {
            currencyId = $(this).val();
            triggerChargeCalculation();
        })

        $(document).on('change', '.gatewayClick', function () {
            let selectedMethodId = $("input[type='radio'][name='methodId']:checked").val();
            supportCurrency(selectedMethodId);
        })

        function triggerChargeCalculation() {
            currencyId = $('#currency').val();
            let amount = parseFloat(subtotal) + parseFloat(shippingCharge);
            let methodId = $("input[type='radio'][name='methodId']:checked").val();

            if (!currencyId || !methodId) {
                amountStatus = false;
                submitButton();
                return;
            }

            checkAmount(amount, currencyId, transaction_type_id, methodId)
        }

        function supportCurrency(selectedGateway) {
            if (!selectedGateway) {
                return;
            }

            const currencyField = $('#currency');
            currencyField.empty().append(`<option selected disabled>{{ trans('Select a supported currency') }}</option>`);

            $.ajax({
                url: "{{ route('supported.currency') }}",
                data: {gateway: selectedGateway},
                type: "GET",
                success: function (data) {
                    const supportedCurrencies = data.data || [];

                    $(supportedCurrencies).each(function (index, code) {
                        currencyField.append(`<option value="${code}">${code}</option>`);
                    });

                    if (supportedCurrencies.length) {
                        currencyField.val(supportedCurrencies[0]);
                        currencyId = supportedCurrencies[0];
                        triggerChargeCalculation();
                    } else {
                        currencyId = null;
                        amountStatus = false;
                        submitButton();
                        $('.transferCharge').text('0');
                        $('.payable').text(`${currencySymbol}${subtotal}`);
                        $('.invalid-feedback').removeClass('d-none').text("{{ trans('No supported currency found for this payment method.') }}");
                    }
                }
            });
        }

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
					let amountField = subtotal;
					if (response.status) {
						amountStatus = true;
						submitButton();
						$('.transferCharge').text(`${response.percentage_charge} + ${response.fixed_charge} = ${currencySymbol}${response.charge}`);
						$('.payable').text(`${currencySymbol}${response.payable_amount}`)
                        $('.invalid-feedback').addClass('d-none');
					} else {
						amountStatus = false;
						submitButton();
						$('.transferCharge').text(`${response.percentage_charge} + ${response.fixed_charge} = ${currencySymbol}${response.charge}`);
						$('.payable').text(`${currencySymbol}${response.payable_amount}`)
						$('.invalid-feedback').removeClass('d-none').text(`${response.message}`)
					}
				});
		}

		function submitButton() {
			if (amountStatus) {
				$("#submit").removeAttr("disabled");
			} else {
				$("#submit").attr("disabled", true);
			}
		}

		$(document).on('click', '#submit', function () {
			name = $('.name').val();
			email = $('.email').val();
			phone = $('.phone').val();
			altPhone = $('.altPhone').val();
			shippingId = $('.shipping').val();
			detailAddress = $('.detailAddress').val();
			methodId = $("input[type='radio'][name='methodId']:checked").val();
			orderNote = $('.orderNote').val();
			cartItem = JSON.parse(sessionStorage.getItem('shoppingCart'));

			validation(name, email, phone, shippingId, detailAddress, methodId);
			formSubmit(name, email, phone, altPhone, shippingId, detailAddress, methodId, orderNote, cartItem);
		})

		function formSubmit(name = null, email = null, phone = null, altPhone = null, shippingId = null, detailAddress = null, methodId = null, orderNote = null, cartItem = null) {
			$.ajax({
				method: "POST",
				url: "{{ route('public.checkout.store') }}",
				dataType: "json",
				data: {
					'name': name,
					'email': email,
					'phone': phone,
					'altPhone': altPhone,
					'shippingId': shippingId,
					'detailAddress': detailAddress,
					'methodId': methodId,
					'orderNote': orderNote,
					'cartItem': cartItem,
					'currency_id': currencyId,
				}
			})
				.done(function (response) {
					if (response.status == 'success') {
						window.location.href = response.route
					}
					if (response.status == 'fail') {
						// Notiflix.Notify.failure("Payment is not available");
						Notiflix.Notify.failure(response.message);
					}
					if (response.status == 'stockOut') {
						Notiflix.Notify.failure(response.product_name + "has been stock out");
					}
					if (response.status == 'emptyCart') {
						Notiflix.Notify.failure("Please Select product first");
					}
				});
		}

		function validation(name = null, email = null, phone = null, shippingId = null, detailAddress = null, methodId = null) {
			$('.nameError').text('');
			$('.emailError').text('');
			$('.phoneError').text('');
			$('.shippingError').text('');
			$('.detailAddressError').text('');
			$('.methodError').text('');
			if (!name) {
				$('.nameError').text('Name field is required');
			}
			if (!email) {
				$('.emailError').text('Email field is required');
			}
			if (!phone) {
				$('.phoneError').text('Phone field is required');
			}
			if (!shippingId) {
				$('.shippingError').text('Shipping Address is required');
			}
			if (!detailAddress) {
				$('.detailAddressError').text('Detail Address field is required');
			}
			if (!methodId) {
				$('.methodError').text('Payment Method is required');
			}
		}
	</script>
	@if ($errors->any())
		@php
			$collection = collect($errors->all());
			$errors = $collection->unique();
		@endphp
		<script>
			"use strict";
			@foreach ($errors as $error)
			Notiflix.Notify.failure("{{ trans($error) }}");
			@endforeach
		</script>
	@endif
@endpush
