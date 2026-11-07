@extends(userLayout())
@section('title',__('Voucher Create'))

@section('content')

    <div class="content {{ containerClass() }}">
        <x-page-header menu="Services" />

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-md-between">
                        <h4 class="card-header-title">@lang('Voucher Create')</h4>
                        <x-btn.info route="user.voucher.index">@lang('History')</x-btn.info>
                    </div>
                    <form action="{{ route('user.voucher.createRequest') }}" method="post">
                        @csrf
                        <div class="card-body">

                            <div class=" mb-3">
                                <label class="form-label" for="recipient">@lang('Recipient email') {{ basicControl()->allowUser ? __('or username') : '' }}</label>
                                <input type="text" name="recipient" id="recipient"
                                       autocomplete="off" value="{{ old('recipient') }}"
                                       placeholder="@lang('Please enter valid email') {{ basicControl()->allowUser ? __('or username') : ''}}"
                                       class="form-control @error('recipient') is-invalid @enderror">
                                <div class="invalid-feedback">
                                    @error('recipient') @lang($message) @enderror
                                </div>
                                <div class="valid-feedback"></div>
                            </div>

                            <div class="row mb-3 g-3">
                                <div class="col-md-6">
                                    <label for="currency" class="form-label">@lang('Currency')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off" id="currency"
                                                name="currency" required
                                                data-hs-tom-select-options='{ "placeholder": "Select Currency..." }'
                                        >
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
                                    <div class="form-group">
                                        <label class="form-label" for="amount">@lang('Amount')</label>
                                        <x-tooltip class="amount-info d-none" />
                                        <input type="text" id="amount" value="{{ old('amount') }}"
                                               name="amount"
                                               placeholder="@lang('0.00')"
                                               class="form-control @error('amount') is-invalid @enderror"
                                               autocomplete="off">
                                        <div class="invalid-feedback">
                                            @error('amount') @lang($message) @enderror
                                        </div>
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="note">@lang('Note')</label>
                                <textarea name="note" rows="5" class="form-control form-control-sm"></textarea>
                            </div>

                            <x-transaction-details :mobile="true" />

                            <button type="submit" id="submit" class="btn btn-primary btn-sm btn-block mt-3"
                                    disabled>@lang('Create voucher')
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <x-transaction-details :mobile="false" />

        </div>
    </div>

@endsection

<x-assets :flatpickr="false"/>

@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            let recipientField = $('#recipient');
            let amountField = $('#amount');
            let recipientStatus = false;
            let amountStatus = false;

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid');
                $(fieldId).removeClass('is-invalid');
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            //for receiver check
            $(document).on('input', "#recipient", function (e) {
                let recipient = $(recipientField).val();
                if (recipient === '') {
                    clearMessage(recipientField)
                }

                if (!'{{ basicControl()->allowUser }}') {
                    if (isEmail(recipient)) {
                        if (recipient.length >= 3) {
                            checkRecipient();
                        }
                    } else {
                        clearMessage(recipientField)
                    }
                } else if (recipient.length >= 3) {
                    checkRecipient();
                } else {
                    clearMessage(recipientField)
                }


            });

            function isEmail(email) {
                let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }

            function checkRecipient() {
                $.ajax({
                    method: "GET",
                    url: "{{ route('user.voucher.checkRecipient') }}",
                    dataType: "json",
                    data: {
                        'recipient': recipientField.val()
                    }
                })
                    .done(function (response) {
                        clearMessage(recipientField)
                        if (response.status) {
                            recipientStatus = true;
                            $(recipientField).addClass('is-valid')
                            $(recipientField).closest('div').find(".valid-feedback").html(response.message)
                        } else {
                            recipientStatus = false;
                            $(recipientField).addClass('is-invalid')
                            $(recipientField).closest('div').find(".invalid-feedback").html(response.message);
                        }
                        submitButton()
                    });
            }

            //for amount limit check
            $(document).on('change, input', "#amount, #charge_from, #currency", function (e) {
                let amount = amountField.val();
                let currency_id = $('#currency').val();
                let transaction_type_id = "{{ config('transactionType.voucher') }}"; // 6=voucher
                let charge_from = 1;
                let currency_code = $('#currency option:selected').data('currency');
                let currency_type = $('#currency option:selected').data('currencytype');
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
                    clearMessage(amountField);
                    $(amountField).val(0)
                    $('.showCharge').html('');
                }
            });

            function checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('user.voucher.checkInitiateAmount') }}",
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
                            $(amountField).closest('div').find(".valid-feedback").html(response.message)
                            showCharge(response, currency_code)
                        } else {
                            amountStatus = false;
                            $(amountField).addClass('is-invalid')
                            $(amountField).closest('div').find(".invalid-feedback").html(response.message);
                            $('.showCharge').html('');
                        }
                        submitButton()
                    });
            }

            function submitButton() {
                if (recipientStatus && amountStatus) {
                    $("#submit").removeAttr("disabled");
                } else {
                    $("#submit").attr("disabled", true);
                }
            }

            function showCharge(response, currency_code) {

                let minLimit = formatAmount(response.min_limit, response.currency_limit);
                let maxLimit = formatAmount(response.max_limit, response.currency_limit);
                let tooltipContent = `@lang('Min'): <b>${minLimit}</b> | @lang('Max'): <b>${maxLimit} ${currency_code}</b>`;
                updateTooltip('.amount-info', tooltipContent);

                let txnDetails = `
                    <x-list-item label="Available Balance" value="${response.balance} ${currency_code}" class="text-info fw-semibold"/>
                    <x-list-item label="Transfer Charge" value="${response.charge} ${currency_code}" class="text-danger"/>
                    <x-list-item label="Request Amount" value="${response.transfer_amount} ${currency_code}" class=""/>
                    <x-list-item label="Receivable Amount" value="${response.received_amount} ${currency_code}" class="text-success"/>
                `
                $('.showCharge').html(txnDetails);
            }
        });
    </script>
@endpush
