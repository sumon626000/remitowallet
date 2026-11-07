@extends(userLayout())
@section('title', trans('Cash Out'))

@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Money Movement"/>

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-md-between">
                        <h4 class="card-header-title">@lang('Cash Out')</h4>
                        <x-btn.info route="user.transaction"> @lang('History') </x-btn.info>
                    </div>
                    <form action="{{ route('user.cash_out.init') }}" method="post">
                        @csrf
                        <div class="card-body">

                            <div class="mb-3">
                                <label for="agent" class="form-label">@lang('Agent Username/E-mail')</label>
                                <div class="input-group input-group-merge">
                                    <input type="text" class="form-control" name="recipient" id="recipient"
                                           placeholder="agent" value="{{ old('recipient') }}">
                                    <button type="button" class="input-group-append input-group-text"
                                            title="Scan QR Code"
                                            onclick="startQrScan()">
                                        <i class="bi-camera"></i>
                                    </button>
                                </div>
                                <small class="form-text recipient-status"></small>

                                @error('recipient')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>

                            <div class="row mb-4 g-3">
                                <div class="col-md-6">
                                    <label for="Currency" class="form-label">@lang('Currency')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off"
                                                name="currency_id" id="currency"
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

                            <x-transaction-details :mobile="true"/>

                            <button id="submit" type="submit" class="btn btn-primary mt-4" disabled>
                                @lang('Cash out')
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

@include('agent::partials.qr_camera')

@push('script')

    <script>
        'use strict';

        $(document).ready(function () {
            $('[data-toggle="tooltip"]').tooltip()
        });
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

        $(document).on('change, input', "#amount, #currency", function (e) {
            e.preventDefault();
            let amount = amountField.val();
            let currency_id = $('#currency').val();
            let currency_code = $('#currency option:selected').data('currency');
            let currency_type = $('#currency option:selected').data('currencytype');


            if (!isNaN(amount) && amount > 0) {
                let fraction = amount.split('.')[1];
                let limit = currency_type == 0 ? 8 : 2;

                if (fraction && fraction.length > limit) {
                    amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
                    amountField.val(amount);
                }
                checkAmount(amount, currency_id, currency_code);
            } else {
                clearMessage(amountField);
                $('.showCharge').html('');
            }
        });

        function checkRecipient() {
            $.ajax({
                method: "GET",
                url: "{{ route('user.cash_out.checkAgent') }}",
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
                        $('.recipient-status').html(response.message).removeClass('text-danger').addClass('text-success')
                    } else {
                        recipientStatus = false;
                        $(recipientField).addClass('is-invalid')
                        $('.recipient-status').html(response.message).removeClass('text-success').addClass('text-danger')
                    }
                    submitButton()
                });
        }

        function checkAmount(amount, currency_id, currency_code) {
            $.ajax({
                method: "GET",
                url: "{{ route('user.cash_out.checkAmount') }}",
                dataType: "json",
                data: {
                    'amount': amount,
                    'currency_id': currency_id,
                    'type': 'cash_out',
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
            let txnDetails = `
                <x-list-item label="Available Balance" value="${response.balance} ${currency_code}" class="fw-semibold"/>
                <x-list-item label="Transfer Charge" value="${response.charge} ${currency_code}" class="fw-semibold text-danger"/>
                <x-list-item label="Payable Amount" value="${response.transfer_amount} ${currency_code}" class="fw-semibold"/>
                <x-list-item label="Receiver will get" value="${response.received_amount} ${currency_code}" class="fw-semibold text-success"/>
                <x-list-item label="Remaining Balance" value="${response.remaining_balance} ${currency_code}" class="fw-semibold"/>
			`;
            $('.showCharge').html(txnDetails);
        }

    </script>


    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush

