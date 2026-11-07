@extends(template().'layouts.app')
@section('title',__('2 Step Verification'))

@section('content')

    <section class="login">
        <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/login-bg.png') }}');"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg-6 offset-lg-3">
                    <div class="contact-container">
                        <h3>@lang('Two Step Verification')</h3>
                        <div class="contact-form">
                            <form action="{{ route('user.twoFA-Verify') }}" class="account-form" method="post">
                                @csrf
                                <div class="input-content">
                                    <label class="form-label" for="email-code">@lang('Enter 2FA Code')</label>
                                    <input class="form-control @error('code') is-invalid @enderror"
                                           type="text" name="code" value="{{old('code')}}" placeholder="@lang('Code')"
                                           autocomplete="off" autofocus>
                                    @error('code')<span class="text-danger  mt-1">{{ $message }}</span>@enderror
                                </div>
                                <button type="submit"  class="btn-1 w-100 justify-content-center">
                                    @lang('Submit')
                                    <i class="fa-light fa-arrow-right-long"></i> <span></span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection
