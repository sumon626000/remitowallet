@extends('admin.layouts.app')
@section('page_title', __('Edit Charge'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Charges & Limits')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Edit Charge')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Edit Charge') {{  $trxType ?? null }}</h1>
                </div>
            </div>
        </div>

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
                        <div class="card-header">
                            <h2 class="card-title h4">@lang('Edit Charge')</h2>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.charge.update',$chargesLimit) }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row mb-4">
                                    <label for="MinLimit"
                                           class="col-sm-3 col-form-label form-label">@lang("Min Limit")</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control @error('min_limit') is-invalid @enderror"
                                                   name="min_limit" id="MinLimit"
                                                   placeholder="@lang("eg:- 0.00")"
                                                   value="{{ getAmount($chargesLimit->min_limit) }}"
                                                   autocomplete="off">
                                            <span
                                                class="input-group-text rate">{{ __(optional($chargesLimit->currency)->code) }}</span>
                                        </div>
                                        @error('min_limit')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="max_limit"
                                           class="col-sm-3 col-form-label form-label">@lang("Max Limit")</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control @error('max_limit') is-invalid @enderror"
                                                   name="max_limit" id="max_limit"
                                                   placeholder="@lang("eg:- 0.00")"
                                                   value="{{ getAmount($chargesLimit->max_limit) }}"
                                                   autocomplete="off">
                                            <span
                                                class="input-group-text rate">{{ __(optional($chargesLimit->currency)->code) }}</span>
                                        </div>
                                        @error('max_limit')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="percentage_charge"
                                           class="col-sm-3 col-form-label form-label">@lang("Charge Percentage")</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control @error('percentage_charge') is-invalid @enderror"
                                                   name="percentage_charge" id="percentage_charge"
                                                   placeholder="@lang("eg:- 0.00")"
                                                   value="{{ getAmount($chargesLimit->percentage_charge) }}"
                                                   autocomplete="off">
                                            <span
                                                class="input-group-text rate">%</span>
                                        </div>
                                        @error('percentage_charge')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="fixed_charge"
                                           class="col-sm-3 col-form-label form-label">@lang("Charge Fixed")</label>
                                    <div class="col-sm-9">
                                        <div class="input-group">
                                            <input type="text"
                                                   class="form-control @error('fixed_charge') is-invalid @enderror"
                                                   name="fixed_charge" id="fixed_charge"
                                                   placeholder="@lang("eg:- 0.00")"
                                                   value="{{ getAmount($chargesLimit->fixed_charge) }}"
                                                   autocomplete="off">
                                            <span
                                                class="input-group-text rate">{{ __(optional($chargesLimit->currency)->code) }}</span>
                                        </div>
                                        @error('fixed_charge')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-2">
                                    <div class="col-sm-12">
                                        <div class="list-group list-group-lg list-group-flush list-group-no-gutters">
                                            <div class="list-group-item">
                                                <div class="d-flex">
                                                    <div class="flex-grow-1">
                                                        <div class="row align-items-center">
                                                            <div class="col">
                                                                <span class="d-block text-dark">@lang("Status")</span>
                                                                <p class="fs-5 text-body mb-0">@lang("Empower users to display their currency preferences by enabling a currency status feature.")</p>
                                                            </div>
                                                            <div class="col-auto">
                                                                <div class="form-check form-switch">
                                                                    <input type='hidden' value='0' name='is_active'>
                                                                    <input
                                                                        class="form-check-input @error('is_active') is-invalid @enderror"
                                                                        type="checkbox"
                                                                        name="is_active"
                                                                        id="is_active"
                                                                        value="1" {{$chargesLimit->is_active ? 'checked':''}}>
                                                                    <label class="form-check-label"
                                                                           for="is_active"></label>
                                                                    @error('is_active')
                                                                    <span class="invalid-feedback">{{ $message }}</span>
                                                                    @enderror
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- End List Item -->
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">@lang('Save changes')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
