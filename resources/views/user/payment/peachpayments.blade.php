@extends(userLayout())
@section('title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
@section('content')

    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Payment')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@yield('title')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="card p-5" style="max-width: 700px;">
                <div class="row no-gutters">
                    <div class="col-md-4">
                        <img class="img-fluid"
                             src="{{getFile(optional($deposit->gateway)->driver,optional($deposit->gateway)->image) }}"
                             alt="...">
                    </div>
                    <div class="col-md-8">
                        <div class="card-body">
                            <h5 class="card-title">{{ optional($deposit->gateway)->name }}</h5>
                            <p class="card-text">@lang('Please Pay')
                                <strong>
                                    {{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}
                                </strong>
                            </p>
                            <form action="{{$data->url}}" class="paymentWidgets"
                                  data-brands="VISA MASTER AMEX">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

	@if($data->environment == 'test' || $deposit->mode == 1)
		<script src="https://test.oppwa.com/v1/paymentWidgets.js?checkoutId={{$data->checkoutId}}"></script>
	@else
		<script src="https://oppwa.com/v1/paymentWidgets.js?checkoutId={{$data->checkoutId}}"></script>
	@endif
@endsection
