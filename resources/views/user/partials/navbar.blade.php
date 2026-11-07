
<header id="header" class="navbar navbar-expand-lg navbar-fixed navbar-container navbar-bordered bg-white">
    <div class="navbar-nav-wrap">
        <!-- Logo -->
        <a class="navbar-brand" href="{{ route('user.dashboard') }}" aria-label="{{ $basicControl->site_title }}">
            <img class="navbar-brand-logo navbar-brand-logo-auto"
                 src="{{ getFile(in_array(session()->get('themeMode'),['auto',null])?$basicControl->admin_dark_mode_logo_driver : $basicControl->admin_logo_driver, in_array(session()->get('themeMode'),['auto',null])?$basicControl->admin_dark_mode_logo:$basicControl->admin_logo, true) }}"
                 alt="{{ $basicControl->site_title }} Logo"
                 data-hs-theme-appearance="default">
            <img class="navbar-brand-logo"
                 src="{{ getFile($basicControl->admin_dark_mode_logo_driver, $basicControl->admin_dark_mode_logo, true) }}"
                 alt="{{ $basicControl->site_title }} Logo"
                 data-hs-theme-appearance="dark">
            <img class="navbar-brand-logo-mini"
                 src="{{ getFile($basicControl->favicon_driver, $basicControl->favicon, true) }}"
                 alt="{{ $basicControl->site_title }} Logo"
                 data-hs-theme-appearance="default">
            <img class="navbar-brand-logo-mini"
                 src="{{ getFile($basicControl->favicon_driver, $basicControl->favicon, true) }}"
                 alt="Logo"
                 data-hs-theme-appearance="dark">
        </a>
        <!-- End Logo -->

        <div class="navbar-nav-wrap-content-start">
            <!-- Navbar Vertical Toggle -->
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="bi-arrow-bar-left navbar-toggler-short-align" data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>' data-bs-toggle="tooltip" data-bs-placement="right" title="Collapse"></i>
                <i class="bi-arrow-bar-right navbar-toggler-full-align" data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>' data-bs-toggle="tooltip" data-bs-placement="right" title="Expand"></i>
            </button>
            <!-- End Navbar Vertical Toggle -->

            <div class="dropdown ms-2">
                <div class="d-none d-lg-block">
                    <div class="input-group input-group-merge input-group-borderless input-group-hover-light navbar-input-group">
                        <div class="input-group-prepend input-group-text">
                            <i class="bi-search"></i>
                        </div>

                        <input type="search" class="js-form-search form-control global-search"
                               placeholder="@lang("Search for a menu")"
                               data-hs-form-search-options='{
                                   "clearIcon": "#clearSearchResultsIcon",
                                   "dropMenuElement": "#searchDropdownMenu",
                                   "dropMenuOffset": 20,
                                   "toggleIconOnFocus": true,
                                   "activeClass": "focus"
                                }'
                        >
                        <a class="input-group-append input-group-text" href="javascript:void(0)">
                            <i id="clearSearchResultsIcon" class="bi-x-lg d-none"></i>
                        </a>
                    </div>
                </div>

                <button
                    class="js-form-search js-form-search-mobile-toggle btn btn-ghost-secondary btn-icon rounded-circle d-lg-none"
                    type="button" data-hs-form-search-options='{
                       "clearIcon": "#clearSearchResultsIcon",
                       "dropMenuElement": "#searchDropdownMenu",
                       "dropMenuOffset": 20,
                       "toggleIconOnFocus": true,
                       "activeClass": "focus"
                     }'>
                    <i class="bi-search"></i>
                </button>
                <!-- End Input Group -->

                <!-- Card Search Content -->
                <div id="searchDropdownMenu"
                     class="hs-form-search-menu-content dropdown-menu dropdown-menu-form-search navbar-dropdown-menu-borderless">
                    <div class="card">
                        <!-- Body -->
                        <div class="card-body-height search-result">
                            <div class="d-lg-none">
                                <div class="input-group input-group-merge navbar-input-group mb-5">
                                    <div class="input-group-prepend input-group-text">
                                        <i class="bi-search"></i>
                                    </div>
                                    <input type="search" class="form-control global-search"
                                           placeholder="@lang("Search for a menu")" aria-label="@lang("Search for a menu")">
                                    <a class="input-group-append input-group-text" href="javascript:void(0);">
                                        <i class="bi-x-lg"></i>
                                    </a>
                                </div>
                            </div>
                            <span class="dropdown-header">@lang("Result")</span>
                            <div class="dropdown-divider"></div>
                            <div class="content"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Secondary Content -->
        <div class="navbar-nav-wrap-content-end">
            <ul class="navbar-nav">
                <a class="dropdown-item d-flex align-items-center toggle-layout" href="{{route('user.toggle.layout')}}">
                    <i class="bi-columns me-2 animate-icon" title="@lang('Change Layout')"></i>
                </a>
                @if(basicControl()->in_app_notification == 1)
                    <li class="nav-item d-none d-md-inline-block" id="pushNotificationArea">
                        <!-- Notification -->
                        <div class="dropdown">
                            <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle"
                                    id="navbarNotificationsDropdown" data-bs-toggle="dropdown"
                                    aria-expanded="false"
                                    data-bs-auto-close="outside" data-bs-dropdown-animation>
                                <i class="bi-bell"></i>
                                <span class="btn-status btn-sm-status btn-status-danger"></span>
                            </button>

                            <div
                                class="dropdown-menu dropdown-menu-end dropdown-card navbar-dropdown-menu navbar-dropdown-menu-borderless"
                                aria-labelledby="navbarNotificationsDropdown" style="width: 25rem;">
                                <div class="card">
                                    <!-- Header -->
                                    <div class="card-header card-header-content-between">
                                        <h4 class="card-title mb-0">@lang('Notifications')</h4>
                                    </div>
                                    <!-- End Header -->

                                    <!-- Body -->
                                    <div class="card-body-height">
                                        <!-- Tab Content -->
                                        <div class="tab-content" id="notificationTabContent">
                                            <div class="tab-pane fade show active" id="notificationNavOne"
                                                 role="tabpanel" aria-labelledby="notificationNavOne-tab">
                                                <!-- List Group -->
                                                <ul class="list-group list-group-flush navbar-card-list-group">
                                                    <!-- Item -->
                                                    <li class="list-group-item form-check-select"
                                                        v-for="(item, index) in items">
                                                        <div class="row">
                                                            <div class="col ms-n2">
                                                                <h5 class="mb-1">
                                                                    @{{ item.description.name }}</h5>
                                                                <p class="text-body fs-5">
                                                                    @{{ item.description.text }}</p>
                                                                <small class="col-auto text-muted text-cap"
                                                                       v-cloak>@{{ item.formatted_date }}
                                                                </small>
                                                            </div>
                                                        </div>
                                                        <!-- End Row -->

                                                        <a class="stretched-link"
                                                           :href="item.description.link"></a>
                                                    </li>
                                                    <!-- End Item -->
                                                </ul>
                                                <!-- End List Group -->
                                                <div class="text-center p-4" v-if="items.length < 1">
                                                    <img class="dataTables-image mb-3"
                                                         src="{{ asset('assets/admin/img/oc-error.svg') }}"
                                                         alt="..." data-hs-theme-appearance="default">
                                                    <img class="dataTables-image mb-3"
                                                         src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                                                         alt="..." data-hs-theme-appearance="dark">
                                                    <p class="mb-0">@lang("No Notifications Found")</p>
                                                </div>

                                            </div>
                                        </div>
                                        <!-- End Tab Content -->
                                    </div>
                                    <!-- End Body -->

                                    <!-- Card Footer -->
                                    <a class="card-footer text-center" href="javascript:void(0)"
                                       v-if="items.length > 0" @click.prevent="readAll">
                                        @lang("Clear all notifications") <i class="bi-chevron-right"></i>
                                    </a>
                                    <!-- End Card Footer -->
                                </div>
                            </div>
                        </div>
                        <!-- End Notification -->
                    </li>
                @endif


                <li class="nav-item">
                    <!-- Account -->
                    <div class="dropdown gap-2">
                        <a class="navbar-dropdown-account-wrapper" href="javascript:;"
                           id="accountNavbarDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                           data-bs-auto-close="outside" data-bs-dropdown-animation>
                            <div class="avatar avatar-sm avatar-circle">
                                <img class="avatar-img"
                                     src="{{getFile(auth()->user()->image_driver,auth()->user()->image)}}"
                                     alt="{{auth()->user()->fullname}}">
                                <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                            </div>
                        </a>

                        <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu
                            navbar-dropdown-menu-borderless navbar-dropdown-account"
                            aria-labelledby="accountNavbarDropdown" style="width: 16rem;">
                            <div class="dropdown-item-text">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle">
                                        <img class="avatar-img"
                                             src="{{getFile(auth()->user()->image_driver,auth()->user()->image)}}"
                                             alt="{{auth()->user()->fullname}}">
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-0">{{auth()->user()->name}}</h5>
                                        <p class="card-text text-body">{{auth()->user()->email}}</p>
                                    </div>
                                </div>
                            </div>

                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="{{ route('user.profile') }}">
                                <i class="bi-person me-2"></i> @lang('Profile & Account')
                            </a>
                            <a class="dropdown-item" href="{{ route('user.qr.code') }}">
                                <i class="bi-qr-code-scan me-2"></i> @lang('QR Code')
                            </a>
                            <a class="dropdown-item" href="{{ route('user.ticket.list') }}">
                                <i class="bi-ticket me-2"></i> @lang('Ticket')
                            </a>
                            <div class="dropdown-divider"></div>

                            <a class="dropdown-item" href="{{ route('logout') }}" onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">
                                <i class="bi-box-arrow-right me-2"></i> @lang('Sign Out')
                            </a>
                            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>
                        </div>
                    </div>
                    <!-- End Account -->
                </li>


            </ul>
        </div>
        <!-- End Secondary Content -->
    </div>
</header>
