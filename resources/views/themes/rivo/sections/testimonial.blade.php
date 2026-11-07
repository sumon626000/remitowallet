

<section class="testimonial-section pt-50 pb-50">
    <div class="container">
        <div class="section__title section__title-center">
            <span class="section__cate">@lang(@$testimonial['single']['heading'])</span>
            <h3 class="section__title">@lang(@$testimonial['single']['sub_heading'])</h3>
        </div>

        <div class="client-slider owl-theme owl-carousel">
            @foreach($testimonial['multiple'] ?? [] as $item)
                <div class="client-item">
                    <img src="{{ getFile($item['media']->image->driver,$item['media']->image->path) }}" alt="...">
                    <p>
                        {!! __(@$item['feedback']) !!}
                    </p>
                    <h6 class="name">@lang(@$item['name'])</h6>
                    <span class="info">@lang(@$item['title'])</span>
                    <div class="icon center-div">
                        <i class="fas fa-quote-right vertical-center"></i>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</section>
