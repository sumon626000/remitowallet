@extends('admin.layouts.app')
@section('page_title', __('Voucher Settings'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Voucher Settings')</li>
                            <li class="breadcrumb-item active"
                                aria-current="page">@lang('Voucher Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Voucher Settings')</h1>
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
                            <h2 class="card-title h4">@lang('Voucher Settings')</h2>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.voucher.settings') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf

                                <label class="row form-check form-switch mb-4" for="allowUser">
                                        <span class="col-8 col-sm-9 ms-0">
                                          <span class="d-block text-dark">@lang("Allow existing users on voucher create")</span>
                                          <span
                                              class="d-block fs-5">@lang("If enable user can sent voucher payment request to existing user.")</span>
                                        </span>
                                    <span class="col-4 col-sm-3 text-end">
                                           <input type='hidden' value='0' name='allowUser'>
                                                <input
                                                    class="form-check-input @error('allowUser') is-invalid @enderror"
                                                    type="checkbox"
                                                    name="allowUser"
                                                    id="allowUser"
                                                    value="1" {{ $basicControl->allowUser == 1 ? 'checked' : '' }}>
                                    </span>
                                    @error('allowUser')
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
