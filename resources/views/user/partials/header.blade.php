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
                                        <span v-if="items.length > 0" class="btn-status btn-sm-status btn-status-danger"></span>
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

                                    <a class="dropdown-item" href="{{ route('user.profile') }}">
                                        <i class="bi-person me-2"></i> @lang('Profile & Account')
                                    </a>
                                    @if($basic->qr_payment)
                                        <a class="dropdown-item" href="{{ route('user.qr.code') }}">
                                            <i class="bi-qr-code-scan me-2"></i> @lang('QR Code')
                                        </a>
                                    @endif
                                    <a class="dropdown-item" href="{{ route('user.ticket.list') }}">
                                        <i class="bi-ticket me-2"></i> @lang('Ticket')
                                    </a>
                                    <div class="dropdown-divider"></div>

                                    <a class="dropdown-item d-flex align-items-center toggle-layout"
                                       href="{{ route('user.toggle.layout') }}">
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
                    <!-- Dashboards -->
                    <li class="nav-item">
                        <a class="nav-link {{ menuActive('user.dashboard') }}" href="{{route('user.dashboard')}}">
                            <i class="bi-house-door dropdown-item-icon"></i> @lang('Dashboard')
                        </a>
                    </li>
                    <!-- End Dashboards -->

                    <!-- Pages -->
                    <li class="hs-has-sub-menu nav-item">
                        <a id="pagesMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle
                            {{ menuActive(
                                [
                                    'deposit.confirm','user.transfer.confirm','user.requestMoney.confirm',
                                    'user.exchange.confirm','user.escrow.confirmInit','user.voucher.confirmInit',
                                    'user.redeem.initialize','user.redeem.insert','user.redeem.confirm'
                                ])
                            }}"
                           href="javascript:void(0)" role="button">
                            <i class="bi-files-alt dropdown-item-icon"></i> @lang('Services')
                        </a>

                        <!-- Mega Menu -->
                        <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                             aria-labelledby="pagesMegaMenu" style="min-width: 14rem;">
                            @if($basic->deposit)
                                <a class="dropdown-item {{ menuActive('deposit.confirm') }}"
                                   href="{{ route('fund.wallet.selection') }}" data-placement="left">
                                    <span><i class="bi-wallet"></i>@lang('Add Fund')</span>
                                    <small class="d-none">@lang("Services > Add Fund")</small>
                                </a>
                            @endif
                            @if($basic->transfer)
                                <a class="dropdown-item {{ menuActive('user.transfer.confirm') }}" href="{{ route('user.transfer.initialize') }}"
                                   data-placement="left">
                                    <span><i class="bi-send"></i>@lang('Send Money')</span>
                                    <small class="d-none">@lang("Services > Send Money")</small>
                                </a>
                            @endif
                            @if($basic->request)
                                <a class="dropdown-item {{ menuActive('user.requestMoney.confirm') }}" href="{{ route('user.requestMoney.initialize') }}"
                                   data-placement="left">
                                    <span><i class="bi-cash-coin"></i>@lang('Request Money')</span>
                                    <small class="d-none">@lang("Services > Request Money")</small>
                                </a>
                            @endif
                            @if($basic->exchange)
                                <a class="dropdown-item {{ menuActive('user.exchange.confirm') }}" href="{{ route('user.exchange.initialize') }}"
                                   data-placement="left">
                                    <span><i class="bi-currency-exchange"></i>@lang('Exchange Money')</span>
                                    <small class="d-none">@lang("Services > Exchange Money")</small>
                                </a>
                            @endif
                            @if($basic->redeem)
                                <div class="hs-has-sub-menu nav-item">
                                    <a id="RedeemMenu" class="hs-mega-menu-invoker dropdown-item dropdown-toggle
                                        {{ menuActive(['user.redeem.initialize','user.redeem.insert','user.redeem.confirm']) }}"
                                        href="javascript:void(0)" role="button">
                                        <i class="bi-r-circle"></i>@lang('Redeem')
                                    </a>
                                    <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                                         aria-labelledby="RedeemMenu" style="min-width: 14rem;">
                                        <a class="dropdown-item " href="{{ route('user.redeem.initialize') }}">
                                            <span><i class="bi-cpu"></i>@lang('Generate New Code')</span>
                                            <small class="d-none">@lang("Services > Redeem > Generate New Code")</small>
                                        </a>
                                        <a class="dropdown-item " href="{{ route('user.redeem.insert') }}">
                                            <span><i class="bi-r-circle-fill"></i>@lang('Insert Code')</span>
                                            <small class="d-none">@lang("Services > Redeem > Insert Code")</small>
                                        </a>
                                    </div>
                                </div>
                            @endif
                            @if($basic->escrow)
                                <a class="dropdown-item " href="{{ route('user.escrow.createRequest') }}" data-placement="left">
                                    <span><i class="bi-recycle"></i>@lang('Escrow')</span>
                                    <small class="d-none">@lang("Services > Escrow")</small>
                                </a>
                            @endif
                            @if($basic->voucher)
                                <a class="dropdown-item " href="{{route('user.voucher.createRequest')}}"
                                   data-placement="left">
                                    <span><i class="bi-ticket-perforated"></i>@lang('Voucher')</span>
                                    <small class="d-none">@lang("Services > Voucher")</small>
                                </a>
                            @endif
                            @if($basic->invoice)
                                <a class="dropdown-item " href="{{ route('user.invoice.create') }}"
                                   data-placement="left">
                                    <span><i class="bi-receipt"></i>@lang('Invoice')</span>
                                    <small class="d-none">@lang("Services > Invoice")</small>
                                </a>
                            @endif
                        </div>
                        <!-- End Mega Menu -->
                    </li>
                    <!-- End Pages -->

                    <li class="hs-has-sub-menu nav-item">
                        <a id="playGameMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle {{ menuActive(['user.play.game*']) }}"
                           href="javascript:void(0)" role="button">
                            <i class="bi-graph-up-arrow dropdown-item-icon"></i> @lang('FX Trade')
                        </a>
                        <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                             aria-labelledby="playGameMegaMenu" style="min-width: 14rem;">
                            @php $fxSet = fxTradeSettings(); @endphp
                            <a class="dropdown-item {{ menuActive('user.play.game') }}" href="{{ route('user.play.game') }}">
                                <span><i class="bi-grid"></i>@lang('All Stocks')</span>
                                <small class="d-none">@lang("FX Trade > All Stocks")</small>
                            </a>
                            @if((int)$fxSet->fx_enabled === 1)
                            <a class="dropdown-item" href="{{ route('user.play.game.show', 'fx-casino') }}">
                                <span><i class="bi-graph-up-arrow"></i>@lang('Trade Binary')</span>
                                <small class="d-none">@lang("FX Trade > Trade Binary")</small>
                            </a>
                            @endif
                            @if((int)$fxSet->royal_enabled === 1)
                            <a class="dropdown-item" href="{{ route('user.play.game.show', 'royal-step-casino') }}">
                                <span><i class="bi-trophy-fill"></i>@lang('Royal Steps')</span>
                                <small class="d-none">@lang("FX Trade > Royal Steps")</small>
                            </a>
                            @endif
                            @if((int)($fxSet->stocks_enabled ?? 1) === 1)
                            <a class="dropdown-item" href="{{ route('user.play.game.show', 'all-stocks') }}">
                                <span><i class="bi-bar-chart-line"></i>@lang('Forex Trade')</span>
                                <small class="d-none">@lang("FX Trade > Forex Trade")</small>
                            </a>
                            @endif
                            @if((int)$fxSet->bot_enabled === 1)
                            <a class="dropdown-item" href="{{ route('user.play.game.show', 'bot-game') }}">
                                <span><i class="bi-robot"></i>@lang('Bot Game')</span>
                                <small class="d-none">@lang("FX Trade > Bot Game")</small>
                            </a>
                            @endif
                        </div>
                    </li>


                    <!-- Apps -->
                    <li class="hs-has-sub-menu nav-item">
                        <a id="appsMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle
                            {{ menuActive(['user.invoice.view', ]) }} "
                           href="javascript:void(0)" role="button">
                            <i class="bi-arrow-clockwise dropdown-item-icon"></i> @lang('History')
                        </a>

                        <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                             aria-labelledby="appsMegaMenu" style="min-width: 14rem;">
                            <a class="dropdown-item " href="{{route('user.transaction')}}">
                                <span><i class="bi-arrow-left-right"></i>@lang('Transaction')</span>
                                <small class="d-none">@lang("History > Transaction")</small>
                            </a>
                            @if($basic->deposit)
                                <a class="dropdown-item " href="{{ route('user.fund.index') }}">
                                    <span><i class="bi-wallet"></i>@lang('Fund History')</span>
                                    <small class="d-none">@lang("History > Fund History")</small>
                                </a>
                            @endif
                            @if($basic->transfer)
                                <a class="dropdown-item " href="{{route('user.transfer.index')}}">
                                    <span><i class="bi-send"></i>@lang('Transfer History')</span>
                                    <small class="d-none">@lang("History > Transfer History")</small>
                                </a>
                            @endif
                            @if($basic->request)
                                <a class="dropdown-item " href="{{ route('user.requestMoney.index') }}">
                                    <span><i class="bi-cash-coin"></i>@lang('Request History')</span>
                                    <small class="d-none">@lang("History > Request History")</small>
                                </a>
                            @endif
                            @if($basic->exchange)
                                <a class="dropdown-item" href="{{ route('user.exchange.index') }}">
                                    <span><i class="bi-currency-exchange"></i>@lang('Exchange History')</span>
                                    <small class="d-none">@lang("History > Exchange History")</small>
                                </a>
                            @endif
                            @if($basic->redeem)
                                <a class="dropdown-item" href="{{ route('user.redeem.index') }}">
                                    <span><i class="bi-r-circle"></i>@lang('Redeem History')</span>
                                    <small class="d-none">@lang("History > Redeem History")</small>
                                </a>
                            @endif
                            @if($basic->escrow)
                                <a class="dropdown-item" href="{{ route('user.escrow.index') }}">
                                    <span><i class="bi-recycle"></i>@lang('Escrow History')</span>
                                    <small class="d-none">@lang("History > Escrow History")</small>
                                </a>
                            @endif
                            @if($basic->voucher)
                                <a class="dropdown-item" href="{{ route('user.voucher.index') }}">
                                    <span><i class="bi-ticket-perforated"></i>@lang('Voucher History')</span>
                                    <small class="d-none">@lang("History > Voucher History")</small>
                                </a>
                            @endif
                            @if($basic->invoice)
                                <a class="dropdown-item {{ menuActive('user.invoice.view') }}" href="{{ route('user.invoice.index') }}">
                                    <span><i class="bi-receipt"></i>@lang('Invoice History')</span>
                                    <small class="d-none">@lang("History > Invoice History")</small>
                                </a>
                            @endif

                            <a class="dropdown-item " href="{{ route('user.commission.index') }}">
                                <span><i class="bi-cash-stack"></i>@lang('Commission History')</span>
                                <small class="d-none">@lang("History > Commission History")</small>
                            </a>
                        </div>
                    </li>
                    <!-- End Apps -->

                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('user.dispute.index') }}">
                            <i class="bi-grid-1x2 dropdown-item-icon"></i> @lang('Dispute')
                        </a>
                    </li>
                    @if($basic->qr_payment)
                        <li class="nav-item">
                            <a class="nav-link " href="{{ route('user.qr.payment') }}">
                                <i class="bi-qr-code-scan dropdown-item-icon"></i> @lang('QR Payment')
                            </a>
                        </li>
                    @endif
                    @if($basic->virtual_card)
                        <li class="nav-item">
                            <a class="nav-link {{ menuActive('user.virtual.cardTransaction') }}
                                {{ request()->is('add-fund/card/*') ? 'active' : '' }}"
                               href="{{ route('user.virtual.card') }}">
                                <i class="bi-credit-card dropdown-item-icon"></i> @lang('Virtual Card')
                            </a>
                        </li>
                    @endif

                    @if($basic->store)
                        <li class="hs-has-sub-menu nav-item">
                            <a id="appsMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle
                                {{ menuActive( [
                                    'user.store.create','user.store.edit','user.attr.create','user.attr.edit',
                                    'user.product.create','user.product.edit','user.product.view',
                                    'user.stock.create','user.stock.view','user.order.view'
                                    ]) }}"
                               href="javascript:void(0)" role="button">
                                <i class="bi-shop dropdown-item-icon"></i> @lang('Store')
                            </a>

                            <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                                 aria-labelledby="appsMegaMenu" style="min-width: 14rem;">
                                <a class="dropdown-item " href="{{route('user.category.list')}}">
                                    <span><i class="bi-vr"></i>@lang('Category')</span>
                                    <small class="d-none">@lang("Store > Category")</small>
                                </a>
                                <a class="dropdown-item {{ menuActive(['user.store.create','user.store.edit']) }}"
                                   href="{{route('user.store.list')}}">
                                    <span><i class="bi-shop"></i>@lang('Create Store')</span>
                                    <small class="d-none">@lang("Store > Create Store")</small>
                                </a>
                                <a class="dropdown-item {{ menuActive(['user.attr.create','user.attr.edit']) }}"
                                   href="{{route('user.attr.list')}}">
                                    <span><i class="bi-boxes"></i>@lang('Product Attribute')</span>
                                    <small class="d-none">@lang("Store > Product Attribute")</small>
                                </a>
                                <a class="dropdown-item
                                {{ menuActive(['user.product.create','user.product.edit','user.product.view']) }}"
                                   href="{{route('user.product.list')}}">
                                    <span><i class="bi-list-columns"></i>@lang('Marketplace')</span>
                                    <small class="d-none">@lang("Store > Marketplace")</small>
                                </a>
                                <a class="dropdown-item
                                    {{ menuActive(['user.stock.create','user.stock.view']) }}"
                                    href="{{route('user.stock.list')}}">
                                    <span><i class="bi-bag"></i>@lang('Product Stock')</span>
                                    <small class="d-none">@lang("Store > Product Stock")</small>
                                </a>
                                <a class="dropdown-item " href="{{route('user.shipping.list')}}">
                                    <span><i class="bi-truck"></i>@lang('Shipping Charge')</span>
                                    <small class="d-none">@lang("Store > Shipping Charge")</small>
                                </a>
                                <a class="dropdown-item {{ menuActive('user.order.view') }}" href="{{route('user.order.list')}}">
                                    <span><i class="bi-bag-check"></i>@lang('Orders')</span>
                                    <small class="d-none">@lang("Store > Orders")</small>
                                </a>
                            </div>
                        </li>
                    @endif

                    @if($basic->bill_payment)
                        <li class="hs-has-sub-menu nav-item">
                            <a id="appsMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle
                                {{ menuActive( [ 'user.pay.bill.confirm' ]) }}"
                               href="javascript:void(0)" role="button">
                                <i class="bi-receipt dropdown-item-icon"></i> @lang('Bill')
                            </a>

                            <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                                 aria-labelledby="appsMegaMenu" style="min-width: 14rem;">
                                <a class="dropdown-item {{ menuActive('user.pay.bill.confirm') }}" href="{{route('user.pay.bill')}}">
                                    <span><i class="bi-receipt-cutoff"></i>@lang('Pay Bill')</span>
                                    <small class="d-none">@lang("Bill > Pay Bill")</small>
                                </a>
                                <a class="dropdown-item " href="{{route('user.pay.bill.list')}}">
                                    <span><i class="bi-card-checklist"></i>@lang('Bill List')</span>
                                    <small class="d-none">@lang("Bill > Bill List")</small>
                                </a>
                            </div>
                        </li>
                    @endif

                    @if($basic->payout)
                        <li class="hs-has-sub-menu nav-item">
                            <a id="appsMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle "
                               href="javascript:void(0)"
                               role="button"><i class="bi-wallet2 dropdown-item-icon"></i> @lang('Payout')</a>

                            <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
                                 aria-labelledby="appsMegaMenu" style="min-width: 14rem;">
                                <a class="dropdown-item " href="{{ route('user.payout') }}">
                                    <span><i class="bi-credit-card"></i>@lang('Request Payout')</span>
                                    <small class="d-none">@lang("Payout > Request Payout")</small>
                                </a>
                                <a class="dropdown-item " href="{{ route('user.payout.index') }}">
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
                            <a class="dropdown-item " href="{{ route('user.twostep.security') }}">
                                <span><i class="bi-shield-check"></i>@lang('2FA Security')</span>
                                <small class="d-none">@lang("Settings > 2FA Security")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('user.securityPin.reset') }}">
                                <span><i class="bi-lock"></i>@lang('Security PIN')</span>
                                <small class="d-none">@lang("Settings > Security PIN")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('user.securityPin.manage') }}">
                                <span><i class="bi-key"></i>@lang('Manage PIN Uses')</span>
                                <small class="d-none">@lang("Settings > Manage PIN Uses")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('user.verification.kyc') }}">
                                <span><i class="bi-person-check"></i>@lang('Verification Center')</span>
                                <small class="d-none">@lang("Settings > Verification Center")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('user.notifications') }}">
                                <span><i class="bi-bell"></i>@lang('Notification Settings')</span>
                                <small class="d-none">@lang("Settings > Notification Settings")</small>
                            </a>
                            <a class="dropdown-item " href="{{route('user.currency.setting')}}">
                                <span><i class="bi-currency-dollar"></i>@lang('Currency Set')</span>
                                <small class="d-none">@lang("Settings > Currency Set")</small>
                            </a>
                            {{--<a class="dropdown-item " href="{{ route('user.api.key') }}">
                                <span><i class="bi-shield-exclamation"></i>@lang('Business Api Key')</span>
                                <small class="d-none">@lang("Settings > API Key")</small>
                            </a>
                            <a class="dropdown-item " href="{{ route('user.api.docx') }}">
                                <span><i class="bi-file-text"></i>@lang('Api Documentation')</span>
                                <small class="d-none">@lang("Settings > Api Documentation")</small>
                            </a>--}}
                        </div>
                    </li>
                    @stack('extra_menu')

                </ul>

            </div>
            <!-- End Collapse -->
        </nav>
    </div>

</header>
<!-- ========== END HEADER ========== -->

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
