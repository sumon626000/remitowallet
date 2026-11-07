
<section class="services">
    <div class="container">
        <div class="common-title-container">
            <div class="common-title">
                <h6>@lang(@$service['single']['heading']) </h6>
                <h3>@lang(@$service['single']['sub_heading'])</h3>
            </div>
            <p>{!! __(@$service['single']['description']) !!}</p>
        </div>
    </div>
    <div class="services-container">
        <div class="services-carousol">
            <div class="four-item-carousel swiper-container services-carousol-container dynamic-swiper" data-items="4">
                <div class="swiper-wrapper">
                    @forelse(@$service['multiple']  ?? [] as $item)
                        <div class="swiper-slide">
                            <div class="services-single">
                                <div class="services-single-content">
                                    <div class="services-icon">
                                        <i class="{{ @$item['media']->icon }}"></i>
                                    </div>
                                    <a href="{{ @$item['media']->my_link }}">@lang(@$item['title'])</a>
                                    <p>@lang(@$item['sub_title'])</p>
                                </div>
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
            <div class="services-navigation">
                <div class="swiper-button-next"><i class="fa-regular fa-angle-right"></i></div>
                <div class="swiper-button-prev"><i class="fa-regular fa-angle-left"></i></div>
            </div>
        </div>
    </div>


</section>

