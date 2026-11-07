@extends(userLayout())
@section('title',__('2 Step Security'))

@section('content')

    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('2 Step Security')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            @if(auth()->user()->two_fa)
                <div class="col-lg-6 col-md-6 mb-3">
                    <div class="card text-center search-bar">
                        <div class="card-header">
                            <h5>@lang('Two Factor Authenticator')</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mx-auto text-center mt-3">
                                <img class="w-40 mx-auto" src="{{$qrCodeUrl}}" alt="...">
                            </div>

                            <div class="input-group mb-3 mt-2">
                                <input type="text" class="form-control" id="referralURL" value="{{ $secret }}">
                                <button type="button" class="input-group-text copy-btn gap-1"
                                        onclick="copyToClipboard()" >
                                    <i class="bi-clipboard"></i> @lang('Copy')
                                </button>
                            </div>

                            <div class="form-group mx-auto text-center mt-3">
                                <a href="javascript:void(0)" class="w-100 btn btn-primary"
                                   data-bs-toggle="modal"
                                   data-bs-target="#disableModal">@lang('Disable Two Factor Authenticator')</a>
                            </div>
                        </div>

                    </div>
                </div>
            @else
                <div class="col-lg-6 mb-3">
                    <div class="card search-bar text-center br-4">
                        <div class="card-header">
                            <h5>@lang('Two Factor Authenticator')</h5>
                        </div>
                        <div class="card-body">
                            <div class="form-group mx-auto text-center mt-3 ">
                                <img class="mx-auto w-40" src="{{$qrCodeUrl}}" alt="...">
                            </div>

                            <div class="input-group mb-3 mt-2">
                                <input type="text" class="form-control" id="referralURL" value="{{ $secret }}">
                                <button type="button" class="input-group-text copy-btn gap-1"
                                        onclick="copyToClipboard()" >
                                    <i class="bi-clipboard"></i> @lang('Copy')
                                </button>
                            </div>

                            <div class="form-group mx-auto text-center mt-3">
                                <a href="javascript:void(0)" class="btn btn-primary w-100"
                                   data-bs-toggle="modal" data-bs-target="#enableModal">
                                    @lang('Enable Two Factor Authenticator')</a>
                            </div>

                        </div>
                    </div>
                </div>
            @endif
            <div class="col-md-6 mb-3">
                <div class="card text-center h-100">
                    <div class="card-header">
                        <h5>@lang('Google Authenticator')</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="text-uppercase my-3">@lang('Use Google Authenticator to Scan the QR code  or use the code')</h6>
                        <p class="p-2">@lang('Google Authenticator is a multifactor app for mobile devices. It generates timed codes used during the 2-step verification process. To use Google Authenticator, install the Google Authenticator application on your mobile device.')</p>
                        <a class="btn btn-primary mt-3"
                           href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2&hl=en"
                           target="_blank">@lang('DOWNLOAD APP')</a>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection

@push('loadModal')
    <!-- Enable Modal -->
    <div class="modal fade user-modal" id="enableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Verify Your OTP')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{userRoute('twoStepEnable')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="input-box col-md-12">
                            <input type="hidden" name="key" value="{{$secret}}">
                            <input type="text" class="form-control" name="code"
                                   placeholder="@lang('Enter Google Authenticator Code')" autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Verify')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Disable Modal -->
    <div class="modal fade user-modal" id="disableModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Verify that\'s you, to Disable 2FA')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{userRoute('twoStepDisable')}}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="password-box input-group input-group-merge">
                            <input name="password" type="password" class="form-control password"
                                   id="currentPassword"
                                   value="{{ old('password') }}"
                                   placeholder="{{ trans('Enter Your Password') }}"
                                   autocomplete="off">
                            <i class="password-icon fa-regular fa-eye toggle-password input-group-append input-group-text"></i>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Verify')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush


@push('script')
    <script>
        $(document).ready(() => {
            $('.toggle-password').on('click', function () {
                const passwordInput = $(this).prev('.password');
                const passwordType = passwordInput.attr('type');
                passwordInput.attr('type', passwordType === 'password' ? 'text' : 'password');
                $(this).toggleClass('fa-eye-slash', passwordType === 'password');
            });
        });

        function copyToClipboard() {
            let shareLinkInput = document.getElementById('referralURL');

            shareLinkInput.select();
            document.execCommand('copy');
            Notiflix.Notify.success(`Copied: ${shareLinkInput.value}`);

            let copyButton = document.querySelector('.copy-btn');
            copyButton.innerHTML = `{{trans('copied')}} !! <i class="bi-clipboard-check"></i>`;
            setTimeout(function() {
                copyButton.innerHTML = `<i class="bi-clipboard"></i> {{trans('copy')}}`;
            }, 5000);
        }
    </script>
@endpush

