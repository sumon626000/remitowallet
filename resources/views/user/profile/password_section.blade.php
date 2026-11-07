

<div id="passwordSection" class="card">
    <div class="card-header">
        <h2 class="card-title h4">@lang('Change your password')</h2>
    </div>
    <div class="card-body">
        <form action="{{ userRoute('change.password') }}" id="changePasswordForm" method="post" enctype="multipart/form-data">
            @csrf

            <div class="row mb-4">
                <label for="currentPassword" class="col-sm-3 col-form-label form-label">
                    @lang('Current password')
                </label>
                <div class="col-sm-9">
                    <div class="input-group input-group-merge">
                        <input type="password" class="js-toggle-password form-control" id="multiToggleCurrentPasswordLabel"
                               name="current_password" placeholder="@lang("Enter your current password")"
                               data-hs-toggle-password-options='{
                             "target": [".js-change-password-multi-0"],
                             "defaultClass": "bi-eye-slash",
                             "showClass": "bi-eye",
                             "classChangeTarget": "#showMultiPassIcon0"
                           }'>
                        <a class="js-change-password-multi-0 input-group-append input-group-text" href="javascript:void(0)">
                            <i id="showMultiPassIcon0"></i>
                        </a>
                    </div>
                    @error('current_password')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="row mb-4">
                <label for="newPassword" class="col-sm-3 col-form-label form-label">
                    @lang('New password')
                </label>
                <div class="col-sm-9">
                    <div class="input-group input-group-merge">
                        <input type="password" class="js-toggle-password form-control" id="multiToggleNewPasswordLabel"
                               name="password" placeholder="@lang("Enter new password")"
                               data-hs-toggle-password-options='{
                             "target": [".js-change-password-multi-1"],
                             "defaultClass": "bi-eye-slash",
                             "showClass": "bi-eye",
                             "classChangeTarget": "#showMultiPassIcon1"
                           }'>
                        <a class="js-change-password-multi-1 input-group-append input-group-text" href="javascript:void(0)">
                            <i id="showMultiPassIcon1"></i>
                        </a>
                    </div>
                    @error('password')<span class="text-danger">{{ $message }}</span>@enderror
                </div>
            </div>

            <div class="row mb-4">
                <label for="confirmNewPasswordLabel" class="col-sm-3 col-form-label form-label">
                    @lang('Confirm new password')
                </label>
                <div class="col-sm-9">
                    <div class="mb-3">
                        <div class="input-group input-group-merge">
                            <input type="password" class="js-toggle-password form-control"  name="password_confirmation" id="multiToggleNewPasswordLabel" placeholder="@lang("Confirm your new password")"
                                   data-hs-toggle-password-options='{
                                 "target": [".js-change-password-multi-2"],
                                 "defaultClass": "bi-eye-slash",
                                 "showClass": "bi-eye",
                                 "classChangeTarget": "#showMultiPassIcon2"
                               }'>
                            <a class="js-change-password-multi-2 input-group-append input-group-text" href="javascript:void(0)">
                                <i id="showMultiPassIcon2"></i>
                            </a>
                        </div>
                        @error('password_confirmation')<span class="text-danger">{{ $message }}</span>@enderror
                    </div>
                    @if(basicControl()->strong_password)
                        <h5>@lang('Password requirements:')</h5>
                        <p class="fs-6 mb-2">@lang('Ensure that these requirements are met:')</p>
                        <ul class="fs-6">
                            <li>@lang('Minimum 8 characters long - the more, the better')</li>
                            <li>@lang('At least one lowercase character')</li>
                            <li>@lang('At least one uppercase character')</li>
                            <li>@lang('At least one number, symbol, or whitespace character')</li>
                        </ul>
                    @endif
                </div>
            </div>
            <div class="d-flex justify-content-start">
                <button type="submit" class="btn btn-primary">@lang('Save Changes')</button>
            </div>
        </form>
    </div>
</div>

@push('js-lib')
    <script src="{{ asset("assets/admin/js/hs-toggle-password.js") }}"></script>
@endpush

@push('script')
    <script>
        "use strict";
        (function() {
            new HSTogglePassword('.js-toggle-password')
        })();
    </script>
@endpush
