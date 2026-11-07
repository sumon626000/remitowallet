@extends('mobile-payment.layout')
@section('content')
    <style>
        body {
            color: #26293b !important;
        }

        .wpwl-control {
            color: #000 !important;
        }

        #frameDiv {
            border-style: solid;
            border-width: 1cm;
            border-color: red;
            margin: 0;
            padding: 0 13px !important;
            background: #d1d1d1 !important;
        }
    </style>

    <section class="pwa-payment-section">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col h-100 d-flex align-items-center justify-content-center">
                    <div class="pay-box">
                        <div class="d-flex">
                            <div class="img-box">
                                <img
                                    class="img-fluid"
                                    src="{{getFile(optional($deposit->gateway)->driver, optional($deposit->gateway)->image)}}"
                                    alt="..."
                                />
                            </div>
                            <div class="text-box">
                                <h4>@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h4>
                            </div>
                        </div>
                        <form action="{{$data->url}}" class="paymentWidgets" data-brands="VISA MASTER AMEX"></form>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <script src="https://test.oppwa.com/v1/paymentWidgets.js?checkoutId={{$data->checkoutId}}"></script>
@endsection
