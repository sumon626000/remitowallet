@extends(template() . 'layouts.app')
@section('title',trans('Blogs'))
@section('content')

    <section class="blog">
        <div class="container">
            <div class="row">
                @if($blogs)
                    @forelse($blogs as $item)
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
                                            <p><i class="fa-light fa-calendar-days"></i> {{ dateTime($item->created_at) }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ $item->detailsLink() }}" class="blog-single-title">
                                        {{ __($item->titleLimit(50)) }}
                                    </a>
                                    <p> {!! __($item->descriptionLimit(100)) !!} </p>
                                    <a href="{{ $item->detailsLink() }}" class="more-btn">
                                        @lang('Details') <i class="fa-light fa-arrow-right-long"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center fw-bold">
                            @lang('No Data Found')
                        </div>
                    @endforelse
                        {{ $blogs->appends($_GET)->links(template().'partials.pagination') }}
                @endif
            </div>
        </div>
    </section>

@endsection

