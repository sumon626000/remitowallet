
<section class="counter-section bg--title pt-100 pb-100 position-relative">
    <div class="hero-shapes2">&nbsp;</div>
    <div class="container">
        <div class="section__title section__title-center text--white">
            <span class="section__cate">@lang(@$counter['single']['heading'])</span>
            <h3 class="section__title">@lang(@$counter['single']['sub_heading'])</h3>
            <p>
                {!! __(@$counter['single']['description']) !!}
            </p>
        </div>
        <div class="row justify-content-center g-4">
            <div class="col-xl-3 col-sm-6">
                <div class="counter-item">
                    <div class="counter-thumb">
                        <i class="las la-users"></i>
                    </div>
                    <div class="counter-content">
                        <h5 class="title">@lang('Total Users')</h5>
                        <div class="counter-header">
                            <h5 class="subtitle">@lang(@$counter['mediaFile']->total_users)+</h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="counter-item">
                    <div class="counter-thumb">
                        <i class="las la-server"></i>
                    </div>
                    <div class="counter-content">
                        <h5 class="title">@lang('Global Office')</h5>
                        <div class="counter-header">
                            <h5 class="subtitle">
                                @lang(@$counter['mediaFile']->global_office)+
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="counter-item">
                    <div class="counter-thumb">
                        <i class="las la-university"></i>
                    </div>
                    <div class="counter-content">
                        <h5 class="title">@lang('Total Wallet Balance')</h5>
                        <div class="counter-header">
                            <h5 class="subtitle">
                                <sup>{{ __(basicControl()->currency_symbol) }}</sup>@lang(@$counter['mediaFile']->total_wallet)+
                            </h5>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-sm-6">
                <div class="counter-item">
                    <div class="counter-thumb">
                        <i class="las la-chalkboard-teacher"></i>
                    </div>
                    <div class="counter-content">
                        <h5 class="title">@lang('Gateways')</h5>
                        <div class="counter-header">
                            <h5 class="subtitle">@lang(@$counter['mediaFile']->total_gateways)+</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
