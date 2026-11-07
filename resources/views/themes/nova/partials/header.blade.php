
@php
    if (getTheme() != basicControl()->theme) {
        $languages = \Cache::remember('active_languages', now()->addMinutes(60), function () {
            return App\Models\Language::query()->orderBy('default_status', 'desc')->where('status', 1)->get();
        });
    }
@endphp

<header class="main-header header-style-one">

    <!-- Header Lower -->
    <div class="header-lower">
        <div class="container">
            <div class="inner-container d-flex align-items-center justify-content-between">

                <div class="left-column">
                    <div class="logo-box">
                        <div class="logo">
                            <a href="{{ url('/') }}">
                                <img src="{{getFile(basicControl()->logo_driver, basicControl()->logo)}}"
                                     alt="@lang(basicControl()->site_title)">
                            </a>
                        </div>
                    </div>
                </div>

                <div class="right-column d-flex align-items-center">
                    <div class="nav-outer">
                        <div class="mobile-nav-toggler"><i class="fa-regular fa-bars-staggered"></i></div>
                        <nav class="main-menu navbar-expand-md navbar-light">
                            <div class="collapse navbar-collapse show clearfix" id="navbarSupportedContent">
                                <ul class="navigation">
                                    {!! renderHeaderMenu(getHeaderMenuData()) !!}
                                </ul>
                            </div>
                        </nav>
                    </div>

                    <div class="header-right-btn">
                        <div class="sign-up">
                            @guest
                                <div class="button-1">
                                    <a href="{{ route('login') }}" class="btn-1">@lang('Log In') <span></span></a>
                                </div>
                            @else
                                <div class="button-2">
                                <a href="{{ route('user.dashboard') }}" class="btn-1">@lang('Dashboard') <span></span></a>
                                </div>
                            @endguest
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Header Lower -->

    <!-- sticky header -->
    <div class="sticky-header">
        <div class="header-upper">
            <div class="container">
                <div class="inner-container d-flex align-items-center justify-content-between">
                    <div class="left-column d-flex align-items-center">
                        <div class="logo-box">
                            <div class="logo">
                                <a href="{{ url('/') }}">
                                    <img src="{{getFile(basicControl()->logo_driver, basicControl()->logo)}}"
                                         alt="@lang(basicControl()->site_title)">
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="nav-outer d-flex gap-5">
                        <div class="mobile-nav-toggler"><i class="fa-regular fa-bars-staggered"></i></div>
                        <nav class="main-menu navbar-expand-md navbar-light"></nav>
                        <div class="header-right-btn">
                            <div class="sign-up">
                                @guest
                                    <div class="button-1">
                                        <a href="{{ route('login') }}" class="btn-1">@lang('Log In') <span></span></a>
                                    </div>
                                @else
                                    <div class="button-2">
                                        <a href="{{ route('user.dashboard') }}" class="btn-1">@lang('Dashboard') <span></span></a>
                                    </div>
                                @endguest
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- sticky header -->

    <!-- mobile menu -->
    <div class="mobile-menu">
        <div class="menu-backdrop"></div>
        <div class="mobile-menu-header">
            <div class="nav-logo">
                <a href="{{ url('/') }}">
                    <img src="{{getFile(basicControl()->logo_driver, basicControl()->logo)}}"
                         alt="@lang(basicControl()->site_title)">
                </a>
            </div>
            <div class="close-btn"><span class="fal fa-times"></span></div>
        </div>

        <nav class="menu-box">
            <div class="menu-outer"><!--Here Menu Will Come Automatically Via Javascript / Same Menu as in Header--></div>
            <div class="header-mobile-btn d-md-none">
                <div class="sign-up">
                    @guest
                        <div class="button-1">
                            <a href="{{ route('login') }}" class="btn-1">@lang('Log In') <span></span></a>
                        </div>
                    @else
                        <div class="button-2">
                            <a href="{{ route('user.dashboard') }}" class="btn-1">@lang('Dashboard') <span></span></a>
                        </div>
                    @endguest
                </div>
            </div>
        </nav>

    </div>
    <!-- mobile menu -->

    <!-- Bottom Mobile Menu -->
    <ul class="nav bottom-nav fixed-bottom d-lg-none">
        <li class="nav-item">
            <a class="nav-link mobile-nav-toggler" href="#"><i class="fa-light fa-list"></i></a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ getLastSegment() == 'blogs' ? 'active' : '' }}" href="{{ url('/blogs') }}">
                <i class="fa-light fa-planet-ringed"></i></a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">
                <i class="fa-light fa-house"></i></a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request()->is('contact') ? 'active' : '' }}" href="{{ url('/contact') }}">
                <i class="fa-light fa-address-book"></i></a>
        </li>
        <li class="nav-item">
            <a class="nav-link" href="{{route('user.profile')}}"><i class="fa-light fa-user"></i></a>
        </li>
    </ul>
    <!-- Bottom Mobile Menu -->

</header>

