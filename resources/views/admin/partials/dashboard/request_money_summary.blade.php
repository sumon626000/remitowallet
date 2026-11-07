

<div class="row row-cols-1 row-cols-md-2 record">
    @if($basic->request)
        <div class="col-md-12">
            <h5 class="mb-3">@lang('User Request Money Summary')</h5>
        </div>

        <x-stat-card class="col-md-6" icon="bi bi-cash-coin"
                     :amount=" getAmount($requestMoney['total_request_money'] ?? 0, 2)" text="Total request money"
                     pgColor="bg-info"/>
        <x-stat-card class="col-md-6" icon="bi bi-cash-coin"
                     :amount=" getAmount($requestMoney['total_profit'] ?? 0, 2)" text="Total request money profit"
                     pgColor="bg-info"/>
        <x-stat-card class="col-md-6" icon="bi bi-cash-coin"
                     :amount=" getAmount($requestMoney['request_money_7_days'] ?? 0, 2)" text="Last 7 days request money"
                     pgColor="bg-info"/>
        <x-stat-card class="col-md-6" icon="bi bi-cash-stack"
                     :amount=" getAmount($requestMoney['profit_7_days'] ?? 0, 2)" text="Last 7 days request money profit"
                     pgColor="bg-info"/>
    @endif

    @if($basic->voucher)
        <div class="col-md-12">
            <h5 class="mb-3">@lang('User Voucher Summary')</h5>
        </div>
        <x-stat-card class="col-md-6" icon="bi bi-ticket-perforated" :amount="getAmount($voucher['voucher_30_days'], 2)"
                     text="Last 30 Days Voucher Payment" pgColor="bg-primary"/>
        <x-stat-card class="col-md-6" icon="bi bi-cash-stack" :amount="getAmount($voucher['voucher_income_30_days'], 2)"
                     text="Last 30 days Income" pgColor="bg-primary"/>
    @endif

</div>

