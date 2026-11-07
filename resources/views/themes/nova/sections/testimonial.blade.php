<section class="testimonial">
    <div class="container">
        <div class="row">
            <div class="col-lg-3">
                <div class="testimonial-left-image-container">
                    @foreach((@$testimonial['multiple'] ?? []) as $key => $item)
                        @if($key < 3)
                            <img src="{{ @getFile($item['media']->image->driver, $item['media']->image->path) }}" alt="image">
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="col-lg-6">
                <div class="testimonial-slider">
                    <div class="single-item-carousel swiper-container">
                        <div class="swiper-wrapper">
                            @foreach(@$testimonial['multiple'] ?? [] as $item)
                                <div class="swiper-slide">
                                    <div class="testimonial-slider-content">
                                        <div class="icon">
                                            <img src="{{ asset(template(true).'/images/icons/quote.png') }}" alt="icon">
                                        </div>
                                        <h3>@lang(@$item['title'])</h3>
                                        <p>{!! __(@$item['feedback']) !!}</p>
                                        <div class="customer-info">
                                            <img src="{{ @getFile($item['media']->image->driver, $item['media']->image->path) }}" alt="customer">
                                            <h6>@lang(@$item['name'])</h6>
                                            <p>@lang(@$item['designation'])</p>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-3">
                <div class="testimonial-right-image-container">
                    @foreach(($testimonial['multiple'] ?? []) as $key => $item)
                        @if($key >= count($testimonial['multiple']) - 3)
                            <img src="{{ getFile($item['media']->image->driver, $item['media']->image->path) }}" alt="image">
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
