@extends(userLayout())
@section('title', trans('Confirm Transfer'))
@section('content')
    <div class="content {{ containerClass() }}">

        <x-page-header menu="Request Money"/>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Confirm Transfer') </h4>
                    </div>
                    <form action="{{ route('user.requestMoney.confirm',[$utr]) }}" method="post">
                        @csrf
                        <div class="card-body">

                            <div class="list-group list-group-flush list-group-no-gutters">
                                <x-list-item label="Receiver name" value="{{ $requestMoney->sender?->name }}" class=" fw-semibold"/>
                                <x-list-item label="Payable amount" value="{{ getAmount($requestMoney->transfer_amount) }} {{ $requestMoney->currency?->code }}" class="fw-semibold"/>
                                <x-list-item label="Total charge" value="{{ getAmount($requestMoney->charge) }} {{ $requestMoney->currency?->code }}" class=" fw-semibold text-danger"/>
                                <x-list-item label="Receiver will get" value="{{ getAmount($requestMoney->received_amount) }} {{ $requestMoney->currency?->code }}" class="fw-semibold text-success"/>
                                <x-list-item label="Charge deduct from" value="{{ $requestMoney->charge_from == 1 ? __('Receiver') : __('Sender') }}" class="fw-semibold"/>
                                <x-list-item label="Note" value="{{ __($requestMoney->note) }}" class=""/>
                            </div>

                            @if(in_array('transfer',$enable_for))
                                <div class="row mt-4">
                                    <div>
                                        <label for="security_pin" class="form-label">@lang('Security Pin')</label>
                                        <input type="password" class="form-control" name="security_pin"
                                               id="security_pin" placeholder="@lang('Please enter your security PIN')"
                                               value="{{ old('security_pin') }}">
                                    </div>
                                    @error('security_pin')<span class="text-danger">{{$message}}</span>@enderror
                                </div>
                            @endif
                            <button id="submit" type="submit"
                                    class="btn btn-primary mt-4">@lang('Confirm Transfer')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

