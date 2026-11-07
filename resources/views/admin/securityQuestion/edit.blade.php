@extends('admin.layouts.app')
@section('page_title', __('Edit Security Question'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Security Questions')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Edit Security Question')</h1>
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
                            <h2 class="card-title h4">@lang('Edit Security Question')</h2>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.securityQuestion.update',$securityQuestion) }}" method="post"
                                  enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row mb-4">
                                    <label for="questionLabel"
                                           class="col-sm-3 col-form-label form-label">@lang("Question")</label>
                                    <div class="col-sm-9">
                                        <input type="text"
                                               class="form-control @error('question') is-invalid @enderror"
                                               name="question" id="questionLabel"
                                               placeholder="@lang('eg:- What is your pet name?')"
                                               value="@lang($securityQuestion->question)"
                                               autocomplete="off">
                                        @error('question')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary">@lang('Save Changes')</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
