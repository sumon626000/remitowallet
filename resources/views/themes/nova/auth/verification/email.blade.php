@extends(template().'layouts.app')
@section('title',__('Email Verification'))

@section('content')

    <section class="login">
        <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/login-bg.png') }}');"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="contact-container">
                        <h3>@lang('Email Verification')</h3>
                        <div class="mt-3 mb-3">
                            <p class="d-flex flex-wrap">@lang("Your Email Address is") {!! maskEmail(auth()->user()->email) !!}</p>
                        </div>
                        <div class="contact-form">
                            <form action="{{ route('user.mail.verify') }}" class="account-form" method="post">
                                @csrf
                                <div class="input-content">
                                    <label class="form-label" for="email-code">@lang('Enter Your Verification Code')</label>
                                    <input class="form-control @error('code') is-invalid @enderror"
                                           type="text" name="code" value="{{old('code')}}" placeholder="@lang('Code')"
                                           autocomplete="off" autofocus id="email-code">
                                    @error('code')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                    @error('error')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <button type="submit"  class="btn-1 w-100 justify-content-center">
                                    @lang('Submit')
                                    <i class="fa-light fa-arrow-right-long"></i> <span></span>
                                </button>
                            </form>
                        </div>
                        <div class="contact-form-footer">
                            <p>
                                @lang('Didn\'t get Code? Click to')
                                <a href="{{route('user.resend.code')}}?type=email">
                                    @lang('Resend code')
                                </a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
