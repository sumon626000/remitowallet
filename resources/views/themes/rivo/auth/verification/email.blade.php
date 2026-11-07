@extends(template().'layouts.app')
@section('title',__('Email Verification'))

@section('content')

    <section class="account-section bg--title" style="border-bottom: 1px solid #5f5f5f">
        <div class="container">
            <div class="row justify-content-center flex-wrap-reverse gy-4 align-items-center">

                <div class="col-lg-6 col-xxl-5">
                    <div class="account__wrapper bg--body">
                        <div class="account-logo">
                            <a href="{{ url('/') }}">
                                <img src="{{ getFile(basicControl()->logo_driver, basicControl()->logo) }}"
                                     alt="@lang(basicControl()->site_title)">
                            </a>
                        </div>
                        <form class="account-form" action="{{ route('user.mailVerify') }}" method="post">
                            @csrf
                            <div class="form--group">
                                <input type="text" name="code"
                                       class="form-control form--control @error('code') is-invalid @enderror @error('error') is-invalid @enderror">
                                <label for="code" class="form--label prevent-select">@lang('Code')</label>

                                <div class="invalid-feedback">
                                    @error('code') @lang($message) @enderror
                                    @error('error') @lang($message) @enderror
                                </div>
                            </div>
                            <div class="form--group mb-4">
                                <button type="submit"
                                        class="cmn--btn w-100 justify-content-center text--white border-0">@lang('Submit')</button>
                            </div>
                            @if (Route::has('user.resendCode'))
                                <div class="form--group mb-0 text-center">
                                    @lang("Don't have an account?") <a href="{{route('user.resendCode')}}?type=email"
                                                                       class="text--base">@lang('Resend code')</a>
                                    @error('resend')
                                    <p class="text-danger mt-1">@lang($message)</p>
                                    @enderror
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
