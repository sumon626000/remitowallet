@extends(userLayout())
@section('title',__('Reset Security Pin'))

@section('content')
    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Reset Security Pin')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4 card-primary shadow">
                    <div class="card-header card-header-content-sm-between">
                        <h5 class="card-header-title mb-2 mb-sm-0">@lang('Reset Security Pin')</h5>
                        <a href="{{ route('user.securityPin.manage') }}" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-lock"></i> @lang('Manage PIN uses')</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('user.securityPin.reset') }}" method="post">
                            @csrf
                            <div class="form-group mb-3">
                                <label class="form-label" for="answer">{{ optional($twoFactorSetting->securityQuestion)->question }}</label>
                                <input type="text" value="{{ old('answer') }}" name="answer"
                                       placeholder="@lang("Security question's answer")"
                                       class="form-control @error('answer') is-invalid @enderror"
                                       autocomplete="off">
                                <div class="invalid-feedback">
                                    @error('answer') @lang($message) @enderror
                                </div>
                                <div class="form-text text-muted">
                                    @lang('Hints'): {{ __($twoFactorSetting->hints) }}
                                </div>
                            </div>
                            <div class="form-group mb-3">
                                <label class="form-label" for="old_security_pin">@lang('Old security pin')</label>
                                <input type="password" value="{{ old('old_security_pin') }}" name="old_security_pin"
                                       placeholder="@lang('eg:12345')"
                                       class="form-control @error('old_security_pin') is-invalid @enderror"
                                       autocomplete="off">
                                <div class="invalid-feedback">
                                    @error('old_security_pin') @lang($message) @enderror
                                </div>
                                <div class="valid-feedback"></div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="security_pin">@lang('Security Pin')</label>
                                        <input type="password" value="{{ old('security_pin') }}" name="security_pin"
                                               placeholder="@lang('eg:12345')"
                                               class="form-control @error('security_pin') is-invalid @enderror"
                                               autocomplete="off">
                                        <div class="invalid-feedback">
                                            @error('security_pin') @lang($message) @enderror
                                        </div>
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label class="form-label" for="security_pin_confirmation">@lang('Confirm pin')</label>
                                        <input type="password" value="{{ old('security_pin_confirmation') }}"
                                               name="security_pin_confirmation" placeholder="@lang('eg:12345')"
                                               class="form-control @error('security_pin_confirmation') is-invalid @enderror"
                                               autocomplete="off">
                                        <div class="invalid-feedback">
                                            @error('security_pin_confirmation') @lang($message) @enderror
                                        </div>
                                        <div class="valid-feedback"></div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" id="submit"
                                    class="btn btn-primary btn-sm btn-block">@lang('Reset Security PIN')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
