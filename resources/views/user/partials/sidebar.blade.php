

<!-- Navbar Vertical -->
<aside class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl
    navbar-vertical-aside-initialized navbar-bordered
    {{in_array(session()->get('themeMode'), [null, 'auto'] )?  'navbar-dark bg-dark ' : 'navbar-light bg-white'}}">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
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
                        <a class="nav-link {{ menuActive(['user.dashboard']) }}" href="{{ route('user.dashboard') }}">
                            <i class="bi-house-door nav-icon"></i>
                            <span class="nav-link-title">@lang("Dashboard")</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-3">@lang('Services')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    @if($basic->deposit)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['fund.initialize'])}}"
                               href="{{route('fund.initialize')}}" data-placement="left">
                                <i class="bi-wallet nav-icon"></i>
                                <span class="nav-link-title">@lang('Add Fund')</span>
                                <small class="d-none">@lang("Services > Add Fund")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->transfer)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.transfer.initialize'])}}"
                               href="{{ route('user.transfer.initialize') }}" data-placement="left">
                                <i class="bi-send nav-icon"></i>
                                <span class="nav-link-title">@lang('Send Money')</span>
                                <small class="d-none">@lang("Services > Send Money")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->request)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.requestMoney.initialize'])}}"
                               href="{{ route('user.requestMoney.initialize') }}" data-placement="left">
                                <i class="bi-cash-coin nav-icon"></i>
                                <span class="nav-link-title">@lang('Request Money')</span>
                                <small class="d-none">@lang("Services > Request Money")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->exchange)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.exchange.initialize'])}}"
                               href="{{ route('user.exchange.initialize') }}" data-placement="left">
                                <i class="bi-currency-exchange nav-icon"></i>
                                <span class="nav-link-title">@lang('Exchange Money')</span>
                                <small class="d-none">@lang("Services > Exchange Money")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->escrow)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.escrow.createRequest'])}}"
                               href="{{ route('user.escrow.createRequest') }}" data-placement="left">
                                <i class="bi-recycle nav-icon"></i>
                                <span class="nav-link-title">@lang('Escrow')</span>
                                <small class="d-none">@lang("Services > Escrow")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->voucher)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.voucher.createRequest'])}}"
                               href="{{ route('user.voucher.createRequest') }}" data-placement="left">
                                <i class="bi-ticket-perforated nav-icon"></i>
                                <span class="nav-link-title">@lang('Voucher')</span>
                                <small class="d-none">@lang("Services > Voucher")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->invoice)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.invoice.create'])}}"
                               href="{{ route('user.invoice.create') }}" data-placement="left">
                                <i class="bi-receipt nav-icon"></i>
                                <span class="nav-link-title">@lang('Invoice')</span>
                                <small class="d-none">@lang("Services > Invoice")</small>
                            </a>
                        </div>
                    @endif

                    <div class="nav-item" {{ menuActive(['user.play.game*'], 3) }}>
                        <a class="nav-link dropdown-toggle collapsed {{ menuActive(['user.play.game*'], 3) }}" href="#playGameMenu"
                           data-bs-target="#playGameMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="playGameMenu">
                            <i class="bi-graph-up-arrow nav-icon"></i>
                            <span class="nav-link-title">@lang("FX Trade")</span>
                        </a>
                        <div id="playGameMenu" class="nav-collapse collapse {{ menuActive(['user.play.game*'], 2) }}" data-bs-parent="#playGameMenu">
                            <a class="nav-link d-flex justify-content-between {{ menuActive(['user.play.game']) }}"
                               href="{{ route('user.play.game') }}">
                                <span>@lang("All Games")</span>
                                <small class="d-none">@lang("FX Trade > All Games")</small>
                            </a>
                            @php $fxSet = fxTradeSettings(); @endphp
                            @if((int)$fxSet->fx_enabled === 1)
                            <a class="nav-link d-flex justify-content-between {{ request()->is('user/play-game/fx-casino') ? 'active' : '' }}"
                               href="{{ route('user.play.game.show', 'fx-casino') }}">
                                <span>@lang("Trade Binary")</span>
                                <small class="d-none">@lang("FX Trade > Trade Binary")</small>
                            </a>
                            @endif
                            @if((int)$fxSet->royal_enabled === 1)
                            <a class="nav-link d-flex justify-content-between {{ request()->is('user/play-game/royal-step-casino') ? 'active' : '' }}"
                               href="{{ route('user.play.game.show', 'royal-step-casino') }}">
                                <span>@lang("Royal Steps")</span>
                                <small class="d-none">@lang("FX Trade > Royal Steps")</small>
                            </a>
                            @endif
                            @if((int)($fxSet->stocks_enabled ?? 1) === 1)
                            <a class="nav-link d-flex justify-content-between {{ request()->is('user/play-game/all-stocks') ? 'active' : '' }}"
                               href="{{ route('user.play.game.show', 'all-stocks') }}">
                                <span>@lang("All Stocks")</span>
                                <small class="d-none">@lang("FX Trade > All Stocks")</small>
                            </a>
                            @endif
                            @if((int)$fxSet->bot_enabled === 1)
                            <a class="nav-link d-flex justify-content-between {{ request()->is('user/play-game/bot-game') ? 'active' : '' }}"
                               href="{{ route('user.play.game.show', 'bot-game') }}">
                                <span>@lang("Bot Game")</span>
                                <small class="d-none">@lang("FX Trade > Bot Game")</small>
                            </a>
                            @endif
                        </div>
                    </div>

                    <div class="nav-item" {{ menuActive(['user.redeem.*'], 3) }}>
                        <a class="nav-link dropdown-toggle collapsed" href="#redeemMenu"
                           data-bs-target="#redeemMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="redeemMenu">
                            <i class="bi-r-circle nav-icon"></i>
                            <span class="nav-link-title">@lang("Redeem Code")</span>
                        </a>
                        <div id="redeemMenu" class="nav-collapse collapse {{ menuActive(['user.redeem.*'], 2) }}" data-bs-parent="#redeemMenu">
                            <a class="nav-link d-flex justify-content-between {{ menuActive(['user.redeem.initialize']) }}"
                               href="{{ route('user.redeem.initialize') }}">
                                <span>@lang("Generate New Code")</span>
                                <small class="d-none">@lang("Redeem Code > Generate New Code")</small>
                            </a>
                            <a class="nav-link d-flex justify-content-between {{ menuActive(['user.redeem.insert']) }}"
                               href="{{ route('user.redeem.insert') }}">
                                <span>@lang("Insert Code")</span>
                                <small class="d-none">@lang("Redeem Code > Insert Code")</small>
                            </a>
                        </div>
                    </div>
                    @stack('extra_menu')

                    <span class="dropdown-header mt-3">@lang('History')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    <div class="nav-item">
                        <a class="nav-link {{menuActive(['user.transaction'])}}"
                           href="{{route('user.transaction')}}" data-placement="left">
                            <i class="bi-arrow-left-right nav-icon"></i>
                            <span class="nav-link-title">@lang('Transaction')</span>
                            <small class="d-none">@lang("History > Transaction")</small>
                        </a>
                    </div>

                    @if($basic->deposit)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.fund.index'])}}"
                               href="{{route('user.fund.index')}}" data-placement="left">
                                <i class="bi-wallet nav-icon"></i>
                                <span class="nav-link-title">@lang('Funding History')</span>
                                <small class="d-none">@lang("History > Funding History")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->transfer)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.transfer.index'])}}"
                               href="{{route('user.transfer.index')}}" data-placement="left">
                                <i class="bi-send nav-icon"></i>
                                <span class="nav-link-title">@lang('Transfer History')</span>
                                <small class="d-none">@lang("History > Transfer History")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->request)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.requestMoney.index'])}}"
                               href="{{route('user.requestMoney.index')}}" data-placement="left">
                                <i class="bi-cash-coin nav-icon"></i>
                                <span class="nav-link-title">@lang('Request History')</span>
                                <small class="d-none">@lang("History > Request History")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->exchange)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.exchange.index'])}}"
                               href="{{route('user.exchange.index')}}" data-placement="left">
                                <i class="bi-currency-exchange nav-icon"></i>
                                <span class="nav-link-title">@lang('Exchange History')</span>
                                <small class="d-none">@lang("History > Exchange History")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->redeem)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.redeem.index'])}}"
                               href="{{route('user.redeem.index')}}" data-placement="left">
                                <i class="bi-r-circle nav-icon"></i>
                                <span class="nav-link-title">@lang('Redeem History')</span>
                                <small class="d-none">@lang("History > Redeem History")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->escrow)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.escrow.index'])}}"
                               href="{{route('user.escrow.index')}}" data-placement="left">
                                <i class="bi-recycle nav-icon"></i>
                                <span class="nav-link-title">@lang('Escrow History')</span>
                                <small class="d-none">@lang("History > Escrow History")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->voucher)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.voucher.index'])}}"
                               href="{{route('user.voucher.index')}}" data-placement="left">
                                <i class="bi-recycle nav-icon"></i>
                                <span class="nav-link-title">@lang('Voucher History')</span>
                                <small class="d-none">@lang("History > Voucher History")</small>
                            </a>
                        </div>
                    @endif
                    @if($basic->invoice)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.invoice.index'])}}"
                               href="{{route('user.invoice.index')}}" data-placement="left">
                                <i class="bi-receipt nav-icon"></i>
                                <span class="nav-link-title">@lang('Invoice History')</span>
                                <small class="d-none">@lang("History > Invoice History")</small>
                            </a>
                        </div>
                    @endif
                    <div class="nav-item">
                        <a class="nav-link {{menuActive(['user.commission.index'])}}"
                           href="{{route('user.commission.index')}}" data-placement="left">
                            <i class="bi-cash-stack nav-icon"></i>
                            <span class="nav-link-title">@lang('Commission History')</span>
                            <small class="d-none">@lang("History > Commission History")</small>
                        </a>
                    </div>

                    <span class="dropdown-header mt-3">@lang('Other Services')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>
                    <div class="nav-item">
                        <a class="nav-link {{menuActive(['user.dispute.index'])}}"
                           href="{{route('user.dispute.index')}}" data-placement="left">
                            <i class="bi-grid-1x2 nav-icon"></i>
                            <span class="nav-link-title">@lang('Dispute History')</span>
                        </a>
                    </div>
                    @if($basic->qr_payment)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.qr.payment'])}}"
                               href="{{route('user.qr.payment')}}" data-placement="left">
                                <i class="bi-qr-code-scan nav-icon"></i>
                                <span class="nav-link-title">@lang('QR Payment')</span>
                            </a>
                        </div>
                    @endif
                    @if($basic->virtual_card)
                        <div class="nav-item">
                            <a class="nav-link {{menuActive(['user.virtual.card'])}}"
                               href="{{route('user.virtual.card')}}" data-placement="left">
                                <i class="bi-credit-card nav-icon"></i>
                                <span class="nav-link-title">@lang('Virtual Card')</span>
                            </a>
                        </div>
                    @endif
                    @if($basic->bill_payment)
                        <div class="nav-item" {{ menuActive(['user.pay.bill*'], 3) }}>
                            <a class="nav-link dropdown-toggle collapsed" href="#billMenu"
                               data-bs-target="#billMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="billMenu">
                                <i class="bi-card-checklist nav-icon"></i>
                                <span class="nav-link-title">@lang("Bill Service")</span>
                            </a>
                            <div id="billMenu" class="nav-collapse collapse {{ menuActive(['user.pay.bill*'], 2) }}" data-bs-parent="#billMenu">
                                <a class="nav-link d-flex justify-content-between {{ menuActive(['user.pay.bill']) }}"
                                   href="{{ route('user.pay.bill') }}">
                                    <span>@lang("Pay Bill")</span>
                                    <small class="d-none">@lang("Bill Service > Pay Bill")</small>
                                </a>
                                <a class="nav-link d-flex justify-content-between {{ menuActive(['user.pay.bill.list']) }}"
                                   href="{{ route('user.pay.bill.list') }}">
                                    <span>@lang("Bill List")</span>
                                    <small class="d-none">@lang("Bill Service > Bill List")</small>
                                </a>
                            </div>
                        </div>
                    @endif
                    @if($basic->payout)
                        <div class="nav-item" {{ menuActive(['user.payout*'], 3) }}>
                            <a class="nav-link dropdown-toggle collapsed" href="#payoutMenu"
                               data-bs-target="#payoutMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="payoutMenu">
                                <i class="bi-wallet2 nav-icon"></i>
                                <span class="nav-link-title">@lang("Payout")</span>
                            </a>
                            <div id="payoutMenu" class="nav-collapse collapse {{ menuActive(['user.payout*'], 2) }}" data-bs-parent="#payoutMenu">
                                <a class="nav-link d-flex justify-content-between {{ menuActive(['user.payout']) }}"
                                   href="{{ route('user.payout') }}">
                                    <span>@lang("Request Payout")</span>
                                    <small class="d-none">@lang("Payout > Request Payout")</small>
                                </a>
                                <a class="nav-link d-flex justify-content-between {{ menuActive(['user.payout.index']) }}"
                                   href="{{ route('user.payout.index') }}">
                                    <span>@lang("Payout List")</span>
                                    <small class="d-none">@lang("Payout > Payout List")</small>
                                </a>
                            </div>
                        </div>
                    @endif

                    <span class="dropdown-header mt-3">@lang('Store')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    @if($basic->store)
                        <div class="nav-item" {{ menuActive(['user.category.list','user.store.list','user.attr.list',
                                'user.product.list','user.stock.list','user.shipping.list','user.order.list'], 3) }}
                        >
                            <a class="nav-link dropdown-toggle collapsed" href="#storeMenu"
                               data-bs-target="#storeMenu" data-bs-toggle="collapse" role="button"
                               aria-expanded="false" aria-controls="storeMenu">
                                <i class="bi-shop nav-icon"></i> <span class="nav-link-title">@lang("Store")</span>
                            </a>

                            <div id="storeMenu" data-bs-parent="#storeMenu" class="nav-collapse collapse
                                {{ menuActive(['user.category.list','user.store.list','user.attr.list',
                                        'user.product.list','user.stock.list','user.shipping.list','user.order.list'], 2) }}"
                            >
                                <a class="nav-link {{ menuActive(['user.category.list']) }}" href="{{ route('user.category.list') }}">
                                    <span>@lang("Category")</span>
                                    <small class="d-none">@lang("Store > Category")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['user.store.list']) }}" href="{{ route('user.store.list') }}">
                                    <span>@lang("Create Store")</span>
                                    <small class="d-none">@lang("Store > Create Store")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['user.attr.list']) }}" href="{{ route('user.attr.list') }}">
                                    <span>@lang("Product Attribute")</span>
                                    <small class="d-none">@lang("Store > Product Attribute")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['user.product.list']) }}" href="{{ route('user.product.list') }}">
                                    <span>@lang("Marketplace")</span>
                                    <small class="d-none">@lang("Store > Marketplace")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['user.stock.list']) }}" href="{{ route('user.stock.list') }}">
                                    <span>@lang("Product Stock")</span>
                                    <small class="d-none">@lang("Store > Product Stock")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['user.shipping.list']) }}" href="{{ route('user.shipping.list') }}">
                                    <span>@lang("Shipping Charge")</span>
                                    <small class="d-none">@lang("Store > Shipping Charge")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['user.order.list']) }}" href="{{ route('user.order.list') }}">
                                    <span>@lang("Orders")</span>
                                    <small class="d-none">@lang("Store > Orders")</small>
                                </a>
                            </div>
                        </div>
                    @endif

                    <span class="dropdown-header mt-3">@lang('Settings')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    <div class="nav-item" {{ menuActive(['user.twostep.security','user.securityPin.reset','user.securityPin.manage',
                                'user.verification.kyc','user.notifications','user.currency.setting'], 3) }}
                    >
                        <a class="nav-link dropdown-toggle collapsed" href="#settingsMenu"
                           data-bs-target="#settingsMenu" data-bs-toggle="collapse" role="button" aria-expanded="false" aria-controls="settingsMenu">
                            <i class="bi-gear nav-icon"></i> <span class="nav-link-title">@lang("Settings")</span>
                        </a>
                        <div id="settingsMenu" data-bs-parent="#settingsMenu" class="nav-collapse collapse
                             {{ menuActive(['user.twostep.security','user.securityPin.reset','user.securityPin.manage',
                                'user.verification.kyc','user.notifications','user.currency.setting'], 2) }}"
                        >
                            <a class="nav-link {{ menuActive(['user.twostep.security']) }}" href="{{ route('user.twostep.security') }}">
                                <span>@lang("2FA Security")</span>
                                <small class="d-none">@lang("Settings > 2FA Security")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['user.securityPin.reset']) }}" href="{{ route('user.securityPin.reset') }}">
                                <span>@lang("Security PIN")</span>
                                <small class="d-none">@lang("Settings > Security PIN")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['user.securityPin.manage']) }}" href="{{ route('user.securityPin.manage') }}">
                                <span>@lang("Manage PIN Uses")</span>
                                <small class="d-none">@lang("Settings > Manage PIN Uses")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['user.verification.kyc']) }}" href="{{ route('user.verification.kyc') }}">
                                <span>@lang("Verification Center")</span>
                                <small class="d-none">@lang("Settings > Verification Center")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['user.notifications']) }}" href="{{ route('user.notifications') }}">
                                <span>@lang("Notification Settings")</span>
                                <small class="d-none">@lang("Settings > Notification Settings")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['user.currency.setting']) }}" href="{{ route('user.currency.setting') }}">
                                <span>@lang("Currency Set")</span>
                                <small class="d-none">@lang("Settings > Currency Set")</small>
                            </a>
                        </div>
                    </div>



                </div>

                <div class="navbar-vertical-footer">
                    <ul class="navbar-vertical-footer-list">
                        <li class="navbar-vertical-footer-list-item">
                            <span class="dropdown-header">@lang('User Panel')</span>
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
                                        <span class="text-truncate"
                                              title="Auto (system default)">@lang("Default")</span>
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



