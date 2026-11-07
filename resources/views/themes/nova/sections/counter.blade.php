
<section class="wallet">
    <div class="container">
        <div class="common-title">
            <h6>@lang(@$counter['single']['heading'])</h6>
            <h3>@lang(@$counter['single']['sub_heading'])</h3>
            <p>{!! __(@$counter['single']['description']) !!}</p>
        </div>
        <div class="row">
            <div class="col-lg-3 col-md-6">
                <div class="wallet-single">
                    <div class="counter">
                        <div class="counter-icon">
                            <img src="{{ template(true).'/images/icons/counter-1.jpg' }}" alt="icon">
                        </div>
                        <div class="odometer-box">
                            <h5 class="odometer" data-count="{{ @$counter['mediaFile']->total_users }}">00</h5>
                            <div class="odometer-text"><i class="fa-regular fa-plus"></i></div>
                        </div>
                        <h6>@lang('Total Users')</h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="wallet-single">
                    <div class="counter">
                        <div class="counter-icon">
                            <img src="{{ template(true).'/images/icons/counter-2.jpg' }}" alt="icon">
                        </div>
                        <div class="odometer-box">
                            <h5 class="odometer" data-count="{{ @$counter['mediaFile']->global_office }}">00</h5>
                            <div class="odometer-text"><i class="fa-regular fa-plus"></i></div>
                        </div>
                        <h6>@lang('Global Office')</h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="wallet-single">
                    <div class="counter">
                        <div class="counter-icon">
                            <img src="{{ template(true).'/images/icons/counter-3.jpg' }}" alt="icon">
                        </div>
                        <div class="odometer-box">
                            <div class="odometer-text">{{ basicControl()->currency_symbol }}</div>
                            <h5 class="odometer" data-count="{{ @$counter['mediaFile']->total_wallet }}">00</h5>
                            <div class="odometer-text">+</div>
                        </div>
                        <h6>@lang('Total Wallet Balance')</h6>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="wallet-single">
                    <div class="counter">
                        <div class="counter-icon">
                            <img src="{{ template(true).'/images/icons/counter-4.jpg' }}" alt="icon">
                        </div>
                        <div class="odometer-box">
                            <h5 class="odometer" data-count="{{ @$counter['mediaFile']->total_gateways }}">00</h5>
                            <div class="odometer-text"><i class="fa-regular fa-plus"></i></div>
                        </div>
                        <h6>@lang('Gateways')</h6>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
