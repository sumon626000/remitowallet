@extends(userLayout())
@section('title', trans('Confirm Bill Pay'))
@section('content')
    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Confirm Bill Pay')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Confirm Bill Pay')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Confirm Bill Pay') </h4>
                    </div>
                    <form action="{{ route('user.pay.bill.confirm',$billPay->utr) }}" method="post">
                        @csrf
                        <div class="card-body">
                            <ol class="list-group list-group-numbered">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Category')</div>
                                    </div>
                                    <span> {{str_replace('_',' ',ucfirst($billPay->category_name))}} </span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Service')</div>
                                    </div>
                                    <span>{{optional($billPay->service)->type}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Country Code')</div>
                                    </div>
                                    <span>{{$billPay->country_name}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('From Wallet')</div>
                                    </div>
                                    <span>{{optional($billPay->walletCurrency)->code}} - {{optional($billPay->walletCurrency)->name}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start text-danger">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Exchange Rate')</div>
                                    </div>
                                    <span>1 {{optional($billPay->walletCurrency)->code}} = {{getAmount($billPay->exchange_rate,2)}} {{$billPay->currency}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Amount')</div>
                                    </div>
                                    <span>{{getAmount($billPay->amount,2)}} {{$billPay->currency}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start text-danger">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Charge')</div>
                                    </div>
                                    <span class="text-danger">{{getAmount($billPay->charge,2)}} {{$billPay->currency}}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between align-items-start text-success">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Payable Amount')</div>
                                    </div>
                                    <span class="">{{getAmount(($billPay->payable_amount / $billPay->exchange_rate) + ($billPay->charge / $billPay->exchange_rate),2)}} {{optional($billPay->walletCurrency)->code}}</span>
                                </li>
                            </ol>

                            @if(in_array('bill_payment',$enable_for))
                                <div class="row mt-4">
                                    <div>
                                        <label for="security_pin"
                                               class="form-label">@lang('Security Pin')</label>
                                        <input type="password" class="form-control" name="security_pin"
                                               id="security_pin"
                                               placeholder="@lang('Please enter your security PIN')"
                                               value="{{ old('security_pin') }}">
                                    </div>

                                    @error('security_pin')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            @endif
                            <button id="submit" type="submit"
                                    class="btn btn-primary mt-4">@lang('Confirm Pay')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

