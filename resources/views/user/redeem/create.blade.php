@extends(userLayout())
@section('title', trans('Redeem Generate'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Services" submenu="Redeem" pagetitle="Generate New Code"/>

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-md-between">
                        <h4 class="card-header-title">@lang('Redeem Generate')</h4>
                        <x-btn.info route="user.redeem.index">@lang('History')</x-btn.info>
                    </div>
                    <form action="{{ route('user.redeem.initialize') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-6">
                                    <label for="Currency" class="form-label">@lang('Currency')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off" name="currency"
                                                id="currency"
                                                data-hs-tom-select-options='{
                                            "placeholder": "Select Currency..."
                                          }'>
                                            @if(!empty($currencies))
                                                @foreach($currencies as $key => $currency)
                                                    <option data-currency="{{ __($currency->code) }}"
                                                            data-currencytype="{{ $currency->currency_type }}"
                                                            value="{{ $currency->id }}">
                                                        {{ __($currency->code) }} - {{ __($currency->name) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('currency')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="Amount" class="form-label">@lang('Amount')</label>
                                    <x-tooltip class="amount-info d-none" />
                                    <input type="number" step="any" class="form-control" name="amount" id="amount"
                                           placeholder="@lang('0.00')"
                                           value="{{ old('amount') }}">
                                    <small class="form-text"></small>
                                    @error('amount')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                            <!-- Checkbox Switch -->
                            <div class="form-check form-switch mb-4">
                                <input type="checkbox" name="charge_from" class="form-check-input"
                                       id="invalidSwitch" value="1" checked>
                                <label class="form-check-invalid">@lang('Receiver will pay the transaction charge')
                                    <i class="bi-question-circle text-body ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="top" aria-label="Products are the goods or services you sell."
                                        data-bs-original-title="@lang('If enable transaction charge will deduct from receiver')"></i>
                                </label>
                            </div>
                            <!-- End Checkbox Switch -->
                            <div class="row">
                                <div>
                                    <label class="form-label" for="descriptionLabel">@lang('Note')</label>
                                    <textarea id="exampleFormControlTextarea1" name="note" class="form-control"
                                              placeholder="@lang("Note")" rows="4">{{ old("note") }}</textarea>
                                </div>
                                @error('note')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                                @enderror
                            </div>

                            <x-transaction-details :mobile="true" />

                            <button id="submit" type="submit" class="btn btn-primary mt-4" disabled>@lang('Generate')</button>
                        </div>
                    </form>
                </div>
            </div>

            <x-transaction-details :mobile="false" />

        </div>
    </div>
@endsection

<x-assets :flatpickr="false" />

@push('script')
    <script>
        'use strict';
        var chargeBtn = true;
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
            let amountField = $('#amount');
            let amountStatus = false;

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid')
                $(fieldId).removeClass('is-invalid')
                $(fieldId).siblings('small').html('');
            }

            $(document).on('input', "#recipient", function (e) {
                let recipient = $(recipientField).val();
                if (recipient === '') {
                    clearMessage(recipientField)
                }
                if (recipient.length >= 4) {
                    checkRecipient();
                }
            });

            $(document).on('change, input', "#amount, #charge_from, #currency,#invalidSwitch", function (e) {
                let amount = amountField.val();
                let currency_id = $('#currency').val();
                let currency_code = $('#currency option:selected').data('currency');
                let currency_type = $('#currency option:selected').data('currencytype');
                let transaction_type_id = "{{ config('transactionType.redeem') }}"; //config('transactionType.redeem')
                let charge_from = 0;
                if ($("#charge_from").is(':checked')) {
                    charge_from = 1;
                }

                if (!isNaN(amount) && amount > 0) {
                    let fraction = amount.split('.')[1];
                    let limit = currency_type == 0 ? 8 : 2;

                    if (fraction && fraction.length > limit) {
                        amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
                        amountField.val(amount);
                    }
                    checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code)
                } else {
                    clearMessage(amountField)
                    $(amountField).val(0)
                    $('.showCharge').html('')
                }
            });


            function checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code) {
                const chargeSwitch = document.getElementById('invalidSwitch');
                chargeBtn = chargeSwitch.checked;
                $.ajax({
                    method: "GET",
                    url: "{{ route('user.redeem.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'amount': amount,
                        'currency_id': currency_id,
                        'transaction_type_id': transaction_type_id,
                        'charge_from': charge_from,
                    }
                })
                    .done(function (response) {
                        let amountField = $('#amount');
                        clearMessage(amountField)
                        if (response.status) {
                            amountStatus = true;
                            $(amountField).addClass('is-valid')
                            $(amountField).siblings('small').html(response.message).removeClass('text-danger').addClass('text-success');
                            showCharge(response, currency_code)
                        } else {
                            amountStatus = false;
                            $('.showCharge').html('')
                            $(amountField).addClass('is-invalid')
                            $(amountField).siblings('small').html(response.message).removeClass('text-success').addClass('text-danger');
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

            function showCharge(response, currency_code) {

                const charge = chargeBtn ? response.charge : 0;

                const payAmount     = response.transfer_amount - charge;
                const receiveAmount = response.received_amount - charge;
                const balance       = response.remaining_balance + charge;

                let minLimit = formatAmount(response.min_limit, response.currency_limit);
                let maxLimit = formatAmount(response.max_limit, response.currency_limit);
                let tooltipContent =
                    `@lang('Min'): <b>${minLimit}</b> | @lang('Max'): <b>${maxLimit} ${currency_code}</b>`;
                updateTooltip('.amount-info', tooltipContent);

                let txnDetails = `
                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 ms-2">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="d-block">{{ __('Available Balance') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5>${response.balance} ${currency_code}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 ms-2">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="d-block">{{ __('Transfer Charge') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5 class="text-danger">${response.charge} ${currency_code}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 ms-2">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="d-block">{{ __('Payable Amount') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5>${payAmount} ${currency_code}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 ms-2">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="d-block">{{ __('Receiver will get') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5 class="text-success">${receiveAmount} ${currency_code}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="list-group-item">
                    <div class="d-flex align-items-center">
                        <div class="flex-grow-1 ms-2">
                            <div class="row align-items-center">
                                <div class="col">
                                    <span class="d-block">{{ __('Remaining Balance') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5>${balance} ${currency_code}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
			`;
                $('.showCharge').html(txnDetails);
            }
        });

    </script>
@endpush
