<div id="sidebar" class="">
    <div class="sidebar-top">
        <a class="navbar-brand" href="{{ route('user.dashboard') }}"> <img src="{{ asset(template(true) . 'image/icon/logo.png') }}" alt=""/></a>
        <button class="sidebar-toggler d-lg-none" onclick="toggleSideMenu()">
            <i class="fal fa-times"></i>
        </button>
    </div>
    <div class="level-box">
        <div>
            <h4>Level 4</h4>
            <p class="mb-0">Hyip Victor</p>
        </div>
        <img src="{{ asset(template(true).'image/icon/gold-medal.png') }}" alt="" class="level-badge"/>
    </div>
    <ul class="main">
        <li>
            <a class="{{ isMenuActive(['user.dashboard']) }}" href="{{ route('user.dashboard') }}"><i class="fal fa-th-large"></i>Dashboard</a>
        </li>
        <li>
            <a class="{{isMenuActive(['user.profile'])}}" href="{{ route('user.profile') }}"><i class="fal fa-user-edit"></i>Edit Profile</a>
        </li>
        <li>
            <a class="{{isMenuActive(['user.ticket.list'])}}" href="{{ route("user.ticket.list") }}"><i class="fal fa-wallet"></i>Support Ticket</a>
        </li>
        <li>
            <a
                class="dropdown-toggle"
                data-bs-toggle="collapse"
                href="#dropdownCollapsible"
                role="button"
                aria-expanded="false"
                aria-controls="collapseExample">
                <i class="fa-light fa-money-bill"></i> Fund
            </a>
            <div class="collapse dropdown-collapsible" id="dropdownCollapsible">
                <ul>
                    <li>

                        <a class="{{ isMenuActive('user.add.fund') }}" href="{{ route('user.add.fund') }}"><i class="fal fa-th-large"></i>Add Fund</a>
                    </li>
                    <li>

                        <a class="{{ isMenuActive("user.fund.index") }}" href="{{ route('user.fund.index') }}"><i class="fal fa-th-large"></i>List</a>
                    </li>
                </ul>
            </div>
        </li>

        <li>
            <a class="dropdown-toggle"
                data-bs-toggle="collapse"
                href="#dropdownCollapsible2"
                role="button"
                aria-expanded="false"
                aria-controls="collapseExample">
                <i class="fa-light fa-money-bill-wave"></i> Payout
            </a>
            <div class="collapse dropdown-collapsible" id="dropdownCollapsible2">
                <ul class="">
                    <li>
                        <a class="{{ isMenuActive('user.payout') }}" href="{{ route('user.payout') }}"><i class="fal fa-th-large"></i>Payout</a>
                    </li>
                    <li>
                        <a class="{{ isMenuActive('user.payout.index') }}" href="{{ route("user.payout.index") }}"><i class="fal fa-th-large"></i>List</a>
                    </li>
                </ul>
            </div>
        </li>

        <li>
            <a class="{{ menuActive(['user.twostep.security'], 3) }}"
               href="{{ route('user.twostep.security') }}">
                <i class="fal fa-lock text-orange" aria-hidden="true"></i> @lang('2FA Security')
            </a>
        </li>

        <li>
            <a class="dropdown-toggle"
               data-bs-toggle="collapse"
               href="#dropdownCollapsible3"
               role="button"
               aria-expanded="false"
               aria-controls="collapseExample">
                <i class="fa-light fa-badge-check"></i> Verification Center
            </a>
            <div class="collapse dropdown-collapsible" id="dropdownCollapsible3">
                <ul class="">
                    <li>
                        <a class="{{ isMenuActive('user.verification.kyc') }}" href="{{ route('user.verification.kyc') }}"><i class="fa-light fa-arrow-right"></i>KYC</a>
                    </li>
                    <li>
                        <a class="{{ isMenuActive('user.verification.kyc.history') }}" href="{{ route("user.verification.kyc.history") }}"><i class="fa-light fa-arrow-right"></i>History</a>
                    </li>
                </ul>
            </div>
        </li>

        <li>
            @auth
                <a href="{{ route('logout') }}"
                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();"><i
                        class="fal fa-sign-out-alt"></i>@lang('Sign Out')</a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            @endauth
        </li>
    </ul>
</div>
