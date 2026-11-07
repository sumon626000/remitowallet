@extends(userLayout())
@section('title', trans('Send Money'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Services"/>

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-md-between">
                        <h4 class="card-header-title">@lang('Send Money')</h4>
                        <x-btn.info route="user.transfer.index"> @lang('History') </x-btn.info>
                    </div>
                    <form action="{{ route('user.transfer.initialize') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="mb-4">
                                    <label for="recipient" class="form-label">
                                        @lang('Recipient email or username')</label>

                                    <input type="text" class="form-control" name="recipient" id="recipient"
                                           placeholder="@lang('Please enter valid email or username')"
                                           value="{{ old('recipient') }}">
                                    <small class="form-text"></small>

                                    @error('recipient')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row mb-4 g-3">
                                <div class="col-md-6">
                                    <label for="Currency" class="form-label">@lang('Currency')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off"
                                                name="currency" id="currency"
                                                data-hs-tom-select-options='{ "placeholder": "Select Currency..." }'>
                                            @if(!empty($currencies))
                                                @foreach($currencies as $key => $currency)
                                                    <option data-currency="{{ __($currency->code) }}"
                                                            data-currencytype="{{ $currency->currency_type }}"
                                                            value="{{ $currency->id }}"
                                                            @if(@request('currency') == $currency->code) selected @endif
                                                    >
                                                        {{ $currency->code }} - {{ $currency->name }}</option>
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
                                    <x-tooltip class="amount-info d-none"/>
                                    <input type="number" step="any" class="form-control" name="amount" id="amount"
                                           placeholder="@lang('0.00')" value="{{ old('amount') }}">
                                    <small class="form-text"></small>
                                    @error('amount')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>
                            <!-- Checkbox Switch -->
                            <div class="form-check form-switch mb-4">
                                <input type="checkbox" name="charge_from" class="form-check-input "
                                       id="invalidSwitch" value="1" checked>
                                <label class="form-check-invalid">@lang('Receiver will pay the transaction charge') <i
                                        class="bi-question-circle text-body ms-1" data-bs-toggle="tooltip"
                                        data-bs-placement="top"
                                        aria-label="Products are the goods or services you sell."
                                        data-bs-original-title="@lang('If enable transaction charge will deduct from receiver')"></i></label>
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

                            <x-transaction-details :mobile="true"/>

                            <button id="submit" type="submit" class="btn btn-primary mt-4" disabled>
                                @lang('Send Money')
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <x-transaction-details :mobile="false"/>

        </div>
    </div>
@endsection

<x-assets :flatpickr="false"/>

@push('script')
    <script>
        'use strict';
        var chargeBtn = true;
        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
            let recipientField = $('#recipient');
            let amountField = $('#amount');
            let recipientStatus = false;
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
                e.preventDefault();
                let amount = amountField.val();
                let currency_id = $('#currency').val();
                let currency_code = $('#currency option:selected').data('currency');
                let currency_type = $('#currency option:selected').data('currencytype');
                let transaction_type_id = "{{ config('transactionType.transfer') }}"; //transfer
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
                    checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code);
                } else {
                    clearMessage(amountField);
                    $('.showCharge').html('');
                }
            });

            function checkRecipient() {
                $.ajax({
                    method: "GET",
                    url: "{{ route('user.transfer.checkRecipient') }}",
                    dataType: "json",
                    data: {
                        'recipient': recipientField.val()
                    }
                })
                    .done(function (response) {
                        if (response.status) {
                            clearMessage(recipientField)
                            $(recipientField).addClass('is-valid')
                            $(recipientField).siblings('small').html(response.message).removeClass('text-danger').addClass('text-success');
                            recipientStatus = true;
                            submitButton()
                        } else {
                            recipientStatus = false;
                            submitButton()
                            clearMessage(recipientField)
                            $(recipientField).addClass('is-invalid')
                            $(recipientField).siblings('small').html(response.message).removeClass('text-success').addClass('text-danger');
                        }
                    });
            }

            function checkAmount(amount, currency_id, transaction_type_id, charge_from, currency_code) {
                const chargeSwitch = document.getElementById('invalidSwitch');
                chargeBtn = chargeSwitch.checked;
                $.ajax({
                    method: "GET",
                    url: "{{ route('user.transfer.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'amount': amount,
                        'currency_id': currency_id,
                        'transaction_type_id': transaction_type_id,
                        'charge_from': charge_from,
                    }
                })
                    .done(function (response) {

                        let minLimit = formatAmount(response.min_limit, response.currency_limit);
                        let maxLimit = formatAmount(response.max_limit, response.currency_limit);
                        let tooltipContent = `@lang('Min'): <b>${minLimit}</b> | @lang('Max'): <b>${maxLimit} ${currency_code}</b>`;
                        updateTooltip('.amount-info', tooltipContent);

                        let amountField = $('#amount');
                        clearMessage(amountField)
                        if (response.status) {
                            $(amountField).addClass('is-valid')
                            $(amountField).siblings('small').html(response.message).removeClass('text-danger').addClass('text-success');
                            amountStatus = true;
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

            function isEmail(email) {
                let regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
                return regex.test(email);
            }

            function submitButton() {
                if (recipientStatus && amountStatus) {
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
