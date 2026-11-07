@extends(template() . 'layouts.app')
@section('title',trans('Blog Details'))
@section('content')
    <!-- ======= Blog Single Section ======= -->

    <section class="blog-details">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="blog-details-left-container">
                        <div class="blog-details-top-image">
                            <div class="image">
                                <img src="{{ getFile(optional($blogDetails->blog)->blog_image_driver, optional($blogDetails->blog)->blog_image) }}" alt="image">
                            </div>
                            <div class="blog-details-image-info">
                                <ul>
                                    <li><i class="fa-light fa-user"></i> @lang('By Admin')</li>
                                    <li><i class="fa-light fa-calendar-days"></i> {{ dateTime($blogDetails->created_at) }}</li>
                                </ul>
                            </div>
                        </div>
                        <div class="blog-details-content">
                            <h3>@lang($blogDetails->title)</h3>
                            <p>
                                {!! __($blogDetails->description) !!}
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="blog-details-right-container">
                        <div class="sidebar">
                            <div class="sidebar-content">
                                <div class="sidebar-search">
                                    <form action="{{ route('blog.search') }}" method="get">
                                        <input type="search" name="search" class="search" placeholder="Search...">
                                        <div class="search-btn">
                                            <button type="submit"><i class="fa-light fa-magnifying-glass"></i></button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div class="sidebar-content">
                                <div class="sidebar-category">
                                    <h5>@lang('Category')</h5>
                                    <div class="sidebar-category-list">
                                        <ul>
                                            @forelse($categories as $item)
                                                <li>
                                                    <a href="{{ route('blog.category', [slug(optional($item)->name), $item->id]) }}">
                                                        <span>{{ $item->name }}</span><i class="fa-regular fa-angle-right"></i></a>
                                                </li>
                                            @empty
                                            @endforelse
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="sidebar-content">
                                <div class="sidebar-recent-post">
                                    <h5>@lang('Recent Blogs')</h5>
                                    @foreach($latestBlogs as $item)
                                        <div class="recent-post-list">
                                            <div class="recent-post-image">
                                                <img src="{{ getFile( $item->blog_image_driver,$item->blog_image ) }}" alt="image">
                                            </div>
                                            <div class="recent-post-info">
                                                <a href="{{ $item->detailsLink() }}">{{ __($item->titleLimit(65)) }}</a>
                                                <p><i class="fa-light fa-calendar-days"></i> {{ dateTime($item->created_at) }}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

@endsection
