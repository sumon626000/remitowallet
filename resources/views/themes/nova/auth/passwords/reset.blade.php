@extends(template().'layouts.app')

@section('content')

    <section class="login">
        <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/login-bg.png') }}');"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="contact-container">
                        <h3>@lang('Reset Your Password')</h3>
                        <div class="contact-form">

                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ trans(session('status')) }}
                                    <button type="button" class="btn-close removeStatus" data-bs-dismiss="alert"
                                            aria-label="Close">
                                    </button>
                                </div>
                            @endif

                            <form action="{{ route('password.update') }}" method="post">
                                @csrf

                                <input type="hidden" name="token" value="{{ $token }}">
                                <input type="hidden" name="email" value="{{ $email ?? old('email') }}">

                                <div class="input-content">
                                    <label for="password" class="form-label">@lang('New Password')</label>
                                    <input type="password" name="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password" placeholder="@lang('Enter new password')">
                                    <div class="invalid-feedback">
                                        @error('password') @lang($message) @enderror
                                    </div>
                                </div>
                                <div class="input-content">
                                    <label for="password_confirmation" class="form-label">@lang('Confirm New Password')</label>
                                    <input type="password" name="password_confirmation"
                                           class="form-control @error('password_confirmation') is-invalid @enderror"
                                           id="password_confirmation" placeholder="@lang('Enter confirm password')">

                                    <div class="invalid-feedback">
                                        @error('password_confirmation') @lang($message) @enderror
                                    </div>
                                </div>

                                <button type="submit"  class="btn-1 w-100 justify-content-center">
                                    @lang('Reset Password')
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


@push('script')
    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif

@endpush
