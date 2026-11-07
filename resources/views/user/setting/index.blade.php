@extends(userLayout())
@section('title', trans('Currency Settings'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Currency Settings')</h1>
                </div>
            </div>
        </div>

        <x-notification-box title="Notice!"
            message="Configure the currency settings for efficient store management and streamline the payment process with QR code integration"
        />

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Currency Settings')</h4>
                    </div>
                    <form action="{{ route('user.currency.setting') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-4">
                                @if(basicControl()->store == 1)
                                    <div class="col-md-6">
                                        <label for="Currency" class="form-label">@lang('Store Currency')</label>
                                        <div class="tom-select-custom">
                                            <select class="js-select form-select" autocomplete="off" name="currency"
                                                    id="currency"
                                                    data-hs-tom-select-options='{
                                            "placeholder": "Select Store Currency..."
                                          }'>
                                                @if(!empty($currencies))
                                                    @foreach($currencies as $key => $currency)
                                                        <option
                                                            value="{{ $currency->id }}" {{optional(auth()->user())->store_currency_id == $currency->id ? 'selected':''}}>
                                                            {{ __($currency->code) }}
                                                            - {{ __($currency->name) }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('currency')
                                            <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                @if(basicControl()->qr_payment == 1)
                                    <div class="col-md-6">
                                        <label for="Currency" class="form-label">@lang('QR Code Currency')</label>
                                        <div class="tom-select-custom">
                                            <select class="js-select form-select" autocomplete="off"
                                                    name="qr_currency_id"
                                                    id="qr_currency_id"
                                                    data-hs-tom-select-options='{
                                            "placeholder": "Select QR Currency..."
                                          }'>
                                                @if(!empty($currencies))
                                                    @foreach($currencies as $key => $currency)
                                                        <option
                                                            value="{{ $currency->id }}" {{optional(auth()->user())->qr_currency_id == $currency->id ? 'selected':''}}>
                                                            {{ __($currency->code) }}
                                                            - {{ __($currency->name) }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                            @error('qr_currency_id')
                                            <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <button id="submit" type="submit"
                                    class="btn btn-primary mt-4">@lang('Save Changes')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';

        (function () {
            HSCore.components.HSTomSelect.init('.js-select')
        })();
    </script>
@endpush
