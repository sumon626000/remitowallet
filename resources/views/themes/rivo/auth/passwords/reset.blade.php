@extends(template().'layouts.app')

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

                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ __(session('status')) }}
                            </div>
                        @endif

                        <form class="account-form" action="{{ route('password.update') }}" method="post">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">
                            <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

                            <div class="form--group">
                                <input type="password" name="password"
                                       class="form-control form--control @error('password') is-invalid @enderror"
                                       id="password">
                                <label for="password" class="form--label prevent-select">@lang('New Password')</label>
                                <div class="invalid-feedback">
                                    @error('password') @lang($message) @enderror
                                </div>
                            </div>
                            <div class="form--group">
                                <input type="password" name="password_confirmation"
                                       class="form-control form--control @error('password_confirmation') is-invalid @enderror"
                                       id="password_confirmation">
                                <label for="password_confirmation"
                                       class="form--label prevent-select">@lang('Confirm New Password')</label>
                                <div class="invalid-feedback">
                                    @error('password_confirmation') @lang($message) @enderror
                                </div>
                            </div>

                            <div class="form--group mb-4">
                                <button type="submit"
                                        class="cmn--btn w-100 justify-content-center text--white border-0">@lang('Reset Password')</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
