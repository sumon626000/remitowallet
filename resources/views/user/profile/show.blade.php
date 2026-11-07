@extends(userLayout())
@section('title',__('Profile'))
@section('content')

    <div class="content {{ containerClass() }}">
        <!-- Page Header -->
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang('Dashboard')</a></li>
                            <li class="breadcrumb-item active">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang('Profile')</a></li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Edit Profile')</h1>
                </div>
            </div>
        </div>

        <div class="row">

            @include('user.profile.profileNav')

            <div class="col-lg-9">
                <div class="d-grid gap-3 gap-lg-5">
                    <form action="{{ userRoute('profile') }}" method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="profile-cover">
                            <div class="profile-cover-img-wrapper">
                                <img id="profileCoverImg" class="profile-cover-img" alt="..."
                                     src="{{ asset('assets/admin/img/img1.jpg') }}">
                            </div>
                        </div>

                        <label class="avatar avatar-xxl avatar-circle avatar-uploader profile-cover-avatar"
                               for="editAvatarUploaderModal">
                            <img id="editAvatarImgModal" class="avatar-img"
                                 src="{{getFile($userProfile->image_driver,$userProfile->image)}}"
                                 alt="...">
                            <input type="file" class="js-file-attach avatar-uploader-input" id="editAvatarUploaderModal"
                                   name="profile_picture"
                                   data-hs-file-attach-options='{
                                    "textTarget": "#editAvatarImgModal",
                                    "mode": "image",
                                    "targetAttr": "src",
                                    "allowTypes": [".png", ".jpeg", ".jpg"]
                                 }'>
                            <span class="avatar-uploader-trigger">
                          <i class="bi-pencil-square avatar-uploader-icon shadow-sm"></i>
                        </span>
                        </label>

                        <div class="card">
                            <div class="card-header">
                                <h2 class="card-title h4">@lang('Basic information')</h2>
                            </div>
                            <div class="card-body">
                                <div class="row mb-4">
                                    <label for="firstNameLabel"
                                           class="col-sm-3 col-form-label form-label">@lang('Full name')</label>
                                    <div class="col-sm-9">
                                        <div class="input-group input-group-sm-vertical">
                                            <input type="text" name="firstname"
                                                   placeholder="@lang('Your Firstname')"
                                                   value="{{ old('firstname', $userProfile->firstname) }}"
                                                   class="form-control" id="firstname" required>
                                            <input type="text" name="lastname"
                                                   placeholder="@lang('Your Lastname')"
                                                   value="{{ old('lastname', $userProfile->lastname ) }}"
                                                   class="form-control" id="lastname" required>
                                        </div>
                                        @error('firstname')<span class="invalid-feedback d-inline">{{ $message }}</span>@enderror
                                        @error('lastname')<span class="invalid-feedback d-inline">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="username" class="col-sm-3 col-form-label form-label">@lang('Username')</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="username" id="username" required
                                               placeholder="@lang('Username')" value="{{ old('username', $userProfile->username ) }}">
                                        @error('username')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <label for="email" class="col-sm-3 col-form-label form-label">@lang('Email Address')</label>
                                    <div class="col-sm-9">
                                        <input type="email" class="form-control" name="email" id="email" required
                                               placeholder="@lang('demo@email.com')" value="{{ old('email', $userProfile->email ) }}">
                                        @error('email')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>


                                <div class="row mb-4">
                                    <label for="phoneLabel"
                                           class="col-sm-3 col-form-label form-label">@lang('Phone')</label>
                                    <div class="col-sm-9">
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="spanPhoneCode"></span>

                                            <input type="text" class="js-input-mask form-control" name="phone"
                                                   id="phoneLabel" placeholder="Phone"
                                                   value="{{ old('phone', $userProfile->phone) }}"
                                                   autocomplete="off">
                                        </div>
                                        @error('phone')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="locationLabel"
                                           class="col-sm-3 col-form-label form-label">@lang('Country')</label>
                                    <div class="col-sm-9">
                                        <div class="tom-select-custom mb-4">
                                            <select class="js-select form-select countryChange" id="locationLabel"
                                                    name="country">
                                                @forelse(config('country') as $country)
                                                    <option value="{{ $country['name'] }}"
                                                            data-country-code="{{ $country['code'] }}"
                                                            data-phone-code="{{ $country['phone_code'] }}"
                                                            {{ $country['code'] == $userProfile->country_code ? 'selected' : '' }}
                                                            data-option-template='<span class="d-flex align-items-center"><img class="avatar avatar-xss avatar-circle me-2" src="{{ asset($country['flag']) }}" alt="Afghanistan Flag" /><span class="text-truncate">{{ $country['name'] }}</span></span>'>
                                                        @lang($country['name'])
                                                    </option>
                                                @empty
                                                @endforelse
                                            </select>
                                            <input type="hidden" name="phone_code" id="phoneCode" value="{{ old('phone_code') }}"/>
                                            <input type="hidden" name="country" id="countryName" value="{{ old('country') }}"/>
                                            <input type="hidden" name="country_code" id="countryCode" value="{{ old('country_code') }}"/>
                                            @error('country')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="mb-3">
                                            <div class="input-group input-group-sm-vertical">
                                                <input type="text" class="form-control" name="city" id="cityLabel"
                                                       placeholder="City" aria-label="City"
                                                       value="{{ old('city', $userProfile->city) }}" autocomplete="off">
                                                <input type="text" class="form-control" name="state" id="stateLabel"
                                                       placeholder="State" aria-label="State"
                                                       value="{{ old('state', $userProfile->state) }}">

                                                <input type="text" class="js-input-mask form-control" name="zip_code"
                                                       id="zipCodeLabel" placeholder="Zip code" aria-label="Zip code"
                                                       value="{{ old('zip_code', $userProfile->zip_code) }}" autocomplete="off">
                                            </div>
                                            @error('city')
                                            <span class="invalid-feedback d-inline">{{ $message }}</span>
                                            @enderror
                                            @error('state')
                                            <span class="invalid-feedback d-inline">{{ $message }}</span>
                                            @enderror
                                            @error('zip_code')
                                            <span class="invalid-feedback d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="language" class="col-sm-3 col-form-label form-label">@lang('Language')</label>
                                    <div class="col-sm-9">
                                        <div class="tom-select-custom mb-4">
                                            <select class="js-select form-select" id="language" name="language">
                                                @foreach($languages as $language)
                                                    <option value="{{ $language->id }}"
                                                        {{ old('language', $userProfile->language_id) == $language->id ? 'selected' : '' }}>
                                                        {{ __($language->name) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('language')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <label for="timezone" class="col-sm-3 col-form-label form-label">@lang('Time Zone')</label>
                                    <div class="col-sm-9">
                                        <div class="tom-select-custom mb-4">
                                            <select class="js-select form-select" id="timezone" name="timezone">
                                                @foreach(timezone_identifiers_list() as $key => $value)
                                                    <option value="{{$value}}"
                                                        @selected(old('timezone',$userProfile->time_zone) == $value)
                                                    > {{ $value }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        @error('timezone')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="addressLine1Label" class="col-sm-3 col-form-label form-label">
                                        @lang('Address line 1')</label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="address_one"
                                               id="addressLine1Label" placeholder="Address One"
                                               aria-label="Your address"
                                               value="{{ old('address_one', $userProfile->address_one) }}" autocomplete="off">
                                        @error('address_one')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="row mb-4">
                                    <label for="addressLine2Label" class="col-sm-3 col-form-label form-label">
                                        @lang('Address line 2')
                                        <span class="form-label-secondary">(@lang("Optional"))</span></label>
                                    <div class="col-sm-9">
                                        <input type="text" class="form-control" name="address_two"
                                               id="addressLine2Label" placeholder="Address Two"
                                               aria-label="Address Two"
                                               value="{{ old('address_two', $userProfile->address_two) }}" autocomplete="off">
                                        @error('address_two')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="d-flex justify-content-start">
                                    <button type="submit" class="btn btn-primary">@lang('Save changes')</button>
                                </div>
                            </div>
                        </div>
                        <!-- End Card -->
                    </form>

                    @include('user.profile.password_section')
                    @include('user.profile.recent_devices_section')

                </div>
                <div id="stickyBlockEndPoint"></div>
            </div>
        </div>
    </div>


@endsection


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/hs-file-attach.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hs-sticky-block.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/hs-scrollspy.min.js') }}"></script>
    <script src="{{ asset("assets/admin/js/hs-toggle-password.js") }}"></script>
@endpush
@push('script')
    <script>
        'use strict'

        $(document).ready(function () {

            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 250
            })
            new HSFileAttach('.js-file-attach')
            new HSStickyBlock('.js-sticky-block', {
                targetSelector: document.getElementById('header').classList.contains('navbar-fixed') ? '#header' : null
            })
            new bootstrap.ScrollSpy(document.body, {
                target: '#navbarSettings',
                offset: 100
            })
            new HSScrollspy('#navbarVerticalNavMenu', {
                breakpoint: 'lg',
                scrollOffset: -20
            })

            function updateCountryInfo() {
                let selectedOption = $('.countryChange option:selected');
                $('#phoneCode').val(selectedOption.data('phone-code'));
                $('#countryName').val(selectedOption.val());
                $('#countryCode').val(selectedOption.data('country-code'));
                $('#spanPhoneCode').text(selectedOption.data('phone-code'));
            }
            $('.countryChange').change(updateCountryInfo);
            updateCountryInfo();
        })

    </script>

    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush
