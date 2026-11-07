
@if($basic->make_payment)
    <li class="nav-item">
        <a class="nav-link" href="{{ route('user.make_payment.create') }}">
            <i class="bi-credit-card-2-front"></i> @lang('Make Payment')
        </a>
    </li>
@endif
