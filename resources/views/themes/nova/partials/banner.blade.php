@if(!request() ->routeIs( 'login','register','password.request','password.reset', ))

    <!-- Banner section start -->
    <section class="common-banner">
        @if(@$pageSeo['breadcrumb_image'])
            <div class="bg-layer" style="background: url({{ $pageSeo['breadcrumb_image'] }});"></div>
        @else
            <div class="bg-layer"
                 style="background: url('{{ asset(template(true) . 'images/background/common-banner-bg.jpg') }}');">
            </div>
        @endif

        <div class="container">
            <div class="common-banner-container">
                <div class="common-banner-title">
                    <h1>
                        @if(isset($pageSeo['page_title']))
                            @lang($pageSeo['page_title'])
                        @else
                            @yield('title')
                        @endif
                    </h1>
                </div>
                <div class="breadcrumb">
                    <a href="{{ url('/') }}">
                        <i class="fa-light fa-house"></i> @lang('Home')
                    </a>
                    <i class="fa-regular fa-angle-right"></i>
                    <span>
                    @if(isset($pageSeo['page_title']))
                            @lang($pageSeo['page_title'])
                        @else
                            @yield('title')
                        @endif
                </span>
                </div>
            </div>
        </div>
    </section>
    <!-- Banner section end -->

@endif
