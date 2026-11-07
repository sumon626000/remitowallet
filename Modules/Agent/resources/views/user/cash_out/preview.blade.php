@extends(userLayout())
@section('title', trans('Preview Cash Out'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Money Movement"/>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Confirm Cash Out') </h4>
                    </div>
                    <form action="{{ route('user.cash_out.confirm',$trx_id) }}" method="post">
                        @csrf
                        <div class="card-body">
                            @props(['transaction'])
                            @php $currencyCode = $transaction->currency?->code @endphp

                            <div class="list-group list-group-flush list-group-no-gutters">
                                <x-list-item label="Receiver name" value="{{ $transaction->agent?->name }}"
                                             class=" fw-semibold"/>
                                <x-list-item label="Payable amount"
                                             value="{{ getAmount($transaction->net_amount) }} {{ $currencyCode }}"
                                             class="fw-semibold"/>
                                <x-list-item label="Total charge"
                                             value="{{ getAmount($transaction->charge) }} {{ $currencyCode }}"
                                             class=" fw-semibold text-danger"/>
                                <x-list-item label="Receiver will get"
                                             value="{{ getAmount($transaction->amount) }} {{ $currencyCode }}"
                                             class="fw-semibold text-success"/>
                            </div>

                            <div class="col-12 ps-2 mt-2">
                                <label class="form-label text-body" for="note">@lang('Note') (@lang('Optional'))</label>
                                <textarea id="note" name="note" class="form-control"
                                          placeholder="@lang("Type here")" rows="1">{{ old("note") }}</textarea>
                            </div>
                            @error('note')
                                <span class="invalid-feedback d-block">{{ $message }}</span>
                            @enderror

                            @if($enabled)
                                <div class="col-12 ps-2 mt-3">
                                    <div>
                                        <label for="security_pin" class="form-label">
                                            @lang('Security Pin') <span class="text-danger">*</span>
                                        </label>
                                        <input type="password" class="form-control" name="security_pin" required
                                               id="security_pin" placeholder="@lang('Please enter your security pin')"
                                               value="{{ old('security_pin') }}">
                                    </div>
                                    @error('security_pin')<span class="text-danger">{{$message}}</span>@enderror
                                </div>
                            @endif
                            <button id="submit" type="submit"
                                    class="btn btn-primary mt-4">@lang('Confirm cash out')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

