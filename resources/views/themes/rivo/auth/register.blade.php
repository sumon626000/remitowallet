@extends(template().'layouts.app')
@section('content')

    <section class="account-section bg--title" style="border-bottom: 1px solid #5f5f5f">
        <div class="container">
            <div class="row justify-content-center flex-wrap-reverse gy-4 align-items-center">
                @if(@$template)
                    <div class="col-lg-6 col-xl-5 col-xxl-4">
                        <div class="section__title text--white text-center text-lg-start">
                            <span class="section__cate">@lang(@$template['register_title'])</span>
                            <h3 class="section__title">@lang(@$template['register_sub_title'])</h3>
                            <p>
                                {{ __(@$template['register_description']) }}
                            </p>
                            <a href="{{ @$template['media']->register_button_link }}"
                               class="cmn--btn btn-outline btn-sm mt-3"><i class="las la-angle-left"></i>
                                @lang(@$template['register_button_name'])
                            </a>
                        </div>
                    </div>
                @endif
                <div class="col-lg-6 col-xxl-5">
                    <div class="account__wrapper bg--body">
                        <div class="account-logo mb-3">
                            <a href="{{ url('/') }}">
                                <img src="{{ getFile(basicControl()->logo_driver, basicControl()->logo) }}"
                                     alt="@lang(basicControl()->site_title)">
                            </a>
                        </div>
                        <form action="{{ route('register') }}" method="post" class="account-form">
                            @csrf
                            <div class="form--group ">
                                <input type="text" name="first_name"
                                       class="form-control form--control @error('first_name') is-invalid @enderror"
                                       id="first_name" value="{{ old('first_name') }}">
                                <label for="first_name" class="form--label prevent-select">@lang('Enter First Name')</label>
                                <div class="invalid-feedback">@error('first_name') @lang($message) @enderror</div>
                            </div>

                            <div class="form--group ">
                                <input type="text" name="last_name"
                                       class="form-control form--control @error('last_name') is-invalid @enderror"
                                       id="last_name" value="{{ old('last_name') }}">
                                <label for="last_name" class="form--label prevent-select">@lang('Enter Last Name')</label>
                                <div class="invalid-feedback">@error('last_name') @lang($message) @enderror</div>
                            </div>

                            <div class="form--group">
                                <input type="text" name="email"
                                       class="form-control form--control @error('email') is-invalid @enderror"
                                       id="email" value="{{ old('email') }}">
                                <label for="email"
                                       class="form--label prevent-select">@lang('Enter Email Address')</label>
                                <div class="invalid-feedback">@error('email') @lang($message) @enderror</div>
                            </div>

                            <div class="form--group">
                                <input type="text" name="username"
                                       class="form-control form--control @error('username') is-invalid @enderror"
                                       id="username" value="{{ old('username') }}">
                                <label for="username" class="form--label prevent-select">@lang('Enter Username')</label>
                                <div class="invalid-feedback">@error('username') @lang($message) @enderror</div>
                            </div>

                            @if($referral)
                                <div class="form--group">
                                    <input type="text" name="referral"
                                           class="form-control form--control @error('referral') is-invalid @enderror"
                                           id="username" value="{{ old('referral',$referral) }}">
                                    <label for="referral"
                                           class="form--label prevent-select">@lang('Enter Referral')</label>
                                    <div class="invalid-feedback">@error('referral') @lang($message) @enderror</div>
                                </div>
                            @endif

                            <div class="form--group">
                                <div class="input-group justify-content-start">
                                    <div class="input-group-prepend w50">
                                        <select name="phone_code" class="form--control country_code text-left">
                                            @foreach($countries as $value)
                                                <option value="{{$value['phone_code']}}"
                                                        data-name="{{$value['name']}}"
                                                        data-code="{{$value['code']}}"
                                                    {{$country_code == $value['code'] ? 'selected' : ''}}>
                                                    {{ __($value['phone_code']) }} <strong> ({{ __($value['name']) }}
                                                        )</strong>
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <input type="text" name="phone" class="form-control pl-5"
                                           value="{{old('phone')}}" placeholder="@lang('Your Phone Number')">
                                </div>
                                @error('phone')
                                <p class="text-danger  mt-1">@lang($message)</p>
                                @enderror
                            </div>

                            <div class="form--group">
                                <input type="password" name="password"
                                       class="form-control form--control @error('password') is-invalid @enderror"
                                       id="password" value="{{ old('password') }}">
                                <label for="password" class="form--label prevent-select">@lang('Password')</label>
                                <div class="invalid-feedback">@error('password') @lang($message) @enderror</div>
                            </div>

                            <div class="form--group">
                                <input type="password" name="password_confirmation" class="form-control form--control"
                                       id="password">
                                <label for="password_confirmation"
                                       class="form--label prevent-select">@lang('Repeat Password')</label>
                            </div>

                            @if(basicControl()->reCaptcha_status_registration)
                                <div class="form-group">
                                    {!! NoCaptcha::renderJs() !!}
                                    {!! NoCaptcha::display() !!}
                                    @error('g-recaptcha-response')
                                    <div class="alert alert-danger mt-1 mb-1">@lang($message)</div>
                                    @enderror
                                </div>
                            @endif
                            <div class="form--group mb-4">
                                <button type="submit"
                                        class="cmn--btn w-100 justify-content-center text--white border-0">@lang('Sign Up')</button>
                            </div>
                            <div class="form--group mb-0 text-center">
                                @lang('Already have an account?') <a href="{{ route('login') }}"
                                                                     class="text--base">@lang('Sign In')</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection

@push('css-lib')
    <link rel="stylesheet" href="{{ asset(template(true) . 'css/intlTelInput.min.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset(template(true) . 'js/intlTelInput.min.js') }}"></script>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endpush

@push('script')
    <script>

        $(document).ready(function () {

            const input = document.querySelector("#telephone");
            window.intlTelInput(input, {
                initialCountry: "bd",
                separateDialCode: true,
            });

            $('.iti__country-list li').on('click', function () {
                $("#country").val('+' + $(this).data('dial-code'));
            })

            const password = document.querySelector('.password');
            const passwordIcon = document.querySelector('.password-icon');

            passwordIcon.addEventListener("click", function () {
                if (password.type == 'password') {
                    password.type = 'text';
                    passwordIcon.classList.add('fa-eye-slash');
                } else {
                    password.type = 'password';
                    passwordIcon.classList.remove('fa-eye-slash');
                }
            })

            const confirmPassword = document.querySelector('.confirm_password');
            const confirmPasswordIcon = document.querySelector('.confirm-password-icon');

            confirmPasswordIcon.addEventListener("click", function () {
                if (confirmPassword.type == 'password') {
                    confirmPassword.type = 'text';
                    confirmPasswordIcon.classList.add('fa-eye-slash');
                } else {
                    confirmPassword.type = 'password';
                    confirmPasswordIcon.classList.remove('fa-eye-slash');
                }
            })
        });

        function refreshCaptcha() {
            let img = document.images['captcha_image'];
            img.src = img.src.substring(
                0, img.src.lastIndexOf("?")
            ) + "?rand=" + Math.random() * 1000;
        }

    </script>
@endpush
