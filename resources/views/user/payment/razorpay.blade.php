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
                            <form action="{{$data->url}}" method="{{$data->method}}">
                                <script src="{{$data->checkout_js}}"
                                        @foreach($data->val as $key=>$value)
                                            data-{{$key}}="{{$value}}"
                                    @endforeach >
                                </script>
                                <input type="hidden" custom="{{$data->custom}}" name="hidden">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection
@push('script')
	<script>
		$(document).ready(function () {
			$('input[type="submit"]').addClass("btn-sm btn-primary border-0");
		})
	</script>
@endpush
