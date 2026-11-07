@extends(template().'layouts.app')
@section('title',__('Login'))
@section('content')

    <section class="login">
        <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/login-bg.png') }}');"></div>
        <div class="container">
            <div class="row">
                <div class="col-xl-6 offset-xl-3">
                    <div class="contact-container">
                        <h3>@lang(@$template['register_title'] ?? 'Create a new account')</h3>
                        <div class="contact-form">
                            <form action="{{ route('register') }}" method="post">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-6 col-md-6">
                                        <div class="input-content">
                                            <input type="text" name="first_name" value="{{old('first_name')}}"
                                                   class="form-control cmn-input" placeholder="@lang('First Name')">
                                            @error('first_name')<span class="text-danger">{{$message}}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <div class="input-content">
                                            <input type="text" name="last_name" value="{{old('last_name')}}"
                                                   class="form-control cmn-input" placeholder="@lang('Last Name')">
                                            @error('last_name')<span class="text-danger">{{$message}}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <div class="input-content">
                                            <input type="email" name="email" value="{{old('email')}}"
                                                   class="form-control cmn-input" placeholder="@lang('Email')">
                                            @error('email')<span class="text-danger">{{$message}}</span>@enderror
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <div class="input-content">
                                            <input type="text" name="username" value="{{old('username')}}"
                                                   class="form-control" placeholder="@lang('Username')">
                                            @error('username')<span class="text-danger">{{$message}}</span>@enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-12 col-md-12">
                                        <div class="input-content">
                                            <div class="register-form">
                                                <input type="number" id="telephone" class="form-control cmn-input" name="phone"
                                                       placeholder="@lang('Enter your valid phone number')">
                                                <input type="hidden" name="phone_code" id="phoneCode"/>
                                                <input type="hidden" name="country" id="countryName"/>
                                                <input type="hidden" name="country_code" id="countryCode"/>
                                                @error('phone')
                                                <span class="text-danger">@lang($message)</span>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6">
                                        <div class="input-content">
                                            <div class="password-box">
                                                <input type="password" name="password" value="{{ old('password') }}"
                                                       class="form-control password" placeholder="@lang('Password')">
                                                <i class="password-icon fa-regular fa-eye"></i>
                                            </div>
                                            @error('password')<span class="text-danger">{{$message}}</span>@enderror
                                        </div>
                                    </div>

                                    <div class="col-lg-6 col-md-6">
                                        <div class="input-content">
                                            <div class="password-box">
                                                <input type="password" name="password_confirmation"
                                                       value="{{ old('password_confirmation') }}"
                                                       class="form-control password" placeholder="@lang('Confirm Password')">
                                                <i class="password-icon fa-regular fa-eye"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if(basicControl()->manual_recaptcha &&  basicControl()->manual_recaptcha_register)
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

                                @if((basicControl()->google_recaptcha) && (basicControl()->google_recaptcha_register))
                                    <div class="row mt-4 mb-3">
                                        <div class="g-recaptcha @error('g-recaptcha-response') is-invalid @enderror"
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
                                <button type="submit"  class="btn-1 w-100 justify-content-center">
                                    @lang('Sign Up')  <i class="fa-light fa-arrow-right-long"></i> <span></span>
                                </button>
                            </form>
                        </div>
                        <div class="contact-form-footer">
                            <p>@lang("Already have an account?") <a href="{{ route('login') }}">@lang('Login here')</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('style')
    <style>
        .password-box, .password-box-two {
            position: relative;
        }

        .password-box i, .password-box-two i {
            position: absolute;
            top: 16px;
            right: 16px;
            cursor: pointer;
            color: var(--white);
        }
    </style>
@endpush

@push('js-lib')
    <script src="{{ asset(template(true) . 'js/intlTelInput.min.js')}}"></script>
    @if((basicControl()->google_recaptcha) && (basicControl()->google_reCaptcha_status_registration))
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    @endif
@endpush
@push('extra_scripts')
    <script>
        const input = document.querySelector("#telephone");
        const iti = window.intlTelInput(input, {
            initialCountry: "bd",
            separateDialCode: true,
        });

        function updateCountryInfo() {
            const selectedCountryData = iti.getSelectedCountryData();
            const phoneCode = selectedCountryData.dialCode;
            const countryName = selectedCountryData.name;
            const countryCode = selectedCountryData.iso2;
            $('#phoneCode').val(phoneCode)
            $('#countryName').val(countryName)
            $('#countryCode').val(countryCode)
        }

        input.addEventListener("countrychange", updateCountryInfo);
        updateCountryInfo();

        function refreshCaptcha() {
            let img = document.images['captcha_image'];
            img.src = img.src.substring(
                0, img.src.lastIndexOf("?")
            ) + "?rand=" + Math.random() * 1000;
        }

        function refreshCaptcha2() {
            let img = document.images['captcha_image2'];
            img.src = img.src.substring(
                0, img.src.lastIndexOf("?")
            ) + "?rand=" + Math.random() * 1000;
        }

    </script>
@endpush
