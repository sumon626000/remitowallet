@extends('admin.layouts.app')
@section('page_title', __('Invoice Settings'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.dashboard')  }}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Invoice Settings')</li>
                            <li class="breadcrumb-item active"
                                aria-current="page">@lang('Invoice Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Invoice Settings')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9 seo-setting">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title h4">@lang('Invoice Settings')</h2>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.invoice.settings') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf

                                <label class="row form-check form-switch mb-4" for="invoice_charge">
                                        <span class="col-8 col-sm-9 ms-0">
                                          <span class="d-block text-dark">@lang("Receiver pay invoice charges")</span>
                                          <span
                                              class="d-block fs-5">@lang("If enabled, the receiver will pay the invoice charges; otherwise, the sender will be responsible for the charges.")</span>
                                        </span>
                                    <span class="col-4 col-sm-3 text-end">
                                           <input type='hidden' value='0' name='invoice_charge'>
                                                <input
                                                    class="form-check-input @error('invoice_charge') is-invalid @enderror"
                                                    type="checkbox"
                                                    name="invoice_charge"
                                                    id="invoice_charge"
                                                    value="1" {{ $basicControl->invoice_charge == 1 ? 'checked' : '' }}>
                                    </span>
                                    @error('invoice_charge')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </label>

                                <div class="d-flex justify-content-end mt-4">
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




