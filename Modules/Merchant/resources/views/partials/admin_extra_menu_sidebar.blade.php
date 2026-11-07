


<span class="dropdown-header d-flex justify-content-between mt-4">
    @lang("Merchant Panel")
    <span class="badge bg-info rounded-pill ms-1">@lang('Addon')</span>
</span>


<small class="bi-three-dots nav-subtitle-replacer"></small>
<div class="nav-item">
    <a class="nav-link dropdown-toggle {{ menuActive(['admin.merchants'], 3) }}"
       href="#navbarVerticalMerchantPanelMenu"
       role="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalMerchantPanelMenu"
       aria-expanded="false" aria-controls="navbarVerticalMerchantPanelMenu">
        <i class="bi-people nav-icon"></i>
        <span class="nav-link-title">@lang('Merchant Management')</span>
    </a>
    <div id="navbarVerticalMerchantPanelMenu" class="nav-collapse collapse
                             {{ menuActive(['admin.merchants'], 2) }}"
         data-bs-parent="#navbarVerticalMerchantPanelMenu">

        <a class="nav-link {{ request()->is('admin/merchants') ? 'active' : '' }}" href="{{ route('admin.merchants') }}">
            <span>@lang("All Merchant")</span>
            <small class="d-none">@lang("Merchant Management > All Merchant")</small>
        </a>

        <a href="{{ route('admin.merchants','active-merchants') }}" class="nav-link d-flex justify-content-between
                                {{ request()->is('admin/merchants/active-merchants') ? 'active' : '' }}">
            <span>@lang("Active Merchants")</span>
            <small class="d-none">@lang("Merchant Management > Active Merchants")</small>

            @if($merchantCounts->active > 0)
                <span class="badge bg-primary rounded-pill ">{{ $merchantCounts->active }}</span>
            @endif
        </a>
        <a href="{{ route('admin.merchants','blocked-mercants') }}" class="nav-link d-flex justify-content-between
                                {{ request()->is('admin/merchants/blocked-merchants') ? 'active' : '' }}">
            <span>@lang("Blocked Mercants")</span>
            <small class="d-none">@lang("Merchant Management > Blocked Mercants")</small>
            @if($merchantCounts->blocked > 0)
                <span class="badge bg-primary rounded-pill ">{{ $merchantCounts->blocked }}</span>
            @endif
        </a>
        <a href="{{ route('admin.merchants','email-unverified') }}" class="nav-link d-flex justify-content-between
                               {{ request()->is('admin/merchants/email-unverified') ? 'active' : '' }}">
            <span>@lang("Email Unverified")</span>
            <small class="d-none">@lang("Merchant Management > Email Unverified")</small>
            @if($merchantCounts->email_unverified > 0)
                <span class="badge bg-primary rounded-pill ">{{ $merchantCounts->email_unverified }}</span>
            @endif
        </a>
        <a href="{{ route('admin.merchants','sms-unverified') }}" class="nav-link d-flex justify-content-between
                               {{ request()->is('admin/merchants/sms-unverified') ? 'active' : '' }}">
            <span>@lang("Sms Unverified")</span>
            <small class="d-none">@lang("Merchant Management > Sms Unverified")</small>
            @if($merchantCounts->sms_unverified > 0)
                <span class="badge bg-primary rounded-pill ">{{ $merchantCounts->sms_unverified }}</span>
            @endif
        </a>

    </div>
</div>
