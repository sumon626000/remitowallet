

<section class="how-to-section bg--title pt-100">
    <div class="container">
        <div class="section__title section__title-center text--white">
            <span class="section__cate">@lang(@$how_it_works['single']['heading'])</span>
            <h3 class="section__title">@lang(@$how_it_works['single']['sub_heading'])</h3>
            <p>
                {!! __(@$how_it_works['single']['description']) !!}
            </p>
        </div>

        <div class="row gy-4">
            <div class="col-lg-6 align-self-center py-lg-5">
                <div class="process-wrapper">
                    @forelse(@$how_it_works['multiple'] ?? [] as $item)
                        <div class="process-block-main">
                            <div class="icon center-div">
                                <i class="las la-user vertical-center"></i>
                            </div>
                            <div class="cont">
                                <h5 class="title">@lang(@$item['title'])</h5>
                                <p>
                                    {!! __($item['sub_title']) !!}
                                </p>
                            </div>
                        </div>
                    @empty
                    @endforelse
                </div>
            </div>
            <div class="col-lg-6 align-self-end">
                <div class="how-thumb">
                    <img src="{{ @getFile($how_it_works['single']['media']->image->driver,$how_it_works['single']['media']->image->path) }}"
                        alt="...">
                </div>
            </div>
        </div>
    </div>
</section>
