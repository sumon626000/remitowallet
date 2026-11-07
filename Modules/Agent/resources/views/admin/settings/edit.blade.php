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
                            <h2 class="card-title h4">{{ $title ?? null }}</h2>
                            <a href="{{ route('admin.agent.settings') }}" class="btn btn-primary btn-sm">
                                @lang('Configuration list')
                            </a>
                        </div>
                        <div class="card-body">

                            <form action="{{ route('admin.agent.settings.update', $agent) }}" method="POST"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                @php
                                    $currencyCode = optional($agent->currency)->code;
                                    $fields = [
                                        ['name' => 'min_limit', 'label' => 'Minimum Limit', 'value' => $agent->min_limit, 'suffix' => $currencyCode],
                                        ['name' => 'max_limit', 'label' => 'Maximum Limit', 'value' => $agent->max_limit, 'suffix' => $currencyCode],
                                        ['name' => 'fixed_charge', 'label' => 'Fixed Charge', 'value' => $agent->fixed_charge, 'suffix' => $currencyCode],
                                        ['name' => 'percent_charge', 'label' => 'Percent Charge', 'value' => $agent->percent_charge, 'suffix' => '%'],
                                        ['name' => 'fixed_commission', 'label' => 'Fixed Commission', 'value' => $agent->fixed_commission, 'suffix' => $currencyCode],
                                        ['name' => 'percent_commission', 'label' => 'Percent Commission', 'value' => $agent->percent_commission, 'suffix' => '%'],
                                        ['name' => 'daily_limit', 'label' => 'Daily Limit', 'value' => $agent->daily_limit, 'suffix' => $currencyCode],
                                        ['name' => 'monthly_limit', 'label' => 'Monthly Limit', 'value' => $agent->monthly_limit, 'suffix' => $currencyCode],
                                    ];
                                @endphp

                                <input type="hidden" name="type" value="{{ $agent->type }}">
                                <input type="hidden" name="currency_id" value="{{ $agent->currency_id }}">

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
                                                                @checked($agent->status)>
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
