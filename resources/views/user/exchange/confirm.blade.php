@extends(userLayout())
@section('title', trans('Confirm Exchange'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Exchange Money"/>

        @props(['exchange'])
        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Confirm Exchange') </h4>
                    </div>
                    <form action="{{ route('user.exchange.confirm',$utr) }}" method="post">
                        @csrf
                        <div class="card-body">

                            @php
                                $exFromCode = $exchange->fromWallet?->currency?->code;
                                $exRate = '1 '. $exchange->fromWallet?->currency?->code.' = '. (getAmount($exchange->exchange_rate)) .' '. $exchange->toWallet?->currency?->code
                            @endphp

                            <div class="list-group list-group-flush list-group-no-gutters">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Exchange')</div>
                                    </div>
                                    <span> {{ __(optional(optional($exchange->fromWallet)->currency)->code) }} @lang('to') {{ __(optional(optional($exchange->toWallet)->currency)->code) }} </span>
                                </li>
                                <x-list-item label="Exchange" value="{{ $exFromCode . trans(' to ') . $exchange->toWallet?->currency?->code }}" class=" fw-semibold"/>
                                <x-list-item label="Exchange Rate" value="{{ $exRate }}" class="fw-semibold"/>
                                <x-list-item label="Total charge" value="{{ getAmount($exchange->charge) }} {{ $exFromCode }}" class=" fw-semibold text-danger"/>
                                <x-list-item label="Payable amount" value="{{ getAmount($exchange->transfer_amount) }} {{ $exFromCode }}" class="fw-semibold"/>
                                <x-list-item label="You will get" value="{{ getAmount($exchange->received_amount) }} {{ $exchange->toWallet?->currency?->code }}" class="fw-semibold text-success"/>
                            </div>

                            @if(in_array('exchange',$enable_for))
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
                                    class="btn btn-primary mt-4">@lang('Confirm Exchange')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

