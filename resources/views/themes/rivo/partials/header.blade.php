
@php
    if (getTheme() != basicControl()->theme) {
        $languages = \Cache::remember('active_languages', now()->addMinutes(60), function () {
            return App\Models\Language::query()->orderBy('default_status', 'desc')->where('status', 1)->get();
        });
    }
@endphp


<!-- Navbar || Top-bar -->
<header class="bg--section">
    <div class="navbar-section">
        <div class="container">
            <div class="navbar-wrapper">
                <div class="logo">
                    <a href="{{ url('/') }}">
                        <img src="{{getFile(basicControl()->logo_driver, basicControl()->logo)}}"
                             alt="@lang(basicControl()->site_title)">
                    </a>
                </div>

                @if(isset($languages))
                    <div class="d-lg-none ms-auto me-3">
                        <div class="lang-change">
                            <select class="form-control selectLanguage" name="language"
                                    onchange="location = this.value;">
                                @foreach($languages as $item)
                                    <option value="{{ route('language', $item->short_name) }}"
                                            @if(app()->getLocale() == $item->short_name) selected @endif>
                                        @lang($item->name)
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @endif

                <div class="nav-toggle d-lg-none">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>

                <div class="nav-menu-area">
                    <div class="menu-close text--danger d-lg-none">
                        <i class="las la-times"></i>
                    </div>
                    <ul class="nav-menu">

                        {!! renderHeaderMenu(getHeaderMenuData()) !!}

                        @if (isset($languages))
                            <li class="d-none d-lg-block ms-lg-5">
                                <div class="lang-change">
                                    <select class="form-control selectLanguage" name="language"
                                            onchange="location = this.value;">
                                        @foreach($languages as $item)
                                            <option value="{{ route('language', $item->short_name) }}"
                                                    @if(app()->getLocale() == $item->short_name) selected @endif>
                                                @lang($item->name)
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </li>
                        @endif
                        <li>
                            @if(Auth::check())
                                <a href="javascript:void(0)" class="ms-lg-3 cmn--btn">@lang('Dashboard')</a>
                                <ul class="sub-nav">
                                    <li>
                                        <a href="{{ route('user.dashboard') }}">@lang('Dashboard')</a>
                                    </li>
                                    <li>
                                        <a href="{{ route('user.profile') }}">@lang('Profile')</a>
                                    </li>
                                    <li>
                                        <a type="button" data-bs-toggle="modal" data-bs-target="#logoutModal">
                                            @lang('Logout')
                                        </a>
                                    </li>
                                </ul>
                            @else
                                <a href="{{ route('login') }}" class="ms-lg-3 cmn--btn">@lang('Login Now')</a>
                            @endif
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>


<!-- Start Logout Modal -->
<div class="modal fade" id="logoutModal" tabindex="-1" aria-labelledby="logoutModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text--base" id="logoutModalLabel">@lang('Confirmation !')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @lang('Are you sure you want to logout?')
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">@lang('Close')</button>
                <a href="{{ route('logout') }}" type="button" class="btn cutombutton" onclick="event.preventDefault();
				document.getElementById('logout-form').submit();">@lang('Logout')</a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>
    </div>
</div>
<!-- End Logout Modal -->
