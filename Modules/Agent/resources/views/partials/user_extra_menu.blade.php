{{--
<li class="hs-has-sub-menu nav-item">
    <a id="appsMegaMenu" class="hs-mega-menu-invoker nav-link dropdown-toggle"
       href="javascript:void(0)" role="button">
        <i class="bi-wallet2 dropdown-item-icon"></i> @lang('Money Movement')
    </a>

    <div class="hs-sub-menu dropdown-menu navbar-dropdown-menu-borderless"
         aria-labelledby="appsMegaMenu" style="min-width: 14rem;">
        <a class="dropdown-item" href="{{ route('user.cash_out.create') }}">
            <span><i class="bi-credit-card"></i> @lang('Cash Out')</span>
            <small class="d-none">@lang('Money Movement > Cash Out')</small>
        </a>

    </div>
</li>
--}}

@if($basic->cash_out)
    <li class="nav-item">
        <a class="nav-link" href="{{ route('user.cash_out.create') }}">
            <i class="bi-cash-stack"></i> @lang('Cash Out')
        </a>
    </li>
@endif
