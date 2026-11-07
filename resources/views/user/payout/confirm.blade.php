@extends(userLayout())
@section('title',__('Payout Confirm'))

@section('content')

    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{userRoute('dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Payout')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Payout Confirm')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Payout Confirm')</h4>
                    </div>
                    <form action="{{ userRoute('payout.confirm',$payout->trx_id) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">

                            @if($payoutMethod->supported_currency)
                                <div class="col-md-12">
                                    <div class="input-box search-currency-dropdown">
                                        <label class="form-label" for="from_wallet">@lang('Payout Currency')</label>
                                        <input type="text" name="currency_code" autocomplete="off"
                                               value="{{ $payout->payout_currency_code }}" readonly
                                               class="form-control transfer-currency @error('currency_code') is-invalid @enderror">
                                        @error('currency_code')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            @if($payoutMethod->code == 'paypal')
                                <div class="col-md-12 mt-4">
                                    <div class="form-group search-currency-dropdown">
                                        <label class="form-label" for="from_wallet">@lang('Select Recipient Type')</label>
                                        <select id="from_wallet" name="recipient_type" class="form-control cmn-select2" required>
                                            <option value="" disabled selected>@lang('Select Recipient Type')</option>
                                            <option value="EMAIL">@lang('Email')</option>
                                            <option value="PHONE">@lang('phone')</option>
                                            <option value="PAYPAL_ID">@lang('Paypal Id')</option>
                                        </select>
                                        @error('recipient_type')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            @if(isset($payoutMethod?->inputForm))
                                @foreach($payoutMethod->inputForm as $key => $value)
                                    @if($value->type == 'text')
                                        <div class="input-box mt-3">
                                            <label class="form-label" for="{{ $value->field_name }}">@lang($value->field_label)</label>
                                            <input type="text" name="{{ $value->field_name }}"
                                                   placeholder="{{ __($value->field_label) }}"
                                                   autocomplete="off"
                                                   @if($value->validation == 'required') required @endif
                                                   value="{{ old($value->field_name) }}"
                                                   class="form-control @error($value->field_name) is-invalid @enderror">
                                            <div class="invalid-feedback">
                                                @error($value->field_name) @lang($message) @enderror
                                            </div>
                                        </div>
                                    @elseif($value->type == 'textarea')
                                        <div class="input-box mt-3">
                                            <label class="form-label" for="{{ $value->field_name }}">@lang($value->field_label)</label>
                                            <textarea
                                                @if($value->validation == 'required') required @endif
                                                class="form-control @error($value->field_name) is-invalid @enderror"
                                                name="{{$value->field_name}}"
                                                rows="3">{{ old($value->field_name) }}</textarea>
                                            <div
                                                class="invalid-feedback">@error($value->field_name) @lang($message) @enderror</div>
                                        </div>
                                    @elseif($value->type == 'file')
                                        <div class="input-box mt-3 mb-4">
                                            <div id="image-preview" class="image-preview">
                                                <label class="form-label" for="image-upload"
                                                       id="image-label">@lang($value->field_label)</label>
                                                <input type="file" name="{{ $value->field_name }}"
                                                       @if($value->validation == 'required') required @endif
                                                       class="form-control @error($value->field_name) is-invalid @enderror"
                                                       id="image-upload"/>
                                            </div>
                                            <div class="invalid-feedback d-block">
                                                @error($value->field_name) @lang($message) @enderror
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            @if(in_array('payout', $enable_for))
                                <div class="input-box mt-3 security-block">
                                    <label class="form-label" for="security_pin">@lang('Security Pin')</label>
                                    <input type="password" name="security_pin"
                                           placeholder="@lang('Please enter your security PIN')"
                                           autocomplete="off"
                                           value="{{ old('security_pin') }}"
                                           class="form-control @error('security_pin') is-invalid @enderror">
                                    <div class="invalid-feedback">
                                        @error('security_pin') @lang($message) @enderror
                                    </div>
                                    <div class="valid-feedback"></div>
                                </div>
                            @endif

                            <button id="submit" type="submit" class="btn btn-primary mt-4" >@lang('Confirm Submit')</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Transaction Details')</h4>
                    </div>
                    <div class="card-body">
                        @php $cc = $payout->payout_currency_code @endphp
                        <div class="list-group list-group-flush list-group-no-gutters showCharge">
                            <x-list-item label="Payout Method" value="{{ __($payoutMethod->name) }}" class="text-info fw-semibold"/>
                            <x-list-item label="Request Amount" value="{{ getAmount($payout->amount).' '.$cc }}" class=" fw-semibold"/>
                            <x-list-item label="Charge" value="{{ getAmount($payout->charge).' '.$cc }}" class="text-danger"/>
                            <x-list-item label="Net Amount" value="{{ (getAmount($payout->net_amount)) }} {{ $cc }}" class="text-info fw-semibold"/>
                            <x-list-item label="Net Amount In Base Currency" value="{{ getAmount($payout->net_amount_in_base_currency) . ' ' . basicControl()->base_currency }}" class="text-success fw-semibold" />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection


@push('script')
    <script type="text/javascript">
        $('.cmn-select2').select2();
    </script>

    @if ($errors->any())
        @php
            $collection = collect($errors->all());
            $errors = $collection->unique();
        @endphp
        <script>
            "use strict";
            @foreach ($errors as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush
