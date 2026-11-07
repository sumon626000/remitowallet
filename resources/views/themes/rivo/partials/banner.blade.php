
@if(!request() ->routeIs(
        'login','register','password.request','password.reset',
))

<!-- Banner section start -->


<section class="hero-section bg--title">

    @if(@$pageSeo['breadcrumb_image'])
    <div class="hero-shapes2"
         style="background:url({{ $pageSeo['breadcrumb_image'] }}) no-repeat center center/cover;">
        &nbsp;
    </div>
    @else
        <div class="hero-shapes2"
             style="background:url({{ asset(template(true).'images/breadcrumb.png') }}) no-repeat center center/cover;">
            &nbsp;
        </div>
    @endif

    <div class="container">
        <div class="hero-breadcrumb">
            <h2 class="title">
                @if(isset($pageSeo['page_title']))
                    @lang($pageSeo['page_title'])
                @else
                    @yield('title')
                @endif
            </h2>
            <ul class="breadcrumb">
                <li>
                    <a href="{{ url('/') }}">
                        <i class="fa-light fa-house"></i>@lang('Home')
                    </a>
                </li>
                <li>
                    @if(isset($pageSeo['page_title']))
                        @lang($pageSeo['page_title'])
                    @else
                        @yield('title')
                    @endif
                </li>
            </ul>
        </div>
    </div>
</section>

<!-- Banner section end -->

@endif
