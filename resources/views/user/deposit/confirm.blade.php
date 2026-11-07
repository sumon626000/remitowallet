@extends(userLayout())
@section('title',__('Preview Add Fund'))

@section('content')

    <div class="content {{ containerClass() }}">

        <x-page-header menu="Services"/>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6 mb-3 mb-lg-0">

                <div class="card">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Transaction Details')</h4>
                    </div>
                    <form action="{{ route('deposit.confirm',$utr) }}" method="post">
                        @csrf
                        @php
                            $receive = getConvertAmount($deposit);
                        @endphp
                        <div class="card-body">
                            <div class="list-group list-group-flush list-group-no-gutters showCharge">
                                <x-list-item label="Gateway" value="{{ __(optional($deposit->gateway)->name) }}"
                                             class="text-info fw-semibold"/>
                                <x-list-item label="Name" value="{{ __($deposit->receiver->name) }}"
                                             class=" fw-semibold"/>
                                @if(isset($deposit->receive_wallet?->code))
                                <x-list-item label="Receive Wallet"
                                             value="{{ __($deposit->receive_wallet?->name) }} ({{ __($deposit->receive_wallet?->code) }})"
                                             show="{{currencyPosition($receive['amount'],$receive['currency_id'])}}"/>
                                @endif
                                <x-list-item label="Amount"
                                             value="{{ (getAmount($deposit->amount)) }} {{ $deposit->payment_method_currency  }}"/>
                                <x-list-item label="Charge"
                                             value="{{ (getAmount($deposit->charge)) }} {{ $deposit->payment_method_currency  }}"
                                             class="text-danger"/>
                                <x-list-item label="Payable amount"
                                             value="{{ (getAmount($deposit->payable_amount)) }} {{ $deposit->payment_method_currency  }}"
                                             class="text-success"/>
                            </div>

                            <div class="form-group mt-3 security-block">
                                @if(in_array('deposit',$enable_for))
                                    <label class="form-label" for="security_pin">@lang('Security Pin')</label>
                                    <input type="password" name="security_pin" value="{{ old('security_pin') }}"
                                           placeholder="@lang('Please enter your security PIN')" autocomplete="off"
                                           class="form-control @error('security_pin') is-invalid @enderror">
                                    <div class="invalid-feedback">
                                        @error('security_pin') @lang($message) @enderror
                                    </div>
                                    <div class="valid-feedback"></div>
                                @endif
                            </div>

                            <button type="submit" id="submit"
                                    class="btn btn-primary btn-sm btn-block btn-security mt-3">@lang('Confirm')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
