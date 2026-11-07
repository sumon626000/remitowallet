@extends(template().'layouts.app')
@section('title',__('Forgot Password'))

@section('content')

    <section class="login">
        <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/login-bg.png') }}');"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="contact-container">
                        <h3>@lang(@$template['forgot_password_title'] ?? 'Reset Password')</h3>
                        <div class="contact-form">
                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ trans(session('status')) }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                                    </button>
                                </div>
                            @endif
                            <form action="{{ route('password.email') }}" class="account-form" method="post">
                                @csrf
                                <div class="input-content">
                                    <label for="email" class="form-label">@lang('Enter Email')</label>
                                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                                           class="form-control @error('email') is-invalid @enderror"
                                           placeholder="@lang('demo@mail.com')">
                                    <div class="text-danger">
                                        @error('email') @lang($message) @enderror
                                    </div>
                                </div>
                                <button type="submit"  class="btn-1 w-100 justify-content-center">
                                    @lang('Send Password Reset Link')
                                    <i class="fa-light fa-arrow-right-long"></i> <span></span>
                                </button>
                            </form>
                        </div>
                        <div class="contact-form-footer">
                            <p>
                                @lang("Don't have an account?")
                                <a href="{{ route('register') }}">@lang('Create a new account')</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
