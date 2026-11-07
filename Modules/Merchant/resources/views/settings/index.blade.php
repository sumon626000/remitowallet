@extends(userLayout())
@section('title', trans('Merchant Settings'))

@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Settings">
            <x-slot name="push_header_button">
                <button type="submit" form="merchantForm" class="btn btn-primary">@lang('Save settings')</button>
            </x-slot>
        </x-page-header>

        <form method="POST" action="{{ route('merchant.settings.update') }}" id="merchantForm">
            @csrf
            <div class="row">

                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="card h-100">
                        <div class="card-header">
                            <h4 class="card-header-title">@lang('Settings') </h4>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label class="form-label">@lang('Charge Applied To')</label>
                                <div class="tom-select-custom">
                                    <select class="js-select form-select" autocomplete="off"
                                            name="charge_applied_to"
                                            data-hs-tom-select-options='{
                                                      "hideSearch": true,
                                                      "searchInDropdown": false
                                                    }'>

                                        @foreach(['merchant'=>'Myself','user'=>'Sender'] as $key=>$item)
                                            <option
                                                value="{{ $key }}" @selected($key==$setting?->charge_applied_to)>
                                                {{ $item }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">@lang('Auto Withdraw')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" name="auto_withdraw">
                                            @foreach(['1'=>'On', '0'=>'Off'] as $key => $item)
                                                <option
                                                    value="{{ $key }}" @selected($key==$setting?->auto_withdraw)>
                                                    {{ $item }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('auto_withdraw')<small
                                        class="text-danger">{{ $message }}</small>@enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">@lang('Withdraw Frequency')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" name="withdraw_frequency">
                                            @foreach(['weekly','monthly'] as $frequency)
                                                <option
                                                    value="{{ $frequency }}" @selected($frequency==$setting?->withdraw_frequency)>
                                                    {{ str_headline($frequency) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error('withdraw_frequency')<small
                                        class="text-danger">{{ $message }}</small>@enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="withdraw_amount" class="form-label">
                                    @lang('Withdraw Amount') (@lang('Select the currency'))
                                </label>
                                
                               
                                <div class="input-group">
                                    <input type="text" class="form-control" name="withdraw_amount" placeholder="0.00"
                                           value="{{ getAmount($setting?->withdraw_amount) ?? 100 }}">
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select bg-soft-secondary" autocomplete="off"
                                                name="withdraw_currency"
                                                data-hs-tom-select-options='{
                                                      "hideSearch": true,
                                                      "searchInDropdown": false
                                                    }'>
                                            @foreach($payoutMethod?->supported_currency ??  [] as $code)
                                                <option value="{{ $code }}"@selected($code == $setting?->withdraw_currency)>
                                                    {{ $code }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">@lang('Last Auto Withdraw At')</label>
                                <input type="text" class="form-control" value="{{ $setting?->last_withdraw_at }}"
                                disabled>
                            </div>

                        </div>
                    </div>

                </div>

                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="card h-100">
                        <div class="card-header">
                            <h4 class="card-header-title">@lang('Auto Withdraw Information')</h4>
                        </div>
                        <div class="card-body">
                            @if(isset($payoutMethod?->inputForm))
                                @php $withdrawInfo = $setting?->withdraw_information; @endphp

                                @foreach($payoutMethod->inputForm ?? [] as $key => $value)
                                    @if($value->type == 'text')
                                        <div class="input-box mb-3">
                                            <label class="form-label"
                                                   for="{{ $value->field_name }}">@lang($value->field_label)</label>
                                            <input type="text" name="{{ $value->field_name }}"
                                                   placeholder="{{ __($value->field_label) }}"
                                                   autocomplete="off"
                                                   @if($value->validation == 'required') required @endif
                                                   value="{{ old($value->field_name, $withdrawInfo?->{$value->field_name}->field_value ?? '') }}"
                                                   class="form-control @error($value->field_name) is-invalid @enderror">
                                            <div class="invalid-feedback">
                                                @error($value->field_name) @lang($message) @enderror
                                            </div>
                                        </div>
                                    @elseif($value->type == 'textarea')
                                        <div class="input-box mb-3">
                                            <label class="form-label"
                                                   for="{{ $value->field_name }}">@lang($value->field_label)</label>
                                            <textarea
                                                @if($value->validation == 'required') required @endif
                                            class="form-control @error($value->field_name) is-invalid @enderror"
                                                name="{{$value->field_name}}"
                                                rows="3">{{ old($value->field_name, $withdrawInfo?->{$value->field_name}->field_value ?? '') }}</textarea>
                                            <div
                                                class="invalid-feedback">@error($value->field_name) @lang($message) @enderror</div>
                                        </div>
                                    @elseif($value->type == 'file')
                                        <div class="input-box mb-3">
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
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection


@push('script')
    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush

<x-assets :flatpickr="false"/>
