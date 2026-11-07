@extends('admin.layouts.app')
@section('page_title', __('API Sandbox'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0)">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('API Sandbox')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('API Sandbox')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('API Sandbox')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title h4">@lang('API Sandbox')</h2>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.api.sandbox.index') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                @if($gateways)
                                    <div class="row">
                                        <label for="SelectGateways"
                                               class="col-sm-3 col-form-label form-label">@lang("Select Gateways")</label>
                                        <div class="col-sm-9">
                                            <div class="tom-select-custom tom-select-custom-with-tags">
                                                <select
                                                    class="js-select form-select @error('gateways') is-invalid @enderror"
                                                    id="SelectGateways" name="gateways[]" multiple data-hs-tom-select-options='{
                                                        "placeholder": "Select a gateways..."
                                                      }'>
                                                    <option value="">@lang('Select a gateways')...</option>
                                                    @foreach($gateways as $gateway)
                                                        <option
                                                            value="{{$gateway->id}}" {{in_array($gateway->id,$basicControl->sandbox_gateways??[]) == true ? 'selected':''}}>{{$gateway->name}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            @error('gateways')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

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

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset("assets/admin/js/hs-file-attach.min.js") }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250,
            })
        })
    </script>
@endpush
