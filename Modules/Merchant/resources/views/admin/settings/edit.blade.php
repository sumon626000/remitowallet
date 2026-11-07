@extends('admin.layouts.app')
@section('page_title', __('Edit Merchant Settings'))
@section('content')
    <div class="content container-fluid">

        <x-page-header menu="Basic Control" submenu="Merchant Settings"/>

        <x-notification-box title="Notice!"
                            message="Set charges, limits, commissions, status and others for merchant"
        />

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h2 class="card-title h4">{{ $title ?? null }}</h2>
                            <a href="{{ route('admin.merchant.settings') }}" class="btn btn-primary btn-sm">
                                @lang('Configuration list')
                            </a>
                        </div>
                        <div class="card-body">

                            <form action="{{ route('admin.merchant.settings.update', $settings) }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                @php
                                    $currencyCode = optional($settings->currency)->code;
                                    $fields = [
                                        ['name' => 'fixed_charge', 'label' => 'Fixed Charge', 'value' => $settings->fixed_charge, 'suffix' => $currencyCode],
                                        ['name' => 'percent_charge', 'label' => 'Percent Charge', 'value' => $settings->percent_charge, 'suffix' => '%'],
                                        ['name' => 'min_withdraw_amount', 'label' => __('Minimum Auto Withdraw Amount'), 'value' => $settings->min_withdraw_amount, 'suffix' => $currencyCode],
                                ];
                                @endphp

                                <input type="hidden" name="currency_id" value="{{ $settings->currency_id }}">

                                <div class="row">
                                    @foreach($fields as $field)
                                        <div class="col-sm-6">
                                            <div class="mb-3">
                                                <label for="{{ $field['name'] }}"
                                                       class="form-label">@lang($field['label'])</label>

                                                <div class="input-group">
                                                    <input type="text"
                                                           class="form-control @error($field['name']) is-invalid @enderror"
                                                           name="{{ $field['name'] }}"
                                                           id="{{ $field['name'] }}"
                                                           placeholder="@lang('eg:- 0.00')"
                                                           value="{{ getAmount($field['value']) }}"
                                                           autocomplete="off" required>

                                                    <span
                                                        class="input-group-text rate">{{ __($field['suffix']) }}</span>
                                                </div>
                                                @error($field['name'])
                                                <small class="form-text text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach

                                        <div class="col-md-6 mb-4">
                                            <small class="text-cap text-body">@lang('Auto Withdraw')</small>
                                            <div class="tom-select-custom">
                                                <select class="js-select  form-select form-select-sm" id="auto_withdraw"
                                                        name="auto_withdraw"
                                                        data-target-column-index="4" data-hs-tom-select-options='{
                                                                  "placeholder": "Select Type",
                                                                  "searchInDropdown": false,
                                                                  "hideSearch": true,
                                                                  "dropdownWidth": "100%"
                                                                }'>
                                                    <option value="1" @selected($settings->auto_withdraw == 1)
                                                            data-option-template='<span class="d-flex align-items-center">
                                                        <span class="legend-indicator bg-success"></span>Yes</span>'>
                                                        @lang('Yes')
                                                    </option>
                                                    <option value="0" @selected($settings->auto_withdraw == 0)
                                                            data-option-template='<span class="d-flex align-items-center">
                                                        <span class="legend-indicator bg-warning"></span>No</span>'>
                                                        @lang('No')
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-sm-12">
                                        <div class="list-group list-group-lg list-group-flush list-group-no-gutters">
                                            <div class="list-group-item">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <span class="d-block text-dark">@lang('Status')</span>
                                                        <p class="fs-5 text-body mb-0">@lang('Switch to change the status.')</p>
                                                    </div>
                                                    <div>
                                                        <div class="form-check form-switch">
                                                            <input type="hidden" name="status" value="0">
                                                            <input type="checkbox"
                                                                   class="form-check-input @error('status') is-invalid @enderror"
                                                                   name="status"
                                                                   id="status"
                                                                   value="1"
                                                                @checked($settings->status)>
                                                            @error('status')
                                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-start">
                                    <button type="submit" class="btn btn-primary">@lang('Save')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

<x-assets :flatpickr="false" />

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
