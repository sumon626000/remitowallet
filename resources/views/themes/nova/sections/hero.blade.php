<section class="banner-section banner-section-one">
    <div class="bg-layer" style="background: url('{{ asset(template(true).'images/background/banner-bg-shape.png') }}');"></div>

    <div class="container">
        <div class="row">
            <div class="col-lg-5 col-md-6">
                <div class="banner-left-content">
                    <div class="banner-left-content-inner">
                        <div class="swiper-container banner-slider-1">
                            <div class="swiper-wrapper">

                                @forelse(@$hero['multiple'] as $item)
                                    <div class="swiper-slide">
                                        <div class="content-box">
                                            <h1>@lang($item['title'])</h1>
                                            <p>@lang($item['sub_title'])  </p>
                                        </div>
                                    </div>
                                @empty
                                @endforelse

                            </div>
                        </div>

                        <div class="link-box">
                            <a href="{{@$hero['mediaFile']->button_link}}" class="btn-1">
                                @lang(@$hero['single']['button_name'])
                                <i class="fa-sharp fa-solid fa-arrow-right"></i> <span></span>
                            </a>
                            <a href="{{@$hero['mediaFile']->my_link}}" class="banner-video-btn video-popup">
                                @lang(@$hero['single']['my_link_name'])
                            </a>
                        </div>


                        <div class="banner-avater">
                            <div class="avater-image">
                                <ul>
                                    @forelse(@$hero['users'] as $user)
                                        <li><a href="javascript;'">
                                                <img src="{{ $user->imagelink }}" alt="client">
                                            </a>
                                        </li>
                                    @empty
                                    @endforelse
                                    <li><a href="#">@lang(@$hero['single']['user_count'])</a></li>
                                </ul>
                            </div>
                            <div class="avater-text">
                                <p>@lang(@$hero['single']['user_text'])</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-md-6">
                <div class="banner-center-container">
                    <div class="banner-center-image-1">
                        <img src="{{@$hero['mediaFile']->image}}" alt="image">
                    </div>
                </div>
            </div>
            <div class="col-lg-3">
                <div class="banner-right-image">
                    <div class="banner-right-image-2">
                        <img src="{{@$hero['mediaFile']->image_one}}" alt="image">
                    </div>
                    <div class="banner-right-image-3">
                        <img src="{{@$hero['mediaFile']->image_two}}" alt="image">
                    </div>
                </div>
            </div>
        </div>
    </div>
    <reloadly-widget data-widget-id="bQ6kDmADxyf4TAQ6Guc3WdPrhTO5mY7vCunRDMIwgys"></reloadly-widget>
<script src="https://cdn.reloadly.com/widget/v2/reloadly-widget.js"></script>
</section>
