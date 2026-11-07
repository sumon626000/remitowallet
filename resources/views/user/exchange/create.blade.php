@extends(userLayout())
@section('title', trans('Exchange Money'))
@section('content')
    <div class="content {{ containerClass() }}">

        <x-page-header menu="Services" />

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-md-between">
                        <h4 class="card-header-title">@lang('Exchange Money')</h4>
                        <x-btn.info route="user.exchange.index">@lang('History')</x-btn.info>
                    </div>
                    <form action="{{ route('user.exchange.initialize') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="from_wallet" class="form-label">@lang('From Wallet')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off" name="from_wallet"
                                                id="from_wallet"
                                                data-hs-tom-select-options='{
                                                    "placeholder": "Select Wallet..."
                                                  }'>
                                            <option value="">@lang('Select From Wallet')</option>
                                            @if(!empty($currencies))
                                                @foreach($currencies as $key => $currency)
                                                    <option data-from_code="{{ $currency->code }}"
                                                            data-currencytype="{{ $currency->currency_type }}"
                                                            value="{{ $currency->id }}"
                                                            @if(@request('currency') == $currency->code) selected @endif
                                                    >
                                                        {{ $currency->code }} - {{ $currency->name }}
                                                    </option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('from_wallet')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="to_wallet" class="form-label">@lang('To Wallet')</label>
                                    <div class="">
                                        <select class="form-select" autocomplete="off" name="to_wallet" id="to_wallet">

                                        </select>
                                        @error('to_wallet')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="Amount" class="form-label">@lang('Amount')</label>
                                    <x-tooltip class="amount-info d-none"/>
                                    <input type="number" step="any" class="form-control" name="amount" id="amount"
                                           value="{{ old('amount') }}" autocomplete="off"
                                           placeholder="@lang('0.00')"
                                    >

                                    <small class="form-text"></small>
                                    @error('amount')<span class="text-danger">{{$message}}</span>@enderror
                                </div>
                            </div>

                            <x-transaction-details :mobile="true" />

                            <button id="submit" type="submit" class="btn btn-primary mt-4" disabled>
                                @lang('Exchange Money')
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

        let from_walletField = $('#from_wallet');
        let to_walletField = $('#to_wallet');
        let amountField = $('#amount');

        $(document).ready(function () {
            let preselectedWallet = $('#from_wallet').val();
            currenciesExceptSelected(preselectedWallet)
        });

        $(document).on('change', '#from_wallet', function (e) {
            let from_wallet = from_walletField.val();
            if (from_wallet.length >= 1) {
                currenciesExceptSelected(from_wallet)
            } else {
                $('#to_wallet').html('<option value=""> Select from wallet </option>');
            }
        })

        $(document).on('change, input', '#to_wallet, #amount', function (e) {
            checkAmount();
        })

        function checkAmount() {
            let from_wallet = from_walletField.val();
            let from_code = $('#from_wallet option:selected').data('from_code');
            let to_code = $('#to_wallet option:selected').data('to_code');
            let currency_type = $('#from_wallet option:selected').data('currencytype');

            let to_wallet = to_walletField.val();
            let amount = amountField.val();
            if (amount > 0 && from_wallet && to_wallet && from_wallet.length >= 1 && to_wallet.length >= 1) {

                let fraction = amount.split('.')[1];
                let limit = currency_type == 0 ? 8 : 2;

                if (fraction && fraction.length > limit) {
                    amount = (Math.floor(amount * Math.pow(10, limit)) / Math.pow(10, limit)).toFixed(limit);
                    amountField.val(amount);
                }

                $.ajax({
                    method: "GET",
                    url: "{{ route('user.exchange.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'from_wallet': from_wallet,
                        'to_wallet': to_wallet,
                        'amount': amount,
                    }
                })
                    .done(function (response) {
                        showCharge(response, from_code, to_code)
                        if (response.status) {
                            clearMessage(amountField)
                            $(amountField).addClass('is-valid')
                            $(amountField).siblings('small').html(response.message).removeClass('text-danger').addClass('text-success');
                            $("#submit").removeAttr("disabled");
                        } else {
                            clearMessage(amountField)
                            $(amountField).addClass('is-invalid')
                            $(amountField).siblings('small').html(response.message).removeClass('text-success').addClass('text-danger');
                            $("#submit").attr("disabled", true);
                        }
                    });
            } else {
                clearMessage(amountField)
                $('.showCharge').html('')
            }
        }

        function showCharge(response, from_code, to_code) {

            let minLimit = formatAmount(response.min_limit, response.currency_limit);
            let maxLimit = formatAmount(response.max_limit, response.currency_limit);
            let tooltipContent = `@lang('Min'): <b>${minLimit}</b> | @lang('Max'): <b>${maxLimit} ${from_code}</b>`;
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
                                    <h5>${response.balance} ${from_code}</h5>
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
                                    <h5 class="text-danger">${response.charge} ${from_code} </h5>
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
                                    <h5>${response.transfer_amount} ${from_code}</h5>
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
                                    <span class="d-block">{{ __('You will get') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5 class="text-success">${response.received_amount} ${to_code}</h5>
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
                                    <span class="d-block">{{ __('Exchange rate') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5>1 ${from_code} = ${response.exchange_rate} ${to_code}</h5>
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
                                    <h5>${response.fromWalletUpdateBalance} ${from_code}</h5>
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
                                    <span class="d-block">{{ __('Updated Balance') }}</span>
                                </div>
                                <div class="col-auto">
                                    <h5>${response.toWalletUpdateBalance} ${to_code}</h5>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

			`;
            $('.showCharge').html(txnDetails);
        }

        function currenciesExceptSelected(from_wallet) {
            $.ajax({
                method: "GET",
                url: "{{ route('user.currencies.except.selected') }}",
                dataType: "json",
                data: {
                    'from_wallet': from_wallet
                }
            })
                .done(function (response) {
                    if (response.status) {
                        let options = '';
                        if (response.to_wallet.length === 0) {
                            options = '<option value="" disabled selected> @lang('No Currency Available') </option>'
                        } else {
                            options += '<option value="" disabled selected>@lang('Select to wallet')</option>'
                            $.each(response.to_wallet, function (key, value) {
                                options += '<option data-to_code="' + value.code + '" value="' + value.id + '">' + value.code + ' - ' + value.name + ' </option>'
                            });
                        }

                        $('#to_wallet').html(options);
                        checkAmount();
                    }
                });
        }

        function clearMessage(fieldId) {
            $(fieldId).removeClass('is-valid')
            $(fieldId).removeClass('is-invalid')
            $(fieldId).siblings('small').html('');
            $(fieldId).closest('div').find(".is-valid").html('');
        }


    </script>
@endpush
