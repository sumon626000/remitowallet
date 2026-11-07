@extends(userLayout())
@section('title',trans('Payout'))
@section('content')

    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Payout')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Payout Request')</h1>
                </div>
            </div>
        </div>


        <form action="{{ userRoute('payout.request') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row g-4">
                <div class="col-lg-7 col-md-6">
                    <div class="card gateways">
                        <div class="card-header card-header-content-md-between">
                            <h4 class="card-header-title">@lang('Your preferred payout method?')</h4>
                            <x-btn.info route="user.payout.index">@lang('History')</x-btn.info>
                        </div>
                        <div class="card-body pt-0">

                            <div class="payment-section">
                                <ul class="payment-container-list">
                                    @foreach($payoutMethod as $key => $method)
                                        <li class="item">
                                            <input type="radio" class="form-check-input selectPayoutMethod"
                                                   name="payout_method_id"
                                                   id="{{ $method->name }}"
                                                   value="{{ $method->id }}"
                                                   autocomplete="off"/>
                                            <label class="form-check-label" for="{{ $method->name }}">
                                                <div class="image-area">
                                                    <img src="{{ getFile($method->driver, $method->logo) }}" alt="">
                                                </div>
                                                <div class="content-area">
                                                    <h5>{{ $method->name }}</h5>
                                                    <span>{{ $method->description }}</span>
                                                </div>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn btn-primary w-100 d-block d-md-none" id="showGatewaysButton">
                                    {{ trans('Select Payout Method') }}
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                <div class="col-lg-5 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <label class="form-label mt-3" for="supported_currency">{{ trans('Select Currency') }}
                                <span data-bs-toggle="tooltip" data-bs-placement="top"
                                      data-bs-title="Kindly choose the currency through which you'd like to payout using the gateway.">
                                            <i class="fa-regular fa-circle-question"></i></span>
                            </label>
                            <select class="cmn-select2 " name="supported_currency" id="supported_currency">
                                <option value="" selected
                                        disabled>{{ trans('Select a payout method first') }}</option>
                            </select>

                            <label class="form-label mt-3" for="">{{ trans('Enter Amount') }}</label>
                            <input class="form-control @error('amount') is-invalid @enderror"
                                   name="amount" type="text" id="amount"
                                   placeholder="Enter Amount" autocomplete="off"
                                   onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                            />
                            <span class="invalid-feedback">@error('amount') @lang($message) @enderror</span>
                            <span class="valid-feedback"></span>


                            <div class="side-box mt-3 mb-3">
                                <div class="showCharge">

                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100 submitBtn mt-3" disabled>
                                {{ trans('Payout') }}
                            </button>

                            <a href="{{ route('user.dashboard') }}" class="btn btn-soft-danger mt-2 w-100">{{ trans('Cancel') }}</a>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Payout Gateways (for mobile) -->
            <div class="modal fade" id="gatewayModal" tabindex="-1" aria-labelledby="gatewayModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="gatewayModalLabel">{{ trans('Select a Payout Gateway') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="payment-container-list d-lg-none d-block">
                                @foreach($payoutMethod as $key => $method)
                                    <li class="item">
                                        <input type="radio" class="form-check-input selectPayoutMethod"
                                               name="payout_method_id" value="{{ $method->id }}"
                                               id="modal-{{ $method->id }}"
                                               autocomplete="off"/>
                                        <label class="form-check-label" for="modal-{{ $method->id }}">
                                            <div class="image-area">
                                                <img src="{{ getFile($method->driver, $method->logo) }}" alt="">
                                            </div>
                                            <div class="content-area">
                                                <h5>{{ $method->name }}</h5>
                                                <span>{{ $method->description }}</span>
                                            </div>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

        </form>
    </div>

@endsection

@push('script')
    <script>
        'use strict';

        $('#showGatewaysButton').on('click', function () {
            $('#gatewayModal').modal('show');
        });

        function emptyInput() {
            let amountField = $('#amount');
            amountField.val('');
            $('.submitBtn').prop('disabled', true);
            $('.showCharge').html('');
            $(amountField).addClass('is-invalid');
            $(amountField).closest('div').find(".valid-feedback").html('');
            $(amountField).closest('div').find(".invalid-feedback").html('Enter your amount');
        }

        $(document).ready(function () {
            let amountField = $('#amount');
            let btnStatus = false;
            let selectedPayoutMethod = "";
            let base_currency = "{{basicControl()->base_currency}}"

            // HSCore.components.HSTomSelect.init('.js-select')
            $('.cmn-select2').select2();


            $(document).on('click', '.selectPayoutMethod', function () {
                let id = this.id;
                $('#gatewayModal').modal('hide');

                selectedPayoutMethod = $(this).val();
                supportCurrency(selectedPayoutMethod);
            });

            function supportCurrency(selectedPayoutMethod) {
                if (!selectedPayoutMethod) {
                    console.error('Selected Gateway is undefined or null.');
                    return;
                }
                const $supportedCurrency = $('#supported_currency');
                $supportedCurrency.empty();

                $.ajax({
                    url: "{{ userRoute('payout.supported.currency') }}",
                    data: {gateway: selectedPayoutMethod},
                    type: "GET",
                    success: function (data) {
                        if (Array.isArray(data) && data.length > 0) {
                            $supportedCurrency.append('<option value="" selected >{{ trans('Choose Currency') }}</option>');
                            data.forEach(function (currency) {
                                $supportedCurrency.append(`<option value="${currency}">${currency}</option>`);
                            });
                            $supportedCurrency.val(data[0]).trigger('change');
                        } else {
                            $supportedCurrency.append('<option value="USD" selected>USD</option>');
                        }
                    },
                    error: function (error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }

            $(document).on('change input', "#amount, #supported_currency, .selectPayoutMethod", function (e) {
                let amount = amountField.val();
                let selectedCurrency = $('#supported_currency').val();
                let currency_type = 1;

                if (!isNaN(amount) && amount > 0) {
                    let fraction = amount.split('.')[1];
                    let limit = currency_type == 0 ? 8 : 2;

                    if (fraction && fraction.length > limit) {
                        amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
                        amountField.val(amount);
                    }
                    checkAmount(amount, selectedCurrency, selectedPayoutMethod)
                } else {
                    clearMessage(amountField)
                    $('.showCharge').html('')
                }
            });

            function checkAmount(amount, selectedCurrency, selectedPayoutMethod) {
                $.ajax({
                    method: "GET",
                    url: "{{ userRoute('payout.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'amount': amount,
                        'selected_currency': selectedCurrency,
                        'selected_payout_method': selectedPayoutMethod,
                    }
                }).done(function (response) {
                    let amountField = $('#amount');
                    clearMessage(amountField);

                    if (response.status) {
                        btnStatus = true;
                        $(amountField).addClass('is-valid');
                        $(amountField).closest('div').find(".valid-feedback").html(response.message);
                        showCharge(response, base_currency);
                    } else {
                        btnStatus = false;
                        $(amountField).addClass('is-invalid');
                        $(amountField).closest('div').find(".invalid-feedback").html(response.message);
                        $('.showCharge').html('');
                    }

                    submitButton();
                });
            }

            function submitButton() {
                if (btnStatus) {
                    $('.submitBtn').prop('disabled', false);
                } else {
                    $('.submitBtn').prop('disabled', true);
                }
            }

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid')
                $(fieldId).removeClass('is-invalid')
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            function showCharge(response, currency) {
                let txnDetails = `<ul class="list-group">
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Amount In') }} ${response.currency} </span>
							<span class="text-success"> ${response.amount} ${response.currency}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Charge') }}</span>
							<span class="text-danger">  ${response.charge} ${response.currency}</span>
						</li>

						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Payout Amount') }}</span>
							<span class="text-info"> ${response.net_payout_amount} ${response.currency}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Exchange Rate') }}</span>
							<span class="text-info"> 1 ${currency} <i class="bi bi-arrow-left-right"></i>  ${response.conversion_rate} ${response.currency}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Payout Amount') }} <sub>(In Base Currency)</sub></span>
							<span class="text-info"> ${response.net_amount_in_base_currency} ${currency}</span>
						</li>
					</ul>`;
                $('.showCharge').html(txnDetails)
            }

        });

    </script>
@endpush


@push('style')
    <style>
        @media (max-width: 767px) {
            .payment-container-list {
                display: none;
            }
        }

        /* Show gateway list on desktop */
        @media (min-width: 768px) {
            #showGatewaysButton {
                display: none;
            }
        }
    </style>
@endpush




