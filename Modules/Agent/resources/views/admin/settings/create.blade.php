@extends('admin.layouts.app')
@section('page_title', __('Edit Agent Settings'))
@section('content')
    <div class="content container-fluid">

        <x-page-header menu="Basic Control" submenu="Agent Settings"/>

        <x-notification-box title="Notice!"
                            message="Set charges, limits, commissions, status and others for agent cash in and cash out"
        />

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h2 class="card-title h4">@lang('Create new agent configuration')</h2>
                            <a href="{{ route('admin.agent.settings') }}" class="btn btn-primary btn-sm">
                                @lang('Configuration list')
                            </a>
                        </div>
                        <div class="card-body">

                            <form action="{{ route('admin.agent.settings.store') }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf

                                <div class="row">
                                    <div class="col-md-6 mb-4">
                                        <span class="text-cap text-body">@lang('Currency')</span>
                                        <div class="tom-select-custom">
                                            <select class="js-select form-select" id="currency" name="currency_id"
                                             required>
                                                @forelse($currencies as $item)
                                                    <option value="{{$item->id}}"
                                                            data-code="{{ $item->code }}"
                                                            data-option-template='<span class="d-flex align-items-center">
                                            <img class="avatar avatar-xss avatar-circle me-2" src="{{ $item->getImage() }}" alt="" />
                                            <span class="text-truncate">{{ $item->name }}</span></span>'>
                                                        @lang($item->name)
                                                    </option>
                                                @empty
                                                @endforelse
                                            </select>

                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-4">
                                        <small class="text-cap text-body">@lang('Type')</small>
                                        <div class="tom-select-custom">
                                            <select class="js-select  form-select form-select-sm" id="type"
                                                    name="type"
                                                    data-target-column-index="4" data-hs-tom-select-options='{
                                                                  "placeholder": "Select Type",
                                                                  "searchInDropdown": false,
                                                                  "hideSearch": true,
                                                                  "dropdownWidth": "100%"
                                                                }'>
                                                <option value="cash_in"
                                                        data-option-template='<span class="d-flex align-items-center">
                                                        <span class="legend-indicator bg-secondary"></span>Cash In</span>'>
                                                    @lang('Cash In')
                                                </option>
                                                <option value="cash_out"
                                                        data-option-template='<span class="d-flex align-items-center">
                                                        <span class="legend-indicator bg-warning"></span>Cash Out</span>'>
                                                    @lang('Cash Out')
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                @php
                                    $currencyCode = $currencies?->value('code') ?? 'EUR';
                                    $fields = [
                                        ['name' => 'min_limit', 'label' => 'Minimum Limit', 'suffix' => $currencyCode],
                                        ['name' => 'max_limit', 'label' => 'Maximum Limit', 'suffix' => $currencyCode],
                                        ['name' => 'fixed_charge', 'label' => 'Fixed Charge', 'suffix' => $currencyCode],
                                        ['name' => 'percent_charge', 'label' => 'Percent Charge', 'suffix' => '%'],
                                        ['name' => 'fixed_commission', 'label' => 'Fixed Commission', 'suffix' => $currencyCode],
                                        ['name' => 'percent_commission', 'label' => 'Percent Commission', 'suffix' => '%'],
                                        ['name' => 'daily_limit', 'label' => 'Daily Limit', 'suffix' => $currencyCode],
                                        ['name' => 'monthly_limit', 'label' => 'Monthly Limit', 'suffix' => $currencyCode],
                                    ];
                                @endphp

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
                                                           placeholder="@lang('0.00')"
                                                           value="{{ old($field['name']) }}"
                                                           autocomplete="off" required>

                                                    <span class="input-group-text rate"
                                                        data-suffix-type="{{ $field['suffix'] === '%' ? 'percent' : 'currency' }}">
                                                        {{ __($field['suffix']) }}
                                                    </span>
                                                </div>
                                                @error($field['name'])
                                                <small class="form-text text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    @endforeach
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
                                                                   name="status" id="status" value="1" checked>
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

<x-assets :flatpickr="false"/>


@push('script')

    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const currencySelect = document.getElementById("currency");
            currencySelect.addEventListener("change", function () {
                const selectedOption = this.options[this.selectedIndex];
                const currencyCode = selectedOption.getAttribute("data-code");
                document.querySelectorAll(".rate[data-suffix-type='currency']").forEach(function (el) {
                    el.textContent = currencyCode;
                });
            });
            currencySelect.dispatchEvent(new Event("change"));
        });
    </script>


    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush
