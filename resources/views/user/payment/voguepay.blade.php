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
                            <button type="button" class="btn btn-primary" id="btn-confirm">@lang('Pay Now')</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script src="https://pay.voguepay.com/js/voguepay.js"></script>
	<script>
		closedFunction = function () {
		}
		successFunction = function (transaction_id) {
			let txref = "{{ $data->merchant_ref }}";
			window.location.href = '{{ url('payment/voguepay') }}/' + txref + '/' + transaction_id;
		}
		failedFunction = function (transaction_id) {
			window.location.href = '{{ route('failed') }}';
		}

		function pay(item, price) {
			Voguepay.init({
				v_merchant_id: "{{ $data->v_merchant_id }}",
				total: price,
				notify_url: "{{ $data->notify_url }}",
				cur: "{{ $data->cur }}",
				merchant_ref: "{{ $data->merchant_ref }}",
				memo: "{{ $data->memo }}",
				developer_code: '5af93ca2913fd',
				custom: "{{ $data->custom }}",
                customer: {
                    name: "{{ $data->customer_name }}",
                    address: "{{ $data->customer_address }}",
                    email: "{{ $data->customer_email }}"
                },
				closed: closedFunction,
				success: successFunction,
				failed: failedFunction
			});
		}
		$(document).ready(function () {
			$(document).on('click', '#btn-confirm', function (e) {
				e.preventDefault();
				pay('Buy', {{ $data->Buy }});
			});
		});
	</script>
@endpush


