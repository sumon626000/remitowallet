@extends(template() . 'layouts.app')
@section('title',trans('Blog Details'))
@section('content')
    <!-- ======= Blog Single Section ======= -->

    <section class="blog-section pt-100 pb-100">
        <div class="container">
            <div class="row justify-content-between gy-5">
                <div class="col-lg-8">
                    <div class="post-details">
                        <img class="w-100" alt="..."
                            src="{{ getFile(optional($blogDetails->blog)->blog_image_driver, optional($blogDetails->blog)->blog_image) }}">
                        <div class="d-flex flex-wrrap post-info">
                            <div>
                                <i class="fas fa-calendar-alt"></i> {{ dateTime($blogDetails->created_at) }}
                            </div>
                        </div>
                        <h4 class="title mt-2">@lang($blogDetails->title)</h4>
                        <div class="content">
                            <p>{!! __($blogDetails->description) !!}</p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="ps-xxl-5">
                        <div class="sidebar">
                            <div class="sidebar-item">
                                <h3 class="sidebar-title">@lang('Popular Posts')</h3>

                                <ul class="sidebar-posts">
                                    @foreach($latestBlogs as $item)
                                        <li>
                                            <div class="image">
                                                <a href="{{ $item->detailsLink() }}">
                                                    <img src="{{ getFile( $item->blog_image_driver,$item->blog_image ) }}" alt="...">
                                                </a>
                                            </div>
                                            <div class="content">
                                                <a href="{{ $item->detailsLink() }}">
                                                    {{ __($item->titleLimit(65)) }}
                                                </a>
                                                <span>{{ dateTime($item->created_at) }}</span>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul><!-- sidebar-posts -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


@endsection
