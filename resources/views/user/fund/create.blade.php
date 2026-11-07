@extends(userLayout())
@section('title',__('Add Fund'))

@section('content')

    <div class="content {{ containerClass() }}">

        <x-page-header
            :menu="$from == 'card' ? 'Virtual Card' : 'Services'"
            :pagetitle="$from == 'card' ? 'Add Fund Virtual Card' : null"
        />
        <form action="{{ route('fund.initialize') }}" method="post">
            @csrf
            <div class="row g-4">
                <div class="col-lg-7 col-md-6">
                    <div class="card gateways">
                        <div class="card-header card-header-content-md-between">
                            <h4 class="card-header-title ">@lang('Your preferred payment method?')</h4>
                            <x-btn.info route="user.fund.index"> @lang('History') </x-btn.info>

                        </div>
                        <div class="card-body pt-0">

                            <div class="payment-section">
                                <ul class="payment-container-list">
                                    @foreach($methods as $key => $method)
                                        <li class="item">
                                            <input type="radio" class="form-check-input methodId"
                                                   name="methodId"
                                                   id="{{$key}}"
                                                   value="{{ $method->id }}"
                                                   {{ old('methodId') == $method->id || $key == 0 ? ' checked' : ''}}
                                                   autocomplete="off"/>
                                            <label class="form-check-label" for="{{ $key }}">
                                                <div class="image-area">
                                                    <img src="{{ $method->gatewayImg() }}" alt="...">
                                                </div>
                                                <div class="content-area">
                                                    <h5>{{ $method->name }}</h5>
                                                    <span>{{ $method->description }}</span>
                                                </div>
                                            </label>
                                        </li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn btn-primary w-100 d-block d-md-none"
                                        id="showGatewaysButton">
                                    {{ trans('Select Payment Gateway') }}
                                </button>
                            </div>

                        </div>
                    </div>
                </div>


                <div class="col-lg-5 col-md-6">
                    <div class="card">
                        <div class="card-body">

                            <input type="hidden" name="orderCardId" value="{{$id}}">
                            <input type="hidden" name="receive_currency" value="{{request()->receive_currency}}">
                            @if($from == 'card')
                                <div>
                                    <label for="editCardNameLabel" class="form-label">@lang('Fund Amount')</label>
                                    <div class="input-group mb-3">
                                    <span class="input-group-text"
                                          id="basic-addon1">{{basicControl()->currency_symbol}}</span>
                                        <input type="text" id="amount" class="form-control" name="amount"
                                               value="{{request()->cardFundAmount}}"
                                               aria-label="Username"
                                               aria-describedby="basic-addon1" readonly>
                                        <span class="invalid-feedback">@error('amount') @lang($message) @enderror</span>
                                    </div>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-sm mb-3">
                                    <label class="form-label mt-3"
                                           for="supported_currency">{{ trans('Payment Currency') }}
                                        <span data-bs-toggle="tooltip" data-bs-placement="top"
                                              data-bs-title="Please select the payment currency">
                                            <i class="fa-regular fa-circle-question"></i></span>
                                    </label>

                                    <div class="tom-select-custom">
                                        <select class="form-select"
                                                id="supported_currency" name="currency"
                                        >
                                            <option value="''" selected disabled>@lang('Select currency')</option>

                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="invalid-feedback">
                                @error('currency') @lang($message) @enderror
                            </div>

                            @if($from != 'card')
                                <x-tooltip class="amount-info d-none"/>
                                <input class="form-control @error('amount') is-invalid @enderror"
                                       name="amount" type="text" id="amount"
                                       value="{{ old('amount') }}"
                                       placeholder="Enter Amount" autocomplete="off"
                                       onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')"
                                />
                                <span class="invalid-feedback">@error('amount') @lang($message) @enderror</span>
                            @endif

                            <div class="side-box mt-3 mb-3">
                                <div class="showCharge">

                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm btn-block w-100 submitBtn mt-3"
                                    disabled>
                                {{ trans('Confirm') }}
                            </button>
                            <a href="{{ route('user.dashboard') }}"
                               class="btn btn-soft-danger mt-2 w-100">{{ trans('Cancel') }}</a>

                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal for Payment Gateways (for mobile) -->
            <div class="modal fade" id="gatewayModal" tabindex="-1" aria-labelledby="gatewayModalLabel"
                 aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="gatewayModalLabel">{{ trans('Select a Payment Gateway') }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <ul class="payment-container-list d-lg-none d-block">
                                @foreach($methods as $key => $method)
                                    <li class="item">
                                        <input type="radio" class="form-check-input methodId"
                                               name="methodId" id="modal-{{ $method->name }}"
                                               value="{{ $method->id }}"
                                               autocomplete="off"/>
                                        <label class="form-check-label" for="modal-{{ $method->name }}">
                                            <div class="image-area">
                                                <img src="{{ getFile($method->driver, $method->image) }}" alt="...">
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

<x-assets :flatpickr="false"/>

@push('script')
    <script>
        'use strict'
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
            let amountField = $('#amount');
            let amountStatus = false;
            var isBase = {{ $from == 'card' ? 'true' : 'false' }};

            $('#showGatewaysButton').on('click', function () {
                $('#gatewayModal').modal('show');
            });

            let methodId = $("input[type='radio'][name='methodId']:checked").val()
            supportCurrency(methodId);

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid')
                $(fieldId).removeClass('is-invalid')
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            $(document).on("keyup", "#amount", function () {
                let amount = amountField.val();
                let transaction_type_id = "{{ config('transactionType.deposit') }}"; //Deposit
                let methodId = $("input[type='radio'][name='methodId']:checked").val()
                let selectedCurrency = $('#supported_currency').val();
                if (!isNaN(amount) && amount > 0) {
                    checkAmount(amount, transaction_type_id, methodId, selectedCurrency)
                } else {
                    clearMessage(amountField)
                    $('.showCharge').html('')
                }
            });

            $(document).on('change', '#supported_currency', function (e) {
                let amount = amountField.val();
                let transaction_type_id = "{{ config('transactionType.deposit') }}"; //Deposit
                let methodId = $("input[type='radio'][name='methodId']:checked").val()
                let selectedCurrency = $('#supported_currency').val();

                if (!isNaN(amount) && amount > 0) {
                    checkAmount(amount, transaction_type_id, methodId, selectedCurrency)
                } else {
                    clearMessage(amountField)
                    $('.showCharge').html('')
                }
            });

            $(document).on('change', '.methodId', function (e) {
                $('#gatewayModal').modal('hide');

                let amount = amountField.val();
                let transaction_type_id = "{{ config('transactionType.deposit') }}"; //Deposit
                let methodId = $("input[type='radio'][name='methodId']:checked").val()
                let selectedCurrency = $('#supported_currency').val();

                supportCurrency(methodId);

                if (!isNaN(amount) && amount > 0) {
                    checkAmount(amount, transaction_type_id, methodId, selectedCurrency)
                } else {
                    clearMessage(amountField)
                    $('.showCharge').html('')
                }
            });

            function supportCurrency(selectedGateway) {
                if (!selectedGateway) {
                    console.error('Selected Gateway is undefined or null.');
                    return;
                }
                $('#supported_currency').empty();
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });

                $.ajax({
                    url: "{{ route('supported.currency') }}",
                    data: {gateway: selectedGateway},
                    type: "GET",
                    success: function (data) {
                        let res = data.data;

                        // 🔹 Clear previous options except the placeholder
                        $('#supported_currency').find('option:not(:disabled)').remove();

                        // 🔹 Append new options
                        $(res).each(function (index, value) {
                            let markup = `<option value="${value}">${value}</option>`;
                            $('#supported_currency').append(markup);
                        });

                        let amount = amountField.val();
                        let transaction_type_id = "{{ config('transactionType.deposit') }}"; //Deposit
                        let methodId = $("input[type='radio'][name='methodId']:checked").val()
                        let selectedCurrency = $('#supported_currency').val();

                        if (!isNaN(amount) && amount > 0) {
                            checkAmount(amount, transaction_type_id, methodId, selectedCurrency)
                        } else {
                            clearMessage(amountField)
                            $('.showCharge').html('')
                        }

                    },
                    error: function (error) {
                        console.error('AJAX Error:', error);
                    }
                });
            }


            function checkAmount(amount, transaction_type_id, methodId, currency_id) {
                $.getJSON("{{ route('deposit.checkAmount') }}", {
                    amount, currency_id, transaction_type_id, methodId, isBase
                })
                    .done(function (response) {
                        const amountField = $('#amount');
                        clearMessage(amountField);

                        const minLimit = formatAmount(response.min_limit, response.currency_limit);
                        const maxLimit = formatAmount(response.max_limit, response.currency_limit);
                        const tooltipContent = `@lang('Min'): <b>${minLimit}</b> | @lang('Max'): <b>${maxLimit} ${response.currency_code}</b>`;
                        updateTooltip('.amount-info', tooltipContent);

                        if (response.status) {
                            amountField.addClass('is-valid').removeClass('is-invalid');
                            amountField.siblings('.valid-feedback').html(response.message);
                            amountStatus = true;
                            showCharge(response);
                        } else {
                            amountField.addClass('is-invalid').removeClass('is-valid');
                            amountField.siblings('.invalid-feedback').html(response.message);
                            amountStatus = false;
                            $('.showCharge').html('');
                        }
                        submitButton();
                    });
            }

            function submitButton() {
                if (amountStatus) {
                    $('.submitBtn').prop('disabled', false);
                } else {
                    $('.submitBtn').prop('disabled', true);
                }
            }

            function showCharge(response) {
                let txnDetails = `
					<ul class="list-group">
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Transfer Charge') }}</span>
							<span class="text-danger">  ${response.charge} ${response.currency_code}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Payable Amount') }}</span>
							<span class="text-info"> ${response.payable_amount} ${response.currency_code}</span>
						</li>
						<li class="list-group-item d-flex justify-content-between">
							<span>{{ __('Receiveable Amount') }}</span>
							<span class="text-info"> ${response.amount} ${response.currency_code}</span>
						</li>
					</ul>
					`;
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
