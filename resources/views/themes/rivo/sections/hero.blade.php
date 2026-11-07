
<section class="hero-section bg--section">
    <div class="hero-shapes bg_img d-none d-md-block"
         data-img="{{ asset(template(true).'images/banner-shapes.png') }}">
    </div>
    <div class="hero-shapes2"
         style="background: url({{asset(template(true).'images/banner-shape-2.png')}}) no-repeat center center/cover;">
        &nbsp;
    </div>
    <div class="container">
        <div class="hero-slider owl-theme owl-carousel">
            @if(isset($hero['multiple']))
                @forelse($hero['multiple'] as $item)
                    <div class="hero-item">
                        <div class="hero-cont">
                            <h1 class="title">@lang(@$item['heading'])</h1>
                            <p class="banner-txt">
                                @lang(@$item['sub_heading'])
                            </p>

                            <a href="{{ @$item['media']->button_link }}" class="cmn--btn">@lang(@$item['button_name'])</a>
                        </div>
                        <div class="hero-img">
                            <img src="{{ @getFile($item['media']->image->driver,$item['media']->image->path) }}" alt="image">
                        </div>
                    </div>
                @empty
                @endforelse
            @endif
        </div>
    </div>
    <div class="container owl--dots">
        <div class="hero-dots owl-dots"></div>
    </div>
    <div class="scrollNext">&nbsp;</div>

</section>
