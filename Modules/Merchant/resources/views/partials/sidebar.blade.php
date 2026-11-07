

<!-- Navbar Vertical -->
<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl
    navbar-vertical-aside-initialized navbar-bordered
    {{in_array(session()->get('themeMode'), [null, 'auto'] )?  'navbar-dark bg-dark ' : 'navbar-light bg-white'}}">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ route('merchant.dashboard') }}" aria-label="{{ $basicControl->site_title }}">
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

            <!-- Navbar Vertical Toggle -->
            <button type="button" class="js-navbar-vertical-aside-toggle-invoker navbar-aside-toggler">
                <i class="bi-arrow-bar-left navbar-toggler-short-align"
                   data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                   data-bs-toggle="tooltip"
                   data-bs-placement="right"
                   title="Collapse">
                </i>
                <i
                    class="bi-arrow-bar-right navbar-toggler-full-align"
                    data-bs-template='<div class="tooltip d-none d-md-block" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>'
                    data-bs-toggle="tooltip"
                    data-bs-placement="right"
                    title="Expand"
                ></i>
            </button>
            <!-- End Navbar Vertical Toggle -->

            <!-- Content -->
            <div class="navbar-vertical-content">
                <div id="navbarVerticalMenu" class="nav nav-pills nav-vertical card-navbar-nav">

                    <div class="nav-item">
                        <a class="nav-link {{ menuActive(['merchant.dashboard']) }}" href="{{ route('merchant.dashboard') }}">
                            <i class="bi-house-door nav-icon"></i>
                            <span class="nav-link-title">@lang("Dashboard")</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-3">@lang('History')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    <div class="nav-item">
                        <a class="nav-link {{menuActive(['merchant.transaction'])}}"
                           href="{{route('merchant.transaction')}}" data-placement="left">
                            <i class="bi-arrow-left-right nav-icon"></i>
                            <span class="nav-link-title">@lang('Transaction')</span>
                            <small class="d-none">@lang("History > Transaction")</small>
                        </a>
                    </div>

                    <span class="dropdown-header mt-3">@lang('Other Services')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    @if($basic->payout)
                        <div class="nav-item" {{ menuActive(['merchant.payout*'], 3) }}>
                            <a class="nav-link dropdown-toggle collapsed" href="#payoutMenu"
                               data-bs-target="#payoutMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="payoutMenu">
                                <i class="bi-wallet2 nav-icon"></i>
                                <span class="nav-link-title">@lang("Payout")</span>
                            </a>
                            <div id="payoutMenu" class="nav-collapse collapse {{ menuActive(['merchant.payout*'], 2) }}" data-bs-parent="#payoutMenu">
                                <a class="nav-link d-flex justify-content-between {{ menuActive(['merchant.payout']) }}"
                                   href="{{ route('merchant.payout') }}">
                                    <span>@lang("Request Payout")</span>
                                    <small class="d-none">@lang("Payout > Request Payout")</small>
                                </a>
                                <a class="nav-link d-flex justify-content-between {{ menuActive(['merchant.payout.index']) }}"
                                   href="{{ route('merchant.payout.index') }}">
                                    <span>@lang("Payout List")</span>
                                    <small class="d-none">@lang("Payout > Payout List")</small>
                                </a>
                            </div>
                        </div>
                    @endif

                    <span class="dropdown-header mt-3">@lang('Settings')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    <div class="nav-item">
                        <a class="nav-link {{menuActive(['merchant.settings.index'])}}"
                           href="{{route('merchant.settings.index')}}" data-placement="left">
                            <i class="bi-arrow-left-right nav-icon"></i>
                            <span class="nav-link-title">@lang('Merchant Settings')</span>
                            <small class="d-none">@lang("Settings > Merchant Settings")</small>
                        </a>
                    </div>

                    <div class="nav-item"
                        {{ menuActive(['merchant.twostep.security','merchant.securityPin.reset', 'merchant.securityPin.manage',
                                'merchant.verification.*','merchant.notifications'], 3) }}
                    >
                        <a class="nav-link dropdown-toggle collapsed" href="#settingsMenu"
                           data-bs-target="#settingsMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="settingsMenu">
                            <i class="bi-gear nav-icon"></i> <span class="nav-link-title">@lang("Settings")</span>
                        </a>
                        <div id="settingsMenu" data-bs-parent="#settingsMenu" class="nav-collapse collapse
                             {{ menuActive(['merchant.twostep.security','merchant.securityPin.reset','merchant.securityPin.manage',
                                'merchant.verification.*','merchant.notifications'], 2) }}"
                        >
                            <a class="nav-link {{ menuActive(['merchant.twostep.security']) }}" href="{{ route('merchant.twostep.security') }}">
                                <span>@lang("2FA Security")</span>
                                <small class="d-none">@lang("Settings > 2FA Security")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['merchant.securityPin.reset']) }}" href="{{ route('merchant.securityPin.reset') }}">
                                <span>@lang("Security PIN")</span>
                                <small class="d-none">@lang("Settings > Security PIN")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['merchant.securityPin.manage']) }}" href="{{ route('merchant.securityPin.manage') }}">
                                <span>@lang("Manage PIN Uses")</span>
                                <small class="d-none">@lang("Settings > Manage PIN Uses")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['merchant.verification.*']) }}" href="{{ route('merchant.verification.kyc') }}">
                                <span>@lang("Verification Center")</span>
                                <small class="d-none">@lang("Settings > Verification Center")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['merchant.notifications']) }}" href="{{ route('merchant.notifications') }}">
                                <span>@lang("Notification Settings")</span>
                                <small class="d-none">@lang("Settings > Notification Settings")</small>
                            </a>

                        </div>
                        
                        <div class="nav-item"
                        {{ menuActive(['merchant.api.key','merchant.api.docx'], 3) }}
                    >
                        <a class="nav-link dropdown-toggle collapsed" href="#docxMenu"
                           data-bs-target="#docxMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="docxMenu">
                            <i class="bi-patch-plus nav-icon"></i> <span class="nav-link-title">@lang("Business Api")</span>
                        </a>
                        <div id="docxMenu" data-bs-parent="#docxMenu" class="nav-collapse collapse
                             {{ menuActive(['merchant.api.key','merchant.api.docx'], 2) }}"
                        >
                            <a class="nav-link {{ menuActive(['merchant.api.key']) }}" href="{{ route('merchant.api.key') }}">
                                <span>@lang("Api Key")</span>
                                <small class="d-none">@lang("Business Api > Api Key")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['merchant.api.docx']) }}" href="{{ route('merchant.api.docx') }}">
                                <span>@lang("Api Documentation")</span>
                                <small class="d-none">@lang("Business Api > Api Documentation")</small>
                            </a>

                        </div>
                    </div>

                </div>

                <div class="navbar-vertical-footer">
                    <ul class="navbar-vertical-footer-list">
                        <li class="navbar-vertical-footer-list-item">
                            <span class="dropdown-header">@lang('Merchant Panel')</span>
                        </li>
                        <li class="navbar-vertical-footer-list-item">
                            <div class="dropdown dropup">
                                <button type="button" class="btn btn-ghost-secondary btn-icon rounded-circle"
                                        id="selectThemeDropdown" data-bs-toggle="dropdown" aria-expanded="false"
                                        data-bs-dropdown-animation></button>
                                <div class="dropdown-menu navbar-dropdown-menu navbar-dropdown-menu-borderless"
                                     aria-labelledby="selectThemeDropdown">
                                    <a class="dropdown-item" href="javascript:void(0)" data-icon="bi-moon-stars"
                                       data-value="auto">
                                        <i class="bi-moon-stars me-2"></i>
                                        <span class="text-truncate" title="Auto(system default)">@lang("Default")</span>
                                    </a>
                                    <a class="dropdown-item" href="javascript:void(0)" data-icon="bi-brightness-high"
                                       data-value="default">
                                        <i class="bi-brightness-high me-2"></i>
                                        <span class="text-truncate"
                                              title="Default (light mode)">@lang("Light Mode")</span>
                                    </a>
                                    <a class="dropdown-item active" href="javascript:void(0)" data-icon="bi-moon"
                                       data-value="dark">
                                        <i class="bi-moon me-2"></i>
                                        <span class="text-truncate" title="Dark">@lang("Dark Mode")</span>
                                    </a>
                                </div>
                            </div>
                        </li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</aside>




