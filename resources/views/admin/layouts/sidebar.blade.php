<!-- Navbar Vertical -->
<aside
    class="js-navbar-vertical-aside navbar navbar-vertical-aside navbar-vertical navbar-vertical-fixed navbar-expand-xl navbar-vertical-aside-initialized
    {{in_array(session()->get('themeMode'), [null, 'auto'] )?  'navbar-dark bg-dark ' : 'navbar-light bg-white'}}">
    <div class="navbar-vertical-container">
        <div class="navbar-vertical-footer-offset">
            <!-- Logo -->
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}" aria-label="{{ $basicControl->site_title }}">
                <img class="navbar-brand-logo navbar-brand-logo-auto"
                     src="{{ getFile(session()->get('themeMode') == 'auto'?$basicControl->admin_dark_mode_logo_driver : $basicControl->admin_logo_driver, session()->get('themeMode') == 'auto'?$basicControl->admin_dark_mode_logo:$basicControl->admin_logo, true) }}"
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
                        <a class="nav-link {{ menuActive(['admin.dashboard','admin.wallet.summary']) }}"
                           href="{{ route('admin.dashboard') }}">
                            <i class="bi-house-door nav-icon"></i>
                            <span class="nav-link-title">@lang("Dashboard")</span>
                        </a>
                    </div>


                    <span class="dropdown-header mt-2">@lang('Transactions')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>
                    @if($basic->transfer)
                        @if(checkPermission(2))
                            <div class="nav-item">
                                <a class="nav-link {{ menuActive(['admin.transfer.index']) }}"
                                   href="{{ route('admin.transfer.index') }}" data-placement="left">
                                    <i class="fa-light fa-users nav-icon"></i>
                                    <span class="nav-link-title">@lang("Transfer List")</span>
                                </a>
                            </div>
                        @endif
                    @endif

                    @if($basic->request)
                        @if(checkPermission(15))
                            <div class="nav-item">
                                <a class="nav-link {{ menuActive(['admin.requestMoney.index']) }}"
                                   href="{{ route('admin.requestMoney.index') }}" data-placement="left">
                                    <i class="fa-light fa-search-dollar nav-icon"></i>
                                    <span class="nav-link-title">@lang("Request Money List")</span>
                                </a>
                            </div>
                        @endif
                    @endif
                    @if($basic->exchange)
                        @if(checkPermission(3))
                            <div class="nav-item">
                                <a class="nav-link {{ menuActive(['admin.exchange.index']) }}"
                                   href="{{ route('admin.exchange.index') }}" data-placement="left">
                                    <i class="fa-light fa-exchange-alt nav-icon"></i>
                                    <span class="nav-link-title">@lang("Exchange List")</span>
                                </a>
                            </div>
                        @endif
                    @endif

                    @if(checkPermission(18))
                    <div class="nav-item">
                        <a class="nav-link {{ menuActive(['admin.transaction']) }}"
                           href="{{ route('admin.transaction') }}" data-placement="left">
                            <i class="bi bi-send nav-icon"></i>
                            <span class="nav-link-title">@lang("Transaction")</span>
                        </a>
                    </div>
                    @endif

                    @if($basic->payout)
                        @if(checkPermission(8))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.payout.log']) }}"
                               href="{{ route('admin.payout.log') }}" data-placement="left">
                                <i class="bi bi-wallet2 nav-icon "></i>
                                <span class="nav-link-title">@lang("Withdraw Log")</span>
                            </a>
                        </div>
                        @endif
                    @endif

                    @if($basic->deposit)
                        @if(checkPermission(1))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.payment.log']) }}"
                               href="{{ route('admin.payment.log') }}" data-placement="left">
                                <i class="bi bi-credit-card-2-front nav-icon"></i>
                                <span class="nav-link-title">@lang("Payment Log")</span>
                            </a>
                        </div>
                        <div class="nav-item ">
                            <a class="nav-link {{ menuActive(['admin.payment.pending']) }} "
                               href="{{ route('admin.payment.pending') }}" data-placement="left">
                                <i class="bi bi-cash nav-icon"></i>
                                @lang("Payment Request")
                                @if($sidebarCounts->deposit_pending > 0)
                                    <span class="badge bg-primary rounded-pill ms-5">{{ $sidebarCounts->deposit_pending }}</span>
                                @endif
                            </a>
                        </div>
                        @endif
                    @endif

                    <span class="dropdown-header mt-2">@lang('Payment Services')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    @if($basic->redeem)
                        @if(checkPermission(4))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.redeem.index']) }}"
                               href="{{ route('admin.redeem.index') }}" data-placement="left">
                                <i class="fa-light fa-gift nav-icon"></i>
                                <span class="nav-link-title">@lang("Redeem Code List")</span>
                            </a>
                        </div>
                        @endif
                    @endif

                    @if($basic->escrow)
                        @if(checkPermission(5))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.escrow.index']) }}"
                               href="{{ route('admin.escrow.index') }}" data-placement="left">
                                <i class="fa-light fa-handshake nav-icon"></i>
                                <span class="nav-link-title">@lang("Escrow List")</span>
                            </a>
                        </div>
                        @endif
                        @if(checkPermission(16))
                            <div class="nav-item">
                                <a class="nav-link {{ menuActive(['admin.dispute.index','admin.dispute.view']) }}"
                                   href="{{ route('admin.dispute.index') }}" data-placement="left">
                                    <i class="fa-light fa-gavel nav-icon"></i>
                                    <span class="nav-link-title">@lang("Dispute List")</span>
                                </a>
                            </div>
                        @endif
                    @endif

                    @if($basic->qr_payment)
                        @if(checkPermission(17))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.qr.payment']) }}"
                               href="{{ route('admin.qr.payment') }}" data-placement="left">
                                <i class="fa-light fa-qrcode nav-icon"></i>
                                <span class="nav-link-title">@lang("QR Payment")</span>
                            </a>
                        </div>
                        @endif
                    @endif
                    @if($basic->voucher)
                        @if(checkPermission(6))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.voucher.index']) }}"
                               href="{{ route('admin.voucher.index') }}" data-placement="left">
                                <i class="fa-light fa-file-invoice-dollar nav-icon"></i>
                                <span class="nav-link-title">@lang("Voucher List")</span>
                            </a>
                        </div>
                        @endif
                    @endif
                    @if($basic->invoice)
                        @if(checkPermission(9))
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.invoice.index']) }}"
                               href="{{ route('admin.invoice.index') }}" data-placement="left">
                                <i class="fa-light fa-file-invoice nav-icon"></i>
                                <span class="nav-link-title">@lang("Invoice List")</span>
                            </a>
                        </div>
                        @endif
                    @endif

                    @if($basic->bill_payment)
                        @if(checkPermission(11))
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle {{ menuActive(['admin.bill.pay.list','admin.bill.pay.view'], 3) }}"
                               href="#navbarVerticalBillPayMenu"
                               role="button"
                               data-bs-toggle="collapse"
                               data-bs-target="#navbarVerticalBillPayMenu"
                               aria-expanded="false"
                               aria-controls="navbarVerticalBillPayMenu">
                                <i class="fa-light fa-credit-card nav-icon"></i>
                                <span class="nav-link-title">@lang("Bill History")</span>
                            </a>
                            <div id="navbarVerticalBillPayMenu"
                                 class="nav-collapse collapse {{ menuActive(['admin.bill.pay.list','admin.bill.pay.view'], 2) }}"
                                 data-bs-parent="#navbarVerticalBillPayMenu">
                                <a class="nav-link {{ menuActive(['admin.bill.pay.view']) }} {{(collect(request()->segments())->last() == 'all' ? 'active':'')}}"
                                   href="{{ route('admin.bill.pay.list','all') }}">
                                    <span>@lang("All Bills")</span>
                                    <small class="d-none">@lang("Bill History > All Bills")</small>
                                </a>

                                <a class="nav-link {{(collect(request()->segments())->last() == 'pending' ? 'active':'')}}"
                                   href="{{ route('admin.bill.pay.list','pending') }}">
                                    <span>@lang("Pending Bills")</span>
                                    <small class="d-none">@lang("Bill History > Pending Bills")</small>
                                </a>

                                <a class="nav-link {{(collect(request()->segments())->last() == 'completed' ? 'active':'')}}"
                                   href="{{ route('admin.bill.pay.list','completed') }}">
                                    <span>@lang("Complete Bills")</span>
                                    <small class="d-none">@lang("Bill History > Complete Bills")</small>
                                </a>

                                <a class="nav-link {{(collect(request()->segments())->last() == 'return' ? 'active':'')}}"
                                   href="{{ route('admin.bill.pay.list','return') }}">
                                    <span>@lang("Return Bills")</span>
                                    <small class="d-none">@lang("Bill History > Return Bills")</small>
                                </a>
                            </div>
                        </div>
                        @endif
                    @endif

                    @if($basic->store)
                        @if(checkPermission(7))
                        <span class="dropdown-header mt-2"> @lang("Store Settings")</span>
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle {{ menuActive(['admin.store.list', 'admin.store.view', 'admin.product.list',
                               'admin.product.view','admin.order.list','admin.order.view','admin.contact.list'], 3) }}"
                               href="#navbarVerticalStoreMenu" role="button"
                               data-bs-toggle="collapse" data-bs-target="#navbarVerticalStoreMenu"
                               aria-expanded="false" aria-controls="navbarVerticalStoreMenu">
                                <i class="fa-light fa-store nav-icon"></i>
                                <span class="nav-link-title">@lang("Store Management")</span>
                            </a>
                            <div id="navbarVerticalStoreMenu" class="nav-collapse collapse
                                 {{ menuActive(['admin.store.list', 'admin.store.view', 'admin.product.list',
                                    'admin.product.view','admin.order.list','admin.order.view','admin.contact.list'], 2) }}"
                                 data-bs-parent="#navbarVerticalStoreMenu">
                                <a class="nav-link {{ menuActive(['admin.store.list','admin.store.view']) }}"
                                   href="{{ route('admin.store.list') }}">
                                    <span>@lang("Create Store")</span>
                                    <small class="d-none">@lang("Store Management > Create Store")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['admin.product.list','admin.product.view']) }}"
                                   href="{{ route('admin.product.list') }}">
                                    <span>@lang("Product List")</span>
                                    <small class="d-none">@lang("Store Management > Product List")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['admin.order.list','admin.order.view']) }}"
                                   href="{{ route('admin.order.list') }}">
                                    <span>@lang("Order List")</span>
                                    <small class="d-none">@lang("Store Management > Order List")</small>
                                </a>
                                <a class="nav-link {{ menuActive('admin.contact.list') }}"
                                   href="{{ route('admin.contact.list') }}">
                                    <span>@lang("Contact List")</span>
                                    <small class="d-none">@lang("Store Management > Contact List")</small>
                                </a>
                            </div>
                        </div>
                        @endif
                    @endif

                    @if(checkPermission(19))
                    <span class="dropdown-header mt-2"> @lang("Referral Settings")</span>
                    <div class="nav-item">
                        <a class="nav-link dropdown-toggle {{ menuActive(['admin.referral.bonus.index', 'admin.commission.index'], 3) }}"
                           href="#navbarVerticalReferMenu" role="button"
                           data-bs-toggle="collapse" data-bs-target="#navbarVerticalReferMenu"
                           aria-expanded="false" aria-controls="navbarVerticalReferMenu">
                            <i class="fa-light fa-money-bill nav-icon"></i>
                            <span class="nav-link-title">@lang("Referral Settings")</span>
                        </a>
                        <div id="navbarVerticalReferMenu" data-bs-parent="#navbarVerticalReferMenu"
                             class="nav-collapse collapse {{ menuActive(['admin.referral.bonus.index', 'admin.commission.index'], 2) }}"
                        >
                            <a class="nav-link {{ menuActive(['admin.referral.bonus.index']) }}"
                               href="{{ route('admin.referral.bonus.index') }}">
                                <span>@lang("Referral Bonus")</span>
                                <small class="d-none">@lang("Referral Settings > Referral Bonus")</small>
                            </a>
                            <a class="nav-link {{ menuActive(['admin.commission.index']) }}"
                               href="{{ route('admin.commission.index') }}">
                                <span>@lang("Commission List")</span>
                                <small class="d-none">@lang("Referral Settings > Commission List")</small>
                            </a>
                        </div>
                    </div>
                    @endif

                    <span class="dropdown-header mt-2"> @lang("Ticket Panel")</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>
                    <div class="nav-item">
                        <a class="nav-link dropdown-toggle {{ menuActive(['admin.ticket', 'admin.ticket.search', 'admin.ticket.view'], 3) }}"
                           href="#navbarVerticalTicketMenu" role="button"
                           data-bs-toggle="collapse" data-bs-target="#navbarVerticalTicketMenu"
                           aria-expanded="false" aria-controls="navbarVerticalTicketMenu">
                            <i class="fa-light fa-headset nav-icon"></i>
                            <span class="nav-link-title">@lang("Support Ticket")</span>
                        </a>
                        <div id="navbarVerticalTicketMenu"
                             class="nav-collapse collapse {{ menuActive(['admin.ticket','admin.ticket.search', 'admin.ticket.view'], 2) }}"
                             data-bs-parent="#navbarVerticalTicketMenu">
                            <a class="nav-link {{ request()->is('admin/tickets/all') ? 'active' : '' }}" href="{{ route('admin.ticket', 'all') }}">
                                <span>@lang("All Tickets")</span>
                                <small class="d-none">@lang("Support Ticket > All Tickets")</small>
                            </a>
                            <a class="nav-link {{ request()->is('admin/tickets/answered') ? 'active' : '' }}"
                               href="{{ route('admin.ticket', 'answered') }}">
                                <span>@lang("Answered Tickets")</span>
                                <small class="d-none">@lang("Support Ticket > Answered Tickets")</small>
                            </a>
                            <a class="nav-link {{ request()->is('admin/tickets/replied') ? 'active' : '' }}"
                               href="{{ route('admin.ticket', 'replied') }}">
                                <span>@lang("Replied Tickets")</span>
                                <small class="d-none">@lang("Support Ticket > Replied Tickets")</small>
                            </a>
                            <a class="nav-link {{ request()->is('admin/tickets/closed') ? 'active' : '' }}"
                               href="{{ route('admin.ticket', 'closed') }}">
                                <span>@lang("Closed Tickets")</span>
                                <small class="d-none">@lang("Support Ticket > Closed Tickets")</small>
                            </a>
                        </div>
                    </div>

                    @if(checkPermission(14))
                    <span class="dropdown-header mt-2"> @lang('Kyc Management')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>
                    <div class="nav-item">
                        <a class="nav-link {{ menuActive(['admin.kyc.form.list','admin.kyc.edit','admin.kyc.create']) }}"
                           href="{{ route('admin.kyc.form.list') }}" data-placement="left">
                            <i class="bi-stickies nav-icon"></i>
                            <span class="nav-link-title">@lang('KYC Setting')</span>
                            <small class="d-none">@lang("Kyc Management > KYC Setting")</small>
                        </a>
                    </div>

                    <div class="nav-item" {{ menuActive(['admin.kyc.list*','admin.kyc.view'], 3) }}>
                        <a class="nav-link dropdown-toggle collapsed" href="#navbarVerticalKycRequestMenu"
                           role="button"
                           data-bs-toggle="collapse" data-bs-target="#navbarVerticalKycRequestMenu"
                           aria-expanded="false"
                           aria-controls="navbarVerticalKycRequestMenu">
                            <i class="bi bi-person-lines-fill nav-icon"></i>
                            <span class="nav-link-title">@lang("KYC Request")</span>
                        </a>
                        <div id="navbarVerticalKycRequestMenu"
                             class="nav-collapse collapse {{ menuActive(['admin.kyc.list*','admin.kyc.view'], 2) }}"
                             data-bs-parent="#navbarVerticalKycRequestMenu">
                            <a class="nav-link d-flex justify-content-between {{ Request::is('admin/kyc/pending') ? 'active' : '' }}"
                               href="{{ route('admin.kyc.list', 'pending') }}">
                                <span>@lang("Pending KYC")</span>
                                <small class="d-none">@lang("Kyc Management > Pending KYC")</small>
                                @if($sidebarCounts->kyc_pending > 0)
                                    <span class="badge bg-primary rounded-pill ">{{ $sidebarCounts->kyc_pending }}</span>
                                @endif
                            </a>
                            <a class="nav-link d-flex justify-content-between {{ Request::is('admin/kyc/approve') ? 'active' : '' }}"
                               href="{{ route('admin.kyc.list', 'approve') }}">
                                <span>@lang("Approved KYC")</span>
                                <small class="d-none">@lang("Kyc Management > Approved KYC")</small>
                            </a>
                            <a class="nav-link {{ Request::is('admin/kyc/rejected') ? 'active' : '' }}"
                               href="{{ route('admin.kyc.list', 'rejected') }}">
                                <span>@lang("Rejected KYC")</span>
                                <small class="d-none">@lang("Kyc Management > Rejected KYC")</small>
                            </a>
                        </div>
                    </div>
                    @endif
                    @if(checkPermission(13))
                    <span class="dropdown-header mt-2"> @lang("User Panel")</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>
                    <div class="nav-item">
                        <a class="nav-link dropdown-toggle {{ menuActive(['admin.users'], 3) }}"
                           href="#navbarVerticalUserPanelMenu"
                           role="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalUserPanelMenu"
                           aria-expanded="false" aria-controls="navbarVerticalUserPanelMenu">
                            <i class="bi-people nav-icon"></i>
                            <span class="nav-link-title">@lang('User Management')</span>
                        </a>
                        <div id="navbarVerticalUserPanelMenu" class="nav-collapse collapse
                             {{ menuActive(['admin.mail.all.user','admin.users'], 2) }}"
                             data-bs-parent="#navbarVerticalUserPanelMenu">

                            <a class="nav-link {{ request()->is('admin/users') ? 'active' : '' }}" href="{{ route('admin.users') }}">
                                <span>@lang("All User")</span>
                                <small class="d-none">@lang("User Management > All User")</small>
                            </a>

                            <a href="{{ route('admin.users','active-users') }}" class="nav-link d-flex justify-content-between
                                {{ request()->is('admin/users/active-users') ? 'active' : '' }}">
                                <span>@lang("Active Users")</span>
                                <small class="d-none">@lang("User Management > Active Users")</small>

                                @if($sidebarCounts->active_users > 0)
                                    <span class="badge bg-primary rounded-pill ">{{ $sidebarCounts->active_users }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.users','blocked-users') }}" class="nav-link d-flex justify-content-between
                                {{ request()->is('admin/users/blocked-users') ? 'active' : '' }}">
                                <span>@lang("Blocked Users")</span>
                                <small class="d-none">@lang("User Management > Blocked Users")</small>
                                @if($sidebarCounts->blocked_users > 0)
                                    <span class="badge bg-primary rounded-pill ">{{ $sidebarCounts->blocked_users }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.users','email-unverified') }}" class="nav-link d-flex justify-content-between
                               {{ request()->is('admin/users/email-unverified') ? 'active' : '' }}">
                                <span>@lang("Email Unverified")</span>
                                <small class="d-none">@lang("User Management > Email Unverified")</small>
                                @if($sidebarCounts->email_unverified > 0)
                                    <span class="badge bg-primary rounded-pill ">{{ $sidebarCounts->email_unverified }}</span>
                                @endif
                            </a>
                            <a href="{{ route('admin.users','sms-unverified') }}" class="nav-link d-flex justify-content-between
                               {{ request()->is('admin/users/sms-unverified') ? 'active' : '' }}">
                                <span>@lang("Sms Unverified")</span>
                                <small class="d-none">@lang("User Management > Sms Unverified")</small>
                                @if($sidebarCounts->sms_unverified > 0)
                                    <span class="badge bg-primary rounded-pill ">{{ $sidebarCounts->sms_unverified }}</span>
                                @endif
                            </a>

                            <a class="nav-link {{ menuActive(['admin.mail.all.user']) }}" href="{{ route("admin.mail.all.user") }}">
                                <span>@lang("Mail To Users")</span>
                                <small class="d-none">@lang("User Management > Mail To Users")</small>
                            </a>
                        </div>
                    </div>
                    @endif

                    @stack('extra_menu')

                    <span class="dropdown-header mt-2">@lang('Subscriber')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>
                    <div class="nav-item">
                        <a class="nav-link {{ menuActive(['admin.subscriber.index','admin.subscriber.mail']) }}"
                           href="{{ route('admin.subscriber.index') }}" data-placement="left">
                            <i class="bi bi-envelope nav-icon"></i>
                            <span class="nav-link-title">@lang("Subscribers")</span>
                        </a>
                    </div>

                    <span class="dropdown-header mt-2"> @lang('SETTINGS PANEL')</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    <div class="nav-item">
                        <a class="nav-link {{ menuActive(controlPanelRoutes()) }}"
                           href="{{ route('admin.settings') }}" data-placement="left">
                            <i class="bi bi-gear nav-icon"></i>
                            <span class="nav-link-title">@lang('Control Panel')</span>
                        </a>
                    </div>

                    @if(any_modules_exist())
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.addon.index']) }}"
                               href="{{ route('admin.addon.index') }}" data-placement="left">
                                <i class="bi bi-plugin nav-icon"></i>
                                <span class="nav-link-title">@lang('Addon Manager')</span>
                                <span class="badge bg-info rounded-pill ms-1">@lang('Addon')</span>
                            </a>
                        </div>
                    @endif

                    @if(checkPermission(12))
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle {{ menuActive(['admin.role','admin.role.staff'],3) }}"
                               href="#navbarRoleMenu" role="button" data-bs-toggle="collapse" data-bs-target="#navbarRoleMenu"
                               aria-expanded="false" aria-controls="navbarRoleMenu">
                                <i class="bi-people nav-icon"></i>
                                <span class="nav-link-title">@lang("Roles & Permission")</span>
                            </a>
                            <div id="navbarRoleMenu" class="nav-collapse collapse {{ menuActive(['admin.role','admin.role.staff'],2) }}"
                                 data-bs-parent="#navbarRoleMenu">
                                <a class="nav-link {{ menuActive(['admin.role']) }}" href="{{ route('admin.role') }}">
                                    <span>@lang("Available Roles")</span>
                                    <small class="d-none">@lang("Roles and Permission > Available Roles")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['admin.role.staff']) }}" href="{{ route('admin.role.staff') }}">
                                    <span>@lang("Manage Staffs")</span>
                                    <small class="d-none">@lang("Roles and Permission > Manage Staffs")</small>
                                </a>
                            </div>
                        </div>
                    @endif

                    @if($basic->virtual_card)
                        @if(checkPermission(10))
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle {{ menuActive(['admin.virtual.card','admin.virtual.cardOrder',
                                        'admin.virtual.cardOrderDetail', 'admin.virtual.cardList','admin.virtual.cardView','admin.virtual.cardTransaction'], 3) }}"
                               href="#navbarVerticalVirtualMenu"
                               role="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalVirtualMenu"
                               aria-expanded="false" aria-controls="navbarVerticalVirtualMenu">
                                <i class="fa-light fa-address-card nav-icon"></i>
                                <span class="nav-link-title">@lang("Virtual Card")</span>
                            </a>
                            <div id="navbarVerticalVirtualMenu" class="nav-collapse collapse
                                 {{ menuActive(['admin.virtual.card','admin.virtual.cardEdit','admin.virtual.cardOrder','admin.virtual.cardOrderDetail',
                                    'admin.virtual.cardList','admin.virtual.cardView','admin.virtual.cardTransaction'], 2) }}"
                                 data-bs-parent="#navbarVerticalVirtualMenu">
                                <a class="nav-link {{ menuActive(['admin.virtual.card','admin.virtual.cardEdit']) }}"
                                   href="{{ route('admin.virtual.card') }}">
                                    <span>@lang("Available Methods")</span>
                                    <small class="d-none">@lang("Virtual Card > Available Methods")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['admin.virtual.cardOrder','admin.virtual.cardOrderDetail']) }}"
                                   href="{{ route('admin.virtual.cardOrder') }}">
                                    <span>@lang("Request List")</span>
                                    <small class="d-none">@lang("Virtual Card > Request List")</small>
                                </a>
                                <a class="nav-link {{menuActive(['admin.virtual.cardView','admin.virtual.cardTransaction'])}} {{(collect(request()->segments())->last() == 'all' ? 'active':'')}}"
                                   href="{{ route('admin.virtual.cardList','all') }}">
                                    <span>@lang("Card List")</span>
                                    <small class="d-none">@lang("Virtual Card > Card List")</small>
                                </a>
                                <a class="nav-link {{(collect(request()->segments())->last() == 'add-fund' ? 'active':'')}}"
                                   href="{{ route('admin.virtual.cardList','add-fund') }}">
                                    <span>@lang("Add Fund Request")</span>
                                    <small class="d-none">@lang("Virtual Card > Add Fund Request")</small>
                                </a>
                                <a class="nav-link {{(collect(request()->segments())->last() == 'block' ? 'active':'')}}"
                                   href="{{ route('admin.virtual.cardList','block') }}">
                                    <span>@lang("Block Request")</span>
                                    <small class="d-none">@lang("Virtual Card > Block Request")</small>
                                </a>
                            </div>
                        </div>
                        @endif
                    @endif

                    @if($basic->bill_payment)
                        @if(checkPermission(11))
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle {{ menuActive(['admin.bill.method.list','admin.bill.method.edit','admin.bill.fetch.service',
                               'admin.bill.service.list'], 3) }}"
                               href="#navbarVerticalBillMethodMenu"
                               role="button"
                               data-bs-toggle="collapse"
                               data-bs-target="#navbarVerticalBillMethodMenu"
                               aria-expanded="false"
                               aria-controls="navbarVerticalBillMethodMenu">
                                <i class="fa-light fa-money-bill nav-icon"></i>
                                <span class="nav-link-title">@lang("Bill Setting")</span>
                            </a>
                            <div id="navbarVerticalBillMethodMenu"
                                 class="nav-collapse collapse {{ menuActive(['admin.bill.method.list','admin.bill.method.edit','admin.bill.fetch.service',
                                    'admin.bill.service.list'], 2) }}"
                                 data-bs-parent="#navbarVerticalBillMethodMenu">
                                <a class="nav-link {{ menuActive(['admin.bill.method.list','admin.bill.method.edit','admin.bill.fetch.service']) }}"
                                   href="{{ route('admin.bill.method.list') }}">
                                    <span>@lang("Available Methods")</span>
                                    <small class="d-none">@lang("Bill Settings > Available Methods")</small>
                                </a>
                                <a class="nav-link {{ menuActive(['admin.bill.service.list']) }}"
                                   href="{{ route('admin.bill.service.list') }}">
                                    <span>@lang("Service List")</span>
                                    <small class="d-none">@lang("Bill Settings > Service List")</small>
                                </a>
                            </div>
                        </div>
                        @endif
                    @endif


                    <div
                        class="nav-item {{ menuActive(['admin.payment.methods', 'admin.edit.payment.methods', 'admin.deposit.manual.index', 'admin.deposit.manual.create', 'admin.deposit.manual.edit'], 3) }}">
                        <a class="nav-link dropdown-toggle"
                           href="#navbarVerticalGatewayMenu"
                           role="button"
                           data-bs-toggle="collapse"
                           data-bs-target="#navbarVerticalGatewayMenu"
                           aria-expanded="false"
                           aria-controls="navbarVerticalGatewayMenu">
                            <i class="bi-briefcase nav-icon"></i>
                            <span class="nav-link-title">@lang('Payment Setting')</span>
                        </a>
                        <div id="navbarVerticalGatewayMenu" class="nav-collapse collapse
                            {{ menuActive(['admin.payment.methods', 'admin.edit.payment.methods', 'admin.deposit.manual.*'], 2) }}"
                             data-bs-parent="#navbarVerticalGatewayMenu">

                            <a class="nav-link {{ menuActive(['admin.payment.methods', 'admin.edit.payment.methods',]) }}"
                               href="{{ route('admin.payment.methods') }}">
                                <span>@lang("Payment Gateway")</span>
                                <small class="d-none">@lang("Payment Settings > Payment Gateway")</small>
                            </a>
                            <a class="nav-link {{ menuActive([ 'admin.deposit.manual.*']) }}"
                               href="{{ route('admin.deposit.manual.index') }}">
                                <span>@lang("Manual Gateway")</span>
                                <small class="d-none">@lang("Payment Settings > Manual Gateway")</small>
                            </a>
                        </div>
                    </div>


                    <div class="nav-item">
                        <a class="nav-link dropdown-toggle {{ menuActive(['admin.payout.method.list','admin.payout.method.create','admin.manual.method.edit','admin.payout.method.edit','admin.payout.withdraw.days'], 3) }}"
                           href="#navbarVerticalWithdrawMenu"
                           role="button"
                           data-bs-toggle="collapse"
                           data-bs-target="#navbarVerticalWithdrawMenu"
                           aria-expanded="false"
                           aria-controls="navbarVerticalWithdrawMenu">
                            <i class="bi bi-wallet2 nav-icon"></i>
                            <span class="nav-link-title">@lang('Withdraw Setting')</span>
                        </a>
                        <div id="navbarVerticalWithdrawMenu"
                             class="nav-collapse collapse {{ menuActive(['admin.payout.method.*','admin.manual.method.edit','admin.payout.withdraw.days'], 2) }}"
                             data-bs-parent="#navbarVerticalWithdrawMenu">
                            <a class="nav-link {{ menuActive(['admin.payout.method.*','admin.manual.method.edit']) }}"
                               href="{{ route('admin.payout.method.list') }}">
                                <span>@lang("Withdraw Method")</span>
                                <small class="d-none">@lang("Withdraw Settings > Withdraw Method")</small>
                            </a>

                            <a class="nav-link  {{ menuActive(['admin.payout.withdraw.days']) }}"
                               href="{{ route("admin.payout.withdraw.days") }}">
                                <span>@lang("Withdrawal Days Setup")</span>
                                <small class="d-none">@lang("Withdraw Settings > Withdrawal Days Setup")</small>
                            </a>
                        </div>
                    </div>

                    @if(checkPermission(20))
                    <span class="dropdown-header mt-2">@lang("Appearance")</span>
                    <small class="bi-three-dots nav-subtitle-replacer"></small>

                    <div class="nav-item">
                        <a class="nav-link {{ menuActive(['admin.theme']) }}"
                           href="{{ route('admin.theme') }}" data-placement="left">
                            <i class="bi bi-gear nav-icon"></i>
                            <span class="nav-link-title">@lang('Manage Theme')</span>
                        </a>
                    </div>

                    <div id="navbarVerticalThemeMenu">
                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.page.index','admin.create.page','admin.edit.page']) }}"
                               href="{{ route('admin.page.index', basicControl()->theme) }}"
                               data-placement="left">
                                <i class="fa-light fa-list nav-icon"></i>
                                <span class="nav-link-title">@lang('Pages')</span>
                            </a>
                        </div>

                        <div class="nav-item">
                            <a class="nav-link {{ menuActive(['admin.manage.menu']) }}"
                               href="{{ route('admin.manage.menu') }}" data-placement="left">
                                <i class="bi-folder2-open nav-icon"></i>
                                <span class="nav-link-title">@lang('Manage Menu')</span>
                            </a>
                        </div>
                    </div>

                        @php
                            $segments = request()->segments();
                            $last  = end($segments);
                        @endphp
                        <div class="nav-item">
                            <a class="nav-link dropdown-toggle
                            {{ menuActive(['admin.manage.content', 'admin.manage.content.multiple', 'admin.content.item.edit*'], 3) }}"
                               href="#navbarVerticalContentsMenu" role="button" data-bs-toggle="collapse"
                               data-bs-target="#navbarVerticalContentsMenu" aria-expanded="false"
                               aria-controls="navbarVerticalContentsMenu">
                                <i class="fa-light fa-pen nav-icon"></i>
                                <span class="nav-link-title">@lang('Manage Content')</span>
                            </a>
                            <div id="navbarVerticalContentsMenu"
                                 class="nav-collapse collapse {{ menuActive(['admin.manage.content', 'admin.manage.content.multiple', 'admin.content.item.edit*'], 2) }}"
                                 data-bs-parent="#navbarVerticalContentsMenu">

                                @foreach(array_diff(array_keys($manageContents), ['message','content_media']) as $name)
                                    <div class="talk-nav-link">
                                        <a class="nav-link {{($last == $name) ? 'active' : '' }}"
                                           href="{{ route('admin.manage.content', $name) }}">
                                            @lang(stringToTitle($name))
                                        </a>
                                        @php
                                            @$preview = $manageContents[$name]['preview'] ?? null;
                                            $previewPath = $preview ? asset(str_replace(':theme', basicControl()->theme, $preview)) : null;
                                        @endphp
                                        @if($previewPath)
                                            <div class="talk-nav-link-icon">
                                                <a href="{{$previewPath}}" data-fancybox="gallery" class="bg-soft-dark">
                                                    <i class="fa-light fa-eye"></i>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>

                    @endif

                    <div class="nav-item">
                        <a class="nav-link dropdown-toggle {{ menuActive(['admin.blog-category.index', 'admin.blog-category.create','admin.blog-category.edit', 'admin.blogs.index', 'admin.blogs.create','admin.blogs.edit*'], 3) }}"
                           href="#navbarVerticalBlogMenu"
                           role="button" data-bs-toggle="collapse"
                           data-bs-target="#navbarVerticalBlogMenu" aria-expanded="false"
                           aria-controls="navbarVerticalBlogMenu">
                            <i class="fa-light fa-newspaper nav-icon"></i>
                            <span class="nav-link-title">@lang('Manage Blog')</span>
                        </a>
                        <div id="navbarVerticalBlogMenu"
                             class="nav-collapse collapse {{ menuActive(['admin.blog-category.*', 'admin.blogs.index', 'admin.blogs.create','admin.blogs.edit*'], 2) }}"
                             data-bs-parent="#navbarVerticalBlogMenu">
                            <a class="nav-link {{ menuActive(['admin.blog-category.*']) }}" href="{{ route('admin.blog-category.index') }}">
                                <span>@lang("Blog Category")</span>
                                <small class="d-none">@lang("Manage Blog > Blog Category")</small>
                            </a>

                            <a class="nav-link {{ menuActive(['admin.blogs.index', 'admin.blogs.create','admin.blogs.edit*']) }}"
                               href="{{ route('admin.blogs.index') }}">
                                <span>@lang("Blogs")</span>
                                <small class="d-none">@lang("Manage Blog > Blogs")</small>
                            </a>
                        </div>
                    </div>

                    <div class="nav-item">
                        <a class="nav-link"
                           href="{{ route('clear') }}" data-placement="left">
                            <i class="bi bi-radioactive nav-icon"></i>
                            <span class="nav-link-title">@lang('Clear Cache')</span>
                        </a>
                    </div>

                    @foreach(collect(config('generalsettings.settings')) as $key => $setting)
                        <div class="nav-item d-none">
                            <a class="nav-link  {{ isMenuActive($setting['route']) }}"
                               href="{{ getRoute($setting['route'], $setting['route_segment'] ?? null) }}">
                                <i class="{{$setting['icon']}} nav-icon"></i>
                                <span class="nav-link-title">{{ __(getTitle($key.' '.'Settings')) }}</span>
                            </a>
                        </div>
                    @endforeach


                </div>

                <div class="navbar-vertical-footer">
                    <ul class="navbar-vertical-footer-list">
                        <li class="navbar-vertical-footer-list-item">
                            <span class="dropdown-header">@lang('Version 4.1 ')</span>
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




