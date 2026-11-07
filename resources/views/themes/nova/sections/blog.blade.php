

<section class="blog">
    <div class="container">
        <div class="row">
            @forelse(@$blog['multiple'] ?? [] as $item)
                <div class="col-lg-4 col-md-6">
                    <div class="blog-single">
                        <div class="blog-single-image">
                            <img src="{{ getFile($item->blog_image_driver, $item->blog_image) }}" alt="image">
                        </div>
                        <div class="blog-single-content">
                            <div class="blog-meta">
                                <div class="blog-meta-badge">
                                    <span>{{ $item->category?->name }}</span>
                                </div>
                                <div class="blog-meta-date">
                                    <p><i class="fa-light fa-calendar-days"></i> {{ $item->created_at }}</p>
                                </div>
                            </div>
                            <a href="{{ $item->detailsLink() }}" class="blog-single-title">
                                {{ __($item->titleLimit(50)) }}
                            </a>
                            <p>{!! __($item->descriptionLimit(90)) !!} </p>
                            <a href="{{ $item->detailsLink() }}" class="more-btn">@lang('Details') <i class="fa-light fa-arrow-right-long"></i></a>
                        </div>
                    </div>
                </div>
            @empty
            @endforelse
        </div>
    </div>
</section>

