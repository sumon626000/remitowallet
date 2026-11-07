<section class="how-to">
    <div class="container">
        <div class="common-title text-center">
            <h6>@lang(@$how_it_works['single']['heading'])</h6>
            <h3>@lang(@$how_it_works['single']['sub_heading'])</h3>
            <p>
                {!! __(@$how_it_works['single']['description']) !!}
            </p>
        </div>
        <div class="row">
            @forelse(@$how_it_works['multiple'] as $key=>$item)
                <div class="col-lg-4 col-md-6">
                    <div class="how-to-single">
                        <div class="how-to-single-inner">
                            <div class="how-to-single-icon-box">
                                <div class="how-to-single-icon">
                                    <img src="{{ @getFile($item['media']->image->driver,$item['media']->image->path) }}" alt="icon">
                                </div>
                                <h5>0{{ $key+1 }}</h5>
                            </div>
                            <div class="how-to-single-content">
                                <h5>@lang($item['title'])</h5>
                                <p>@lang($item['sub_title'])</p>
                                <a href="{{ @$item['media']->button_link }}" class="more-btn">
                                    @lang($item['button_name']) <i class="fa-light fa-arrow-right-long"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
    </div>
</section>

