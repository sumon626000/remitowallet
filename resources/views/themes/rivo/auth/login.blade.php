@extends(template().'layouts.app')
@section('title',__('Login'))
@section('content')
    <section class="account-section bg--title" style="border-bottom: 1px solid #5f5f5f">
        <div class="container">
            <div class="row justify-content-center flex-wrap-reverse gy-4 align-items-center">

                @if(@$template)
                    <div class="col-lg-6 col-xl-5 col-xxl-4">
                        <div class="section__title text--white text-center text-lg-start">
                            <span class="section__cate">@lang(@$template['login_title'])</span>
                            <h3 class="section__title">@lang(@$template['login_sub_title'])</h3>
                            <p>
                                {{ __(@$template['login_description']) }}
                            </p>
                            <a href="{{ @$template['media']->login_button_link }}"
                               class="cmn--btn btn-outline btn-sm mt-3"><i class="las la-angle-left"></i>
                                @lang(@$template['login_button_name'])
                            </a>
                        </div>
                    </div>
                @endif

                <div class="col-lg-6 col-xxl-5">
                    <div class="account__wrapper bg--body">
                        <div class="account-logo">
                            <a href="{{ url('/') }}">
                                <img src="{{ getFile(basicControl()->logo_driver, basicControl()->logo) }}"
                                     alt="@lang(basicControl()->site_title)">
                            </a>
                        </div>

                        @if (session('status'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ trans(session('status')) }}
                                <button type="button" class="btn-close removeStatus" data-bs-dismiss="alert"
                                        aria-label="Close">
                                </button>
                            </div>
                        @endif

                        <form class="account-form" action="{{ route('login') }}" method="post">
                            @csrf
                            <div class="form--group">
                                <input type="text" name="username" value="{{ old('username', config('demo.IS_DEMO') ? (request()->username ?? 'demouser') : '') }}"
                                       class="form-control form--control @error('username') is-invalid @enderror
                                        @error('email') is-invalid @enderror" id="username">
                                <label for="username"
                                       class="form--label prevent-select">@lang('Username or Email')</label>

                                <div class="invalid-feedback">
                                    @error('username') @lang($message) @enderror
                                    @error('email') @lang($message) @enderror
                                </div>
                            </div>
                            <div class="form--group">
                                <input type="password" name="password" value="{{ old('password', config('demo.IS_DEMO') ? (request()->password ?? 'demouser') : '') }}"
                                       class="form-control form--control @error('password') is-invalid @enderror"
                                       id="password">
                                <label for="password" class="form--label prevent-select">@lang('Password')</label>

                                <div class="invalid-feedback">
                                    @error('password') @lang($message) @enderror
                                </div>
                            </div>

                            @if(basicControl()->manual_recaptcha &&  basicControl()->reCaptcha_status_login)
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
                                        <div class="invalid-feedback d-block">
                                            @lang($message)
                                        </div>
                                        @enderror
                                    </div>
                                </div>
                            @endif

                            @if((basicControl()->google_recaptcha) && (basicControl()->google_reCaptcha_status_login))
                                <div class="row mt-4 mb-3">
                                    <div class="g-recaptcha @error('g-recaptcha-response') is-invalid @enderror"
                                        data-sitekey="{{ config('google.recaptcha_site_key')}}"></div>
                                    @error('g-recaptcha-response')
                                    <span class="text-danger" role="alert">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endif

                            <div class="form--group checkgroup d-flex flex-row justify-content-between">
                                <div class="form-check">
                                    <input class="form-check-input form--check-input" type="checkbox" id="check1">
                                    <label class="form-check-label" for="check1">
                                        @lang('Remember Me')
                                    </label>
                                </div>
                                <div>
                                    @if (Route::has('password.request'))
                                        <a href="{{ route('password.request') }}"
                                           class="text--base">@lang('Forgot Your Password')?</a>
                                    @endif
                                </div>
                            </div>

                            <div class="form--group mb-4">
                                <button class="cmn--btn w-100 justify-content-center text--white border-0"
                                        type="submit" >@lang('Sign In')</button>
                            </div>
                            <div class="form--group mb-0 text-center">
                                @lang("Don't have an account?")
                                <a href="{{ route('register') }}" class="text--base">@lang('Sign Up')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

