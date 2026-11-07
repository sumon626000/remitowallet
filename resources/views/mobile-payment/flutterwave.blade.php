@extends('mobile-payment.layout')

@section('content')

    <script src="https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js"></script>

    <style>
        .flutterwave-payment-card {
            background: #fff;
            border-radius: 20px;
            padding: 24px 20px;
            width: 100%;
            max-width: 420px;
            margin: auto;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        .gateway-logo {
            width: 80px;
            height: 80px;
            border-radius: 18px;
            overflow: hidden;
            margin: 0 auto 18px;
            background: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .gateway-logo img {
            max-width: 65px;
            max-height: 65px;
            object-fit: contain;
        }

        .payment-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 6px;
        }

        .payment-subtitle {
            font-size: 14px;
            color: #6b7280;
            margin-bottom: 24px;
        }

        .payment-amount-box {
            background: #f9fafb;
            border-radius: 16px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .payment-amount-label {
            font-size: 13px;
            color: #6b7280;
            margin-bottom: 6px;
        }

        .payment-amount {
            font-size: 28px;
            font-weight: 700;
            color: #111827;
            line-height: 1;
        }

        .payment-currency {
            font-size: 16px;
            font-weight: 600;
            color: #4b5563;
        }

        .btn-pay-now {
            width: 100%;
            border: none;
            border-radius: 14px;
            padding: 14px 18px;
            font-size: 16px;
            font-weight: 600;
            background: #111827;
            color: #fff;
            transition: .2s ease;
        }

        .btn-pay-now:active {
            transform: scale(.98);
        }

        .secure-text {
            margin-top: 18px;
            font-size: 13px;
            color: #6b7280;
        }

        @media (max-width: 575px) {

            .flutterwave-payment-card {
                padding: 22px 16px;
                border-radius: 18px;
            }

            .payment-title {
                font-size: 18px;
            }

            .payment-amount {
                font-size: 24px;
            }
        }
    </style>

    <section class="pwa-payment-section">
        <div class="container-fluid h-100">
            <div class="row h-100 align-items-center justify-content-center">
                <div class="col-12 d-flex justify-content-center">

                    <div class="flutterwave-payment-card text-center">

                        <div class="gateway-logo">
                            <img
                                src="{{ getFile(optional($deposit->gateway)->driver, optional($deposit->gateway)->image) }}"
                                alt="{{ optional($deposit->gateway)->name }}"
                            >
                        </div>

                        <h4 class="payment-title">
                            {{ optional($deposit->gateway)->name }}
                        </h4>

                        <p class="payment-subtitle">
                            @lang('Secure payment powered by Flutterwave')
                        </p>

                        <div class="payment-amount-box">
                            <div class="payment-amount-label">
                                @lang('Amount to Pay')
                            </div>

                            <div class="payment-amount">
                                {{ getAmount($deposit->payable_amount) }}
                                <span class="payment-currency">
                                    {{ $deposit->payment_method_currency }}
                                </span>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="btn-pay-now"
                            onclick="payWithRave()"
                        >
                            @lang('Pay Now')
                        </button>

                        <div class="secure-text">
                            🔒 @lang('Your payment is secure and encrypted')
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </section>

@endsection

@push('script')
    <script>
        'use strict';

        const API_publicKey = @json($data->API_publicKey);

        function payWithRave() {

            getpaidSetup({
                PBFPubKey: API_publicKey,

                customer_email: @json($data->customer_email),

                amount: @json($data->amount),

                customer_phone: @json($data->customer_phone),

                currency: @json($data->currency),

                txref: @json($data->txref),

                onclose: function () {
                    // modal closed
                },

                callback: function (response) {

                    let txref = response.tx.txRef;
                    let status = response.tx.status;

                    window.location =
                        '{{ url("payment/flutterwave") }}/' + txref + '/' + status;
                }
            });
        }
    </script>
@endpush
