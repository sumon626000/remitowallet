@extends(userLayout())

@section('title',trans('Dashboard'))
@section('content')

    <div class="content {{ containerClass() }}">

        <div class="page-header ">
            <div class="row align-items-center">
                <div class="col"><h1 class="page-header-title">@lang('Dashboard')</h1></div>
            </div>
        </div>
        @if($firebaseNotify)
            @include('user.partials.dashboard.notification')
        @endif
        @include('user.partials.dashboard.wallets_others')

        @include('user.partials.dashboard.extra_summary')

        @if($basic->transfer)
            @include('user.partials.dashboard.latest_transfers')
        @endif

    </div>

@endsection


@push('js-lib')
    {{-- Owl Carousel for Dashboard Card --}}
    <script src="{{ asset('assets/global/js/owl.carousel.min.js') }}"></script>
    <script>
        $(document).ready(function () {
            const owl = $(".card-carousel").owlCarousel({
                loop: false,
                nav: false,
                dots: false,
                margin: -40,
                responsive: {
                    0: {items: 1},
                    600: {items: 1},
                    992: {items: 1, margin: -100},
                    1400: {items: 2},
                    1600: {items: 2, margin: -100}
                }
            });

            $(document).on("click", ".owl-prev", function () {
                owl.trigger("prev.owl.carousel");
            });
            $(document).on("click", ".owl-next", function () {
                owl.trigger("next.owl.carousel");
            });
        });
    </script>
@endpush



@push('js-lib')
    <script src="{{ asset('assets/themes/nova/js/swiper.min.js') }}"></script>

    <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>


    <script src="{{ asset('assets/user/js/config.js') }}"></script>
    <script src="{{ asset('assets/global/js/apexcharts.min.js') }}"></script>
    <script src="{{ asset('assets/user/js/dashboard.js') }}"></script>

@endpush

<x-assets :tomselect="false" :flatpickr="false" :daterange="true" />
