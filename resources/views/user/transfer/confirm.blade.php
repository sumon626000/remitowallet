@extends(userLayout())
@section('title', trans('Confirm Transfer'))
@section('content')
    <div class="content {{ containerClass() }}">

        <x-page-header menu="Transfer"/>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Confirm Transfer') </h4>
                    </div>
                    <form action="{{ route('user.transfer.confirm',$utr) }}" method="post">
                        @csrf
                        <div class="card-body">

                            <div class="list-group list-group-flush list-group-no-gutters">
                                <x-list-item label="Receiver name" value="{{ $transfer->receiver?->name }}" class=" fw-semibold"/>
                                <x-list-item label="Payable amount" value="{{ getAmount($transfer->transfer_amount) }} {{ $transfer->currency?->code }}" class="fw-semibold"/>
                                <x-list-item label="Total charge" value="{{ getAmount($transfer->charge) }} {{ $transfer->currency?->code }}" class=" fw-semibold text-danger"/>
                                <x-list-item label="Receiver will get" value="{{ getAmount($transfer->received_amount) }} {{ $transfer->currency?->code }}" class="fw-semibold text-success"/>
                                <x-list-item label="Charge deduct from" value="{{ $transfer->charge_from == 1 ? __('Receiver') : __('Sender') }}" class="fw-semibold"/>
                                <x-list-item label="Note" value="{{ __($transfer->note) }}" class=""/>
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

