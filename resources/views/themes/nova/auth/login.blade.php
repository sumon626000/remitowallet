@extends(template().'layouts.app')
@section('title',__('Login'))
@section('content')
    <section class="login">
        <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/login-bg.png') }}');"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="contact-container">
                        <h3>@lang(@$template['login_title'] ?? 'Login to Get Started')</h3>
                        <div class="contact-form">

                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ trans(session('status')) }}
                                    <button type="button" class="btn-close removeStatus" data-bs-dismiss="alert"
                                            aria-label="Close">
                                    </button>
                                </div>
                            @endif

                            <form action="{{ route('login') }}" method="post">
                                @csrf
                                <div class="input-content">
                                    <label for="email" class="form-label">@lang('Username or Email')</label>
                                    <input type="text" id="email" name="username" value="{{ old('username', config('demo.IS_DEMO') ? (request()->username ?? 'demouser') : '') }}"
                                           class="form-control cmn-input @error('username') is-invalid @enderror
                                            @error('email') is-invalid @enderror"
                                           placeholder="@lang('Enter username or email')">
                                    <div class="invalid-feedback">
                                        @error('username') @lang($message) @enderror
                                        @error('email') @lang($message) @enderror
                                    </div>
                                </div>
                                <div class="input-content">
                                    <label for="password" class="form-label">@lang('Password')</label>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}"
                                           class="fpwd form-label">@lang('Forgot Your Password')?</a>
                                    @endif
                                    <input type="password" id="password" name="password" value="{{ old('password', config('demo.IS_DEMO') ? (request()->password ?? 'demouser') : '') }}"
                                           class="form-control cmn-input @error('password') is-invalid @enderror"
                                           placeholder="@lang('Enter your password')">

                                    <div class="invalid-feedback">
                                        @error('password') @lang($message) @enderror
                                    </div>
                                </div>

                                @if(basicControl()->manual_recaptcha &&  basicControl()->manual_recaptcha_login)
                                    <div class="mb-4">
                                        <label for="captcha" class="form-label">@lang('Manual captcha')</label>
                                        <div class="input-group">
                                            <input type="text" name="captcha" id="captcha"
                                                   class="form-control @error('captcha') is-invalid @enderror"
                                                   placeholder="@lang('Enter captcha code')"
                                                   autocomplete="off" tabindex="2">
                                            <span class="input-group-text  bg-white">
                                                <img src="{{ route('captcha').'?rand='.rand() }}" id="captcha_image2"
                                                     alt="captcha" style="height: 38px;">
                                            </span>
                                            <button type="button" class="input-group-text" onclick="refreshCaptcha2()" title="Refresh Captcha">
                                                <i class="fal fa-sync"></i>
                                            </button>
                                            @error('captcha')
                                                <div class="invalid-feedback d-block">@lang($message)</div>
                                            @enderror
                                        </div>
                                    </div>
                                @endif

                                @if((basicControl()->google_recaptcha) && (basicControl()->google_recaptcha_login))
                                    <div class="row mt-4 mb-3">
                                        <div
                                            class="g-recaptcha @error('g-recaptcha-response') is-invalid @enderror"
                                            data-sitekey="{{ config('google.recaptcha_site_key')}}"></div>
                                        @error('g-recaptcha-response')
                                        <span class="text-danger" role="alert">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endif

                                <div class="checkbox-input">
                                    <input type="checkbox" class="form-check-input">
                                    <label>@lang('Remember me on the device?')</label>
                                </div>
                                <button type="submit" class="btn-1 w-100 justify-content-center">@lang('Sign In')
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

