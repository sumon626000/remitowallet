


<span class="dropdown-header d-flex justify-content-between mt-4">
    @lang("Agent Panel")
    <span class="badge bg-info rounded-pill ms-1">@lang('Addon')</span>
</span>


<small class="bi-three-dots nav-subtitle-replacer"></small>
<div class="nav-item">
    <a class="nav-link dropdown-toggle {{ menuActive(['admin.agents'], 3) }}"
       href="#navbarVerticalAgentPanelMenu"
       role="button" data-bs-toggle="collapse" data-bs-target="#navbarVerticalAgentPanelMenu"
       aria-expanded="false" aria-controls="navbarVerticalAgentPanelMenu">
        <i class="bi-people nav-icon"></i>
        <span class="nav-link-title">@lang('Agent Management')</span>
    </a>
    <div id="navbarVerticalAgentPanelMenu" class="nav-collapse collapse
                             {{ menuActive(['admin.agents'], 2) }}"
         data-bs-parent="#navbarVerticalAgentPanelMenu">

        <a class="nav-link {{ request()->is('admin/agents') ? 'active' : '' }}" href="{{ route('admin.agents') }}">
            <span>@lang("All Agent")</span>
            <small class="d-none">@lang("Agent Management > All Agent")</small>
        </a>

        <a href="{{ route('admin.agents','active-agents') }}" class="nav-link d-flex justify-content-between
                                {{ request()->is('admin/agents/active-agents') ? 'active' : '' }}">
            <span>@lang("Active Agents")</span>
            <small class="d-none">@lang("Agent Management > Active Agents")</small>

            @if($agentCounts->active > 0)
                <span class="badge bg-primary rounded-pill ">{{ $agentCounts->active }}</span>
            @endif
        </a>
        <a href="{{ route('admin.agents','blocked-agents') }}" class="nav-link d-flex justify-content-between
                                {{ request()->is('admin/agents/blocked-agents') ? 'active' : '' }}">
            <span>@lang("Blocked Agents")</span>
            <small class="d-none">@lang("Agent Management > Blocked Agents")</small>
            @if($agentCounts->blocked > 0)
                <span class="badge bg-primary rounded-pill ">{{ $agentCounts->blocked }}</span>
            @endif
        </a>
        <a href="{{ route('admin.agents','email-unverified') }}" class="nav-link d-flex justify-content-between
                               {{ request()->is('admin/agents/email-unverified') ? 'active' : '' }}">
            <span>@lang("Email Unverified")</span>
            <small class="d-none">@lang("Agent Management > Email Unverified")</small>
            @if($agentCounts->email_unverified > 0)
                <span class="badge bg-primary rounded-pill ">{{ $agentCounts->email_unverified }}</span>
            @endif
        </a>
        <a href="{{ route('admin.agents','sms-unverified') }}" class="nav-link d-flex justify-content-between
                               {{ request()->is('admin/agents/sms-unverified') ? 'active' : '' }}">
            <span>@lang("Sms Unverified")</span>
            <small class="d-none">@lang("Agent Management > Sms Unverified")</small>
            @if($agentCounts->sms_unverified > 0)
                <span class="badge bg-primary rounded-pill ">{{ $agentCounts->sms_unverified }}</span>
            @endif
        </a>

    </div>
</div>
