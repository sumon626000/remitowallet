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
                        <button class="btn-custom w-100" id="btn-confirm">@lang('Pay Now')</button>
                        <form
                                action="{{ route('ipn', [optional($deposit->gateway)->code, $deposit->trx_id]) }}"
                                method="POST">
                                @csrf
                                <script src="//js.paystack.co/v1/inline.js"
                                        data-key="{{ $data->key }}"
                                        data-email="{{ $data->email }}"
                                        data-amount="{{$data->amount}}"
                                        data-currency="{{$data->currency}}"
                                        data-ref="{{ $data->ref }}"
                                        data-custom-button="btn-confirm">
                                </script>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@push('script')
    
@endpush
