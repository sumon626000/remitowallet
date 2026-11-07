@extends(userLayout())
@section('title', trans('Escrow Request'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Services"/>

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-md-between">
                        <h4 class="card-header-title">@lang('Escrow Request')</h4>
                        <x-btn.info route="user.escrow.index">@lang('History')</x-btn.info>
                    </div>
                    <form action="{{ route('user.escrow.createRequest') }}" method="post"
                          enctype="multipart/form-data">
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

                            <div class="row mt-2">
                                <label for="basicFormFile" class="js-file-attach form-label"
                                       data-hs-file-attach-options='{
                                  "textTarget": "[for=\"customFile\"]"
                                 }'>@lang('Attachment (optional)')</label>
                                <div>
                                    <input class="form-control" type="file" id="basicFormFile" name="attachment">
                                </div>
                                @error('attachment')
                                <div class="error text-danger"> @lang($message) </div>
                                @enderror
                            </div>

                            <x-transaction-details :mobile="true" />

                            <button id="submit" type="submit" class="btn btn-primary mt-4" disabled>
                                @lang('Create escrow request')
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <x-transaction-details :mobile="false" />

        </div>
    </div>
@endsection

@push('js-lib')
    <script src="{{ asset("assets/admin/js/hs-file-attach.min.js") }}"></script>
@endpush

<x-assets :flatpickr="false" />

@push('script')
    <script>
        'use strict';

        $(document).ready(function () {
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

            $(document).on('change, input', "#amount, #charge_from, #currency", function (e) {
                e.preventDefault();
                let amount = amountField.val();
                let currency_id = $('#currency').val();
                let currency_code = $('#currency option:selected').data('currency');
                let currency_type = $('#currency option:selected').data('currencytype');
                let transaction_type_id = "{{ config('transactionType.escrow') }}";
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
                    url: "{{ route('user.escrow.checkRecipient') }}",
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
                $.ajax({
                    method: "GET",
                    url: "{{ route('user.escrow.checkInitiateAmount') }}",
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
                        if (response.status) {
                            clearMessage(amountField)
                            $(amountField).addClass('is-valid')
                            $(amountField).siblings('small').html(response.message).removeClass('text-danger').addClass('text-success');
                            amountStatus = true;
                            submitButton()
                            showCharge(response, currency_code)
                        } else {
                            amountStatus = false;
                            submitButton()
                            $('.showCharge').html('')
                            clearMessage(amountField)
                            $(amountField).addClass('is-invalid')
                            $(amountField).siblings('small').html(response.message).removeClass('text-success').addClass('text-danger');
                        }
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

                const receiveAmount = response.received_amount - response.charge;

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
                                    <span class="d-block">{{ __('Charge') }}</span>
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
                                    <span class="d-block">{{ __('Request Amount') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5>${response.transfer_amount} ${currency_code}</h5>
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
                                    <span class="d-block">{{ __('Receivable Amount') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5>${receiveAmount} ${currency_code}</h5>
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
