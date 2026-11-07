<!-- ========== HEADER ========== -->
<header id="header" class="navbar navbar-expand-lg navbar-bordered navbar-spacer-y-0 flex-lg-column">
    <div class="navbar-dark w-100 bg-dark py-2">
        <div class="container">
            <div class="navbar-nav-wrap">
                <!-- Logo -->
                <a class="navbar-brand" href="{{url('/')}}" aria-label="Front">
                    <img class="navbar-brand-logo"
                         src="{{getFile(basicControl()->footer_logo_driver, basicControl()->footer_logo)}}"
                         alt="{{basicControl()->site_title}}">
                </a>
                <!-- End Logo -->

                <!-- Content Start -->
                <div class="navbar-nav-wrap-content-start">

                    <!-- Search Form -->
                    @include('user.partials.searchForm')
                    <!-- End Search Form -->
                </div>
                <!-- End Content Start -->

                <!-- Content End -->
                <div class="navbar-nav-wrap-content-end">
                    <!-- Navbar -->
                    <ul class="navbar-nav">

                        @if(basicControl()->in_app_notification == 1)
                            <li class="nav-item d-none d-md-inline-block" id="pushNotificationArea">
                                <!-- Notification -->
                                <div class="dropdown">
                                    <button type="button" class="btn btn-ghost-light btn-icon rounded-circle"
                                            id="navbarNotificationsDropdown" data-bs-toggle="dropdown"
                                            aria-expanded="false"
                                            data-bs-auto-close="outside" data-bs-dropdown-animation>
                                        <i class="bi-bell"></i>
                                        <span v-if="items.length > 0"
                                              class="btn-status btn-sm-status btn-status-danger"></span>
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
                            <!-- Style Switcher -->
                            <div class="dropdown ">
                                <button type="button" class="btn btn-ghost-light btn-icon rounded-circle"
                                        id="selectThemeDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                        data-bs-dropdown-animation>
                                </button>

                                <div
                                    class="dropdown-menu dropdown-menu-end navbar-dropdown-menu navbar-dropdown-menu-borderless"
                                    aria-labelledby="selectThemeDropdown">
                                    <a class="dropdown-item" href="javascript:void(0)" data-icon="bi-moon-stars"
                                       data-value="auto">
                                        <i class="bi-moon-stars me-2"></i>
                                        <span class="text-truncate"
                                              title="Auto (system default)">@lang('Auto (system default)')</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" data-icon="bi-brightness-high"
                                       data-value="default">
                                        <i class="bi-brightness-high me-2"></i>
                                        <span class="text-truncate"
                                              title="Default (light mode)">@lang('Default (light mode)')</span>
                                    </a>
                                    <a class="dropdown-item active" href="javascript:void(0)" data-icon="bi-moon"
                                       data-value="dark">
                                        <i class="bi-moon me-2"></i>
                                        <span class="text-truncate" title="Dark">@lang('Dark')</span>
                                    </a>
                                </div>
                            </div>

                            <!-- End Style Switcher -->
                        </li>

                        <li class="nav-item">
                            <!-- Account -->
                            <div class="dropdown gap-2">
                                <a class="navbar-dropdown-account-wrapper" href="javascript:;"
                                   id="accountNavbarDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                   data-bs-auto-close="outside" data-bs-dropdown-animation>
                                    <div class="avatar avatar-sm avatar-circle">
                                        <img class="avatar-img" src="{{auth()->user()->getImage()}}"
                                             alt="{{auth()->user()->name}}">
                                        <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                    </div>
                                </a>

                                <div class="dropdown-menu dropdown-menu-end navbar-dropdown-menu
                                            navbar-dropdown-menu-borderless navbar-dropdown-account"
                                     aria-labelledby="accountNavbarDropdown" style="width: 16rem;">
                                    <div class="dropdown-item-text">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm avatar-circle">
                                                <img class="avatar-img" src="{{auth()->user()->getImage()}}"
                                                     alt="{{auth()->user()->name}}">
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <h5 class="mb-0">{{auth()->user()->name}}</h5>
                                                <p class="card-text text-body">{{auth()->user()->email}}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="dropdown-divider"></div>

                                    <a class="dropdown-item" href="{{ route('agent.profile') }}">
                                        <i class="bi-person me-2"></i> @lang('Profile & Account')
                                    </a>
                                    @if($basic->qr_payment)
                                        <a class="dropdown-item" href="{{ route('agent.qr.code') }}">
                                            <i class="bi-qr-code-scan me-2"></i> @lang('QR Code')
                                        </a>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('agent.ticket.list') }}">
                                        <i class="bi-ticket me-2"></i> @lang('Ticket')
                                    </a>
                                    <div class="dropdown-divider"></div>

                                    <a class="dropdown-item d-flex align-items-center toggle-layout"
                                       href="{{ route('agent.toggle.layout') }}">
                                        <i class="bi-columns me-2 animate-icon"></i>
                                        <span class="ms-1">@lang('Change Layout')</span>
                                    </a>

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

                        <li class="nav-item">
                            <!-- Toggler -->
                            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#navbarMegaMenu"
                                    aria-controls="navbarMegaMenu" aria-expanded="false"
                                    aria-label="Toggle navigation">
                                <span class="navbar-toggler-default"><i class="bi-list"></i></span>
                                <span class="navbar-toggler-toggled"><i class="bi-x"></i></span>
                            </button>
                            <!-- End Toggler -->
                        </li>
                    </ul>
                    <!-- End Navbar -->
                </div>
                <!-- End Content End -->
            </div>
        </div>
    </div>

    <div class="container">
        <nav class="js-mega-menu flex-grow-1">
            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="navbarMegaMenu">
                <ul class="navbar-nav">

                    <li class="nav-item">
                        <a class="nav-link {{ menuActive('agent.dashboard') }}" href="{{route('agent.dashboard')}}">
                            <i class="bi-house-door dropdown-item-icon"></i> @lang('Dashboard')
                        </a>
                    </li>

                    <li class="hs-has-sub-menu nav-item">
                        <a id="pagesMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle
                            {{ menuActive( [ 'deposit.confirm' ]) }}"
                           href="javascript:void(0)" role="button">
                            <i class="bi-files-alt dropdown-item-icon"></i> @lang('Services')
                        </a>

                        <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                             aria-labelledby="pagesMegaMenu" style="min-width: 14rem;">
                            @if($basic->deposit)
                                <a class="dropdown-item {{ menuActive('deposit.confirm') }}"
                                   href="{{ route('fund.initialize') }}" data-placement="left">
                                    <span><i class="bi-wallet"></i>@lang('Add Fund')</span>
                                    <small class="d-none">@lang("Services > Add Fund")</small>
                                </a>
                            @endif
                            @if($basic->cash_in)
                                <a class="dropdown-item " href="{{ route('agent.cash_in.create') }}">
                                    <span><i class="bi-cash"></i>@lang('Cash In')</span>
                                    <small class="d-none">@lang("Service > Cash In")</small>
                                </a>
                            @endif
                        </div>
                    </li>


                    <!-- Apps -->
                    <li class="hs-has-sub-menu nav-item">
                        <a id="HistoryMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle"
                           href="javascript:void(0)" role="button">
                            <i class="bi-arrow-clockwise dropdown-item-icon"></i> @lang('History')
                        </a>

                        <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                             aria-labelledby="HistoryMenu" style="min-width: 14rem;">
                            <a class="dropdown-item " href="{{route('agent.transaction')}}">
                                <span><i class="bi-arrow-left-right"></i>@lang('Transaction')</span>
                                <small class="d-none">@lang("History > Transaction")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('agent.statement.index') }}">
                                <span><i class="bi-receipt"></i>@lang('Agent Statements')</span>
                                <small class="d-none">@lang("History > Agent Statements")</small>
                            </a>
                            @if($basic->deposit)
                                <a class="dropdown-item " href="{{ route('agent.fund.index') }}">
                                    <span><i class="bi-wallet"></i>@lang('Fund History')</span>
                                    <small class="d-none">@lang("History > Fund History")</small>
                                </a>
                            @endif
                        </div>
                    </li>

                    @if($basic->payout)
                        <li class="hs-has-sub-menu nav-item">
                            <a id="payoutMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle "
                               href="javascript:void(0)"
                               role="button"><i class="bi-wallet2 dropdown-item-icon"></i> @lang('Payout')</a>

                            <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                                 aria-labelledby="payoutMenu" style="min-width: 14rem;">
                                <a class="dropdown-item " href="{{ route('agent.payout') }}">
                                    <span><i class="bi-credit-card"></i>@lang('Request Payout')</span>
                                    <small class="d-none">@lang("Payout > Request Payout")</small>
                                </a>
                                <a class="dropdown-item " href="{{ route('agent.payout.index') }}">
                                    <span><i class="bi-list-check"></i>@lang('Payout List')</span>
                                    <small class="d-none">@lang("Payout > Payout List")</small>
                                </a>
                            </div>
                        </li>
                    @endif

                    <li class="hs-has-sub-menu nav-item">
                        <a id="appsMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle "
                           href="javascript:void(0)"
                           role="button"><i class="bi-gear dropdown-item-icon"></i> @lang('Settings')</a>

                        <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                             aria-labelledby="appsMegaMenu" style="min-width: 14rem;">
                            <a class="dropdown-item " href="{{ route('agent.twostep.security') }}">
                                <span><i class="bi-shield-check"></i>@lang('2FA Security')</span>
                                <small class="d-none">@lang("Settings > 2FA Security")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('agent.securityPin.reset') }}">
                                <span><i class="bi-lock"></i>@lang('Security PIN')</span>
                                <small class="d-none">@lang("Settings > Security PIN")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('agent.securityPin.manage') }}">
                                <span><i class="bi-key"></i>@lang('Manage PIN Uses')</span>
                                <small class="d-none">@lang("Settings > Manage PIN Uses")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('agent.verification.kyc') }}">
                                <span><i class="bi-person-check"></i>@lang('Verification Center')</span>
                                <small class="d-none">@lang("Settings > Verification Center")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('agent.notifications') }}">
                                <span><i class="bi-bell"></i>@lang('Notification Settings')</span>
                                <small class="d-none">@lang("Settings > Notification Settings")</small>
                            </a>
                        </div>
                    </li>

                </ul>

            </div>
            <!-- End Collapse -->
        </nav>
    </div>

</header>

@push('script')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const e = window.location.href,
                t = document.querySelectorAll('.hs-sub-menu .dropdown-item,.nav-item .nav-link');
            t.forEach(t => {
                if (t.href === e) {
                    t.classList.add("active");
                    const o = t.closest(".hs-has-sub-menu")?.querySelector(".hs-mega-menu-invoker");
                    o && o.classList.add("active")
                }
            })
        });
    </script>
@endpush
