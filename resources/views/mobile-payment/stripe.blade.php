@extends('mobile-payment.layout')
@section('content')

    <script src="https://js.stripe.com/v3/"></script>
    <style>
        .StripeElement {
            box-sizing: border-box;
            height: 40px;
            padding: 10px 12px;
            border: 1px solid transparent;
            border-radius: 4px;
            background-color: white;
            box-shadow: 0 1px 3px 0 #e6ebf1;
            -webkit-transition: box-shadow 150ms ease;
            transition: box-shadow 150ms ease;
        }

        .StripeElement--focus {
            box-shadow: 0 1px 3px 0 #cfd7df;
        }

        .StripeElement--invalid {
            border-color: #fa755a;
        }

        .StripeElement--webkit-autofill {
            background-color: #fefde5 !important;
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
                                    src="{{ getFile(optional($deposit->gateway)->driver, optional($deposit->gateway)->image) }}"
                                    alt="..."
                                />
                            </div>
                            <div class="text-box">
                                <h4>@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h4>
                            </div>
                        </div>
                        <form action="{{$data->url}}" method="{{$data->method}}">
                            <script
                                src="{{$data->src}}"
                                class="stripe-button"
                                @foreach($data->val as $key=> $value)
                                    data-{{$key}}="{{$value}}"
                                @endforeach>
                            </script>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
@push('script')
    <script>
        $(document).ready(function () {
            $('button[type="submit"]').removeClass("stripe-button-el").addClass("btn btn-bg").find('span').css('min-height', 'initial');
        })
    </script>
@endpush



