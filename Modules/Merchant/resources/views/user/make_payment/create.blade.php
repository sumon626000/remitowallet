

@extends(userLayout())
@section('title', trans('Make Payment'))

@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Money Movement"/>

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5 h-100">
                    <div class="card-header card-header-content-md-between">
                        <h4 class="card-header-title">@lang('Make Payment')</h4>
                    </div>
                    <form action="{{ route('user.make_payment.init') }}" method="post">
                        @csrf
                        <div class="card-body">

                            <div class="mb-3">
                                <label for="merchant" class="form-label">@lang('Merchant Username/E-mail')</label>
                                <div class="input-group input-group-merge">
                                    <input type="text" class="form-control" name="recipient" id="recipient"
                                           placeholder="merchant" value="{{ old('recipient') }}">
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
                                                            @selected(request('currency') == $currency->code)
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
                                           placeholder="@lang('0.00')" value="{{ old('amount') }}" disabled>
                                    <small class="form-text"></small>
                                    @error('amount')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            </div>

                            <x-transaction-details :mobile="true"/>

                            <button id="submit" type="submit" class="btn btn-primary mt-4" disabled>
                                @lang('Make payment')
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
@include('merchant::partials.qr_camera')

@push('script')
    <script>
        "use strict";

        function debounce(fn, delay = 300) {
            let timer;
            return function (...args) {
                clearTimeout(timer);
                timer = setTimeout(() => fn.apply(this, args), delay);
            };
        }

        const recipientField = $('#recipient');
        const amountField = $('#amount');
        const currencyField = $('#currency');
        const submitBtn = $('#submit');

        let recipientStatus = false;
        let amountStatus = false;

        function clearMessage($input) {
            $input.removeClass('is-valid is-invalid');
            $input.siblings('small').html('');
        }

        function updateSubmitStatus() {
            if (recipientStatus && amountStatus) {
                submitBtn.prop('disabled', false);
            } else {
                submitBtn.prop('disabled', true);
            }
        }

        function checkMerchant() {
            const recipient = recipientField.val().trim();
            if (recipient.length < 4) return;

            $.get("{{ route('user.make_payment.checkMerchant') }}", { recipient })
                .done(response => {
                    clearMessage(recipientField);
                    const $msg = $('.recipient-status');

                    if (response.status) {
                        recipientStatus = true;
                        recipientField.addClass('is-valid');
                        $msg.html(response.message).removeClass('text-danger').addClass('text-success');

                        amountField.prop('disabled', false);
                    } else {
                        recipientStatus = false;
                        recipientField.addClass('is-invalid');
                        $msg.html(response.message).removeClass('text-success').addClass('text-danger');
                        amountField.prop('disabled', true);
                    }

                    updateSubmitStatus();
                });
        }

        function checkAmount() {
            const amount = parseFloat(amountField.val());
            const currency_id = currencyField.val();
            const currency_code = currencyField.find('option:selected').data('currency');

            if (!recipientStatus || isNaN(amount) || amount <= 0 || !currency_id) {
                amountStatus = false;
                updateSubmitStatus();
                return;
            }

            const currency_type = currencyField.find('option:selected').data('currencytype');
            const decimals = currency_type === 0 ? 8 : 2;
            if (amount.toString().includes('.')) {
                const limited = parseFloat(amount.toFixed(decimals));
                if (limited !== amount) amountField.val(limited);
            }

            $.get("{{ route('user.make_payment.checkAmount') }}", {
                amount,
                currency_id,
                merchant: recipientField.val().trim()
            }).done(response => {
                clearMessage(amountField);
                const $msg = amountField.siblings('small');

                if (response.status) {
                    amountField.addClass('is-valid');
                    $msg.html(response.message).removeClass('text-danger').addClass('text-success');
                    amountStatus = true;
                    showCharge(response, currency_code);
                } else {
                    amountField.addClass('is-invalid');
                    $msg.html(response.message).removeClass('text-success').addClass('text-danger');
                    amountStatus = false;
                    $('.showCharge').html(`<h5 class="text-danger">${response.message}</h5>`);
                }

                updateSubmitStatus();
            });
        }

        function showCharge(response, currency_code) {
            const markup = `
            <x-list-item label="Available Balance" value="${response.balance} ${currency_code}" class="fw-semibold"/>
            <x-list-item label="Transfer Charge" value="${response.charge} ${currency_code}" class="fw-semibold text-danger"/>
            <x-list-item label="Payable Amount" value="${response.payable_amount} ${currency_code}" class="fw-semibold"/>
            <x-list-item label="Receiver will get" value="${response.received_amount} ${currency_code}" class="fw-semibold text-success"/>
            <x-list-item label="Charge applied to" value="${response.charge_applied_to}" class="fw-semibold text-capitalize"/>
            <x-list-item label="Remaining Balance" value="${response.remaining_balance} ${currency_code}" class="fw-semibold"/>
        `;
            $('.showCharge').html(markup);
        }

        recipientField.on('input', debounce(checkMerchant, 400));
        amountField.on('input', debounce(checkAmount, 400));
        currencyField.on('change', checkAmount);

        $(document).ready(() => $('[data-toggle="tooltip"]').tooltip());
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
