

<section class="blog-section pt-50 pb-100">
    <div class="container">
        <div class="section__title section__title-center">
            <span class="section__cate">@lang(@$blog['single']['heading'])</span>
            <h3 class="section__title">@lang(@$blog['single']['sub_heading'])</h3>
            <p>
                {!! __(@$blog['single']['description']) !!}
            </p>
        </div>
        <div class="row g-4 justify-content-center">

            @forelse($blog['multiple'] as $item)
                <div class="col-lg-4 col-md-6 col-sm-10">
                    <div class="post-item">
                        <div class="post-inner">
                            <div class="post-img">
                                <a href="{{ $item->detailsLink() }}">
                                    <img src="{{ getFile($item->blog_image_driver, $item->blog_image) }}" alt="...">
                                </a>
                            </div>
                            <div class="post-content text-start">
                                <h6 class="title mb-2">
                                    <a href="{{ $item->detailsLink() }}">
                                        {{ __($item->titleLimit(65)) }}
                                    </a>
                                </h6>
                                <a href="{{ $item->detailsLink() }}" class="text--base">
                                    @lang('Read More')
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse

        </div>
    </div>
</section>
