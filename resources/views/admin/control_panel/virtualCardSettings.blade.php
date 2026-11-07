@extends('admin.layouts.app')
@section('page_title', __('Virtual-Card Settings'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Virtual-Card Settings')</li>
                            <li class="breadcrumb-item active"
                                aria-current="page">@lang('Virtual-Card Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Virtual-Card Settings')</h1>
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
                            <h2 class="card-title h4">@lang('Virtual-Card Settings')</h2>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.virtual-card.settings') }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf

                                <label class="row form-check form-switch mb-4" for="v_card_multiple">
                                        <span class="col-8 col-sm-9 ms-0">
                                          <span
                                              class="d-block text-dark">@lang("Allow existing users on create multiple card")</span>
                                          <span
                                              class="d-block fs-5">@lang("If enable user can sent voucher payment request to existing user.")</span>
                                        </span>
                                    <span class="col-4 col-sm-3 text-end">
                                           <input type='hidden' value='0' name='v_card_multiple'>
                                                <input
                                                    class="form-check-input @error('v_card_multiple') is-invalid @enderror"
                                                    type="checkbox"
                                                    name="v_card_multiple"
                                                    id="v_card_multiple"
                                                    value="1" {{ $basicControl->v_card_multiple == 1 ? 'checked' : '' }}>
                                    </span>
                                    @error('v_card_multiple')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </label>

                                <label for="virtualCard"
                                       class="form-label">@lang('Charges exiting users per card request')</label>
                                <div class="input-group">
                                    <input type="text"
                                           class="form-control  @error('v_card_charge') is-invalid @enderror"
                                           name="v_card_charge" id="virtualCard"
                                           placeholder="@lang('Virtual Card Charge')" aria-label="@lang('Virtual Card Charge')d"
                                           value="{{$basicControl->v_card_charge}}">
                                    <span class="input-group-text" id="basic-addon2">{{$basicControl->base_currency}}</span>
                                </div>
                                @error('v_card_charge')
                                <span class="invalid-feedback">{{ $message }}</span>
                                @enderror

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
