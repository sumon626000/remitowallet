@extends(template() . 'layouts.app')
@section('title',trans('Blogs'))
@section('content')

    <section class="blog-section pt-100 pb-100">
        <div class="container">
            <div class="row g-4">
                @if($blogs)
                    @foreach($blogs ?? [] as $item)
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-10">
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
                    @endforeach
                @endif
            </div>
        </div>
    </section>


@endsection

