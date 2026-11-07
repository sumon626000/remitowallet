

@if($basic->redeem || $basic->voucher)

    <div class="row row-cols-1 row-cols-md-2 record">
        <div class="col-md-12">
            <h5 class="mb-3">@lang('User Redeem & Voucher Summary')</h5>
        </div>

        <x-stat-card icon="bi bi-receipt" :amount=" getAmount($redeemCode['redeemCode_30_days'], 2)" text="Last 30 Days Redeem Money" pgColor="bg-primary"/>
        <x-stat-card icon="bi bi-cash-stack" :amount=" getAmount($redeemCode['redeemCode_income_30_days'], 2)" text="Last 30 days Income" pgColor="bg-primary"/>

        <x-stat-card icon="bi bi-ticket-perforated" :amount=" getAmount($voucher['voucher_30_days'], 2)" text="Last 30 Days Voucher Payment" pgColor="bg-primary"/>
        <x-stat-card icon="bi bi-cash-stack" :amount=" getAmount($voucher['voucher_income_30_days'], 2)" text="Last 30 days Income" pgColor="bg-primary"/>
    </div>

@endif
