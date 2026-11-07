
<div class="row row-cols-1 row-cols-md-2 record">
    @if($basic->transfer)
        <div class="col-md-12">
            <h5 class="mb-3">@lang('User Transfer Summary')</h5>
        </div>
        <x-stat-card class="col-md-6" :amount=" getAmount($transfer['total_transfer'], 2)"
                     text="Total transfer" pgColor="bg-warning"/>
        <x-stat-card class="col-md-6" :amount=" getAmount($transfer['total_profit'], 2)"
                     text="Total profit" pgColor="bg-warning"/>
        <x-stat-card class="col-md-6" :amount=" getAmount($transfer['transfer_7_days'], 2)" text="Last 7 days transfer" pgColor="bg-warning"/>
        <x-stat-card class="col-md-6" icon="bi bi-cash-stack"
                     :amount=" getAmount($transfer['profit_7_days'], 2)" text="Last 7 days profit" pgColor="bg-warning"/>

    @endif

    <div class="col-md-12">
        <h5 class="mb-3">@lang('User Redeem Summary')</h5>
    </div>
    <x-stat-card class="col-md-6" icon="bi bi-receipt" :amount=" getAmount($redeemCode['total_redeem'], 2)"
                 text="Total redeem amount" pgColor="bg-primary"/>
    <x-stat-card class="col-md-6" icon="bi bi-cash-stack"
                 :amount=" getAmount($redeemCode['total_redeem_profit'], 2)" text="Total redeem profit"
                 pgColor="bg-primary"/>

</div>


