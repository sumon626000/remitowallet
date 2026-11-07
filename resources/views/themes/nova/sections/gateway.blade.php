
<section class="payment">
    <div class="container">
        <div class="row">
            <div class="col-lg-5">
                <div class="payment-left-container">
                    <div class="common-title">
                        <h6>@lang(@$gateway['single']['heading'])</h6>
                        <h3>@lang(@$gateway['single']['sub_heading'])</h3>
                        <p>{!! __(@$gateway['single']['description']) !!}</p>
                    </div>
                    <a href="{{ $gateway['mediaFile']->button_link }}" class="btn-1">@lang($gateway['single']['button_name'])
                        <i class="fa-light fa-arrow-right-long"></i> <span></span></a>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="payment-right-container">
                    <div class="payment-girl">
                        <img src="{{ @$gateway['mediaFile']->image }}" alt="image">
                    </div>
                    <div class="protect-method-box">
                        <img src="{{ @$gateway['mediaFile']->image_one }}" alt="icon">
                        <h6>@lang(@$gateway['single']['title'])</h6>
                    </div>
                    <div class="payment-method-container">
                        <div class="payment-method-content">
                            <div class="payment-method-title">
                                <div class="payment-method-icon">
                                    <img src="{{ @$gateway['mediaFile']->image_two }}" alt="icon">
                                </div>
                                <div class="payment-method-info">
                                    <h4>@lang(@$gateway['single']['title_two'])</h4>
                                    <p>@lang(@$gateway['single']['sub_title'])</p>
                                </div>
                            </div>

                            <div class="payment-method-slider">
                                <div class="three-item-carousel swiper-container " data-items="3">
                                    <div class="swiper-wrapper">
                                        @foreach(@$gateway['multiple'] ?? [] as $index => $gatewayImage)
                                            @if($index % 2 == 0)
                                                <div class="swiper-slide">
                                                    @endif

                                                    <div class="payment-card">
                                                        <img src="{{ $gatewayImage }}" alt="card">
                                                    </div>

                                                    @if($index % 2 == 1 || $loop->last)
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                                <div class="swiper-pagination"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
