@if($basic->make_payment)
    <div class="nav-item">
        <a class="nav-link {{menuActive(['user.make_payment.*'])}}"
           href="{{route('user.make_payment.create')}}" data-placement="left">
            <i class="bi-credit-card-2-front nav-icon"></i>
            <span class="nav-link-title">@lang('Make Payment')</span>
            <small class="d-none">@lang("Services > Make Payment")</small>
        </a>
    </div>
@endif
