@extends('mobile-payment.layout')
@section('content')
    <section class="pwa-payment-section">
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col h-100 d-flex align-items-center justify-content-center">
                    <div class="pay-box">
                        <div class="d-flex">
                            <div class="img-box">
                                <img
                                    class="img-fluid"
                                    src="{{getFile(optional($deposit->gateway)->driver,optional($deposit->gateway)->image)}}"
                                    alt="..."
                                />
                            </div>
                            <div class="text-box">
                                <h4>@lang('Please Pay') {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</h4>
                            </div>
                        </div>
                        <div id="paypal-button-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('script')
  <script src="https://www.paypal.com/sdk/js?client-id={{ $data->cleint_id }}">
    </script>
    <script>
        paypal.Buttons({
            createOrder: function (data, actions) {
                return actions.order.create({
                    purchase_units: [
                        {
                            description: "{{ $data->description }}",
                            custom_id: "{{ $data->custom_id }}",
                            amount: {
                                currency_code: "{{ $data->currency }}",
                                value: "{{ $data->amount }}",
                                breakdown: {
                                    item_total: {
                                        currency_code: "{{ $data->currency }}",
                                        value: "{{ $data->amount }}"
                                    }
                                }
                            }
                        }
                    ]
                });
            },
            onApprove: function (data, actions) {
                return actions.order.capture().then(function (details) {
                    var trx = "{{ $data->custom_id }}";
                    window.location = '{{ url('payment/paypal')}}/' + trx + '/' + details.id
                });
            }
        }).render('#paypal-button-container');
    </script>  
@endpush
