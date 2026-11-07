
@props(['basic'])

@php
    $services = [
        'payout'
    ];
    $allActions = [
        'payout'        => ['route' => 'merchant.payout', 'icon' => 'bi-cash-coin', 'label' => __('Payout')],

        'transaction'   => ['route' => 'merchant.transaction', 'icon' => 'bi-arrow-left-right', 'label' => __('Transaction')],
        'kyc'           => ['route' => 'merchant.verification.kyc', 'icon' => 'bi-person-check', 'label' => __('Verification')],
        '2step'         => ['route' => 'merchant.twostep.security', 'icon' => 'bi-shield-check', 'label' => __('2Step Security')],
        'pin_manage'    => ['route' => 'merchant.securityPin.manage', 'icon' => 'bi-key', 'label' => __('Security Pin')],
        'notification'  => ['route' => 'merchant.notifications', 'icon' => 'bi-bell', 'label' => __('Notification')],
        'ticket'        => ['route' => 'merchant.ticket.list', 'icon' => 'bi-headset', 'label' => __('Support')],
        'profile'       => ['route' => 'merchant.profile', 'icon' => 'bi-person', 'label' => __('Profile')],
    ];

    $enabled = collect($allActions)->filter(function ($_, $key) use ($services, $basic) {
        return in_array($key, $services) && data_get($basic, $key) == 1;
    });
    $fallback = collect($allActions)->reject(function ($_, $key) use ($services) {
        return in_array($key, $services);
    });
    $actions = $enabled->concat($fallback)->values();
@endphp

<div class="row d-flex flex-column flex-lg-row">

    <!-- Mobile View with Swiper -->
    <div class="col-lg-6 mb-3 mb-lg-5 d-lg-none order-1 order-lg-2">
        <div class="h-100">
            <div class="card-header card-header-content-between">
                <h4 class="card-header-title">@lang('Quick Links')</h4>
            </div>
            <div class="card-body px-0">
                <div class="swiper-container quick-links-swiper ">
                    <div class="swiper-wrapper">
                        @foreach ($actions->take(3) ?? [] as $action)
                            <div class="swiper-slide" style="width: auto;">
                                <div class="p-0 mx-1 text-center">
                                    <a href="{{ route($action['route']) }}" target="_blank"
                                       class="text-center py-3">
                                    <span class="icon icon-lg icon-circle icon-soft-secondary">
                                        <i class="{{ $action['icon'] }}"></i>
                                    </span>
                                        <h6 class="card-title mt-2 mt-lg-3 small">{{ $action['label'] }}</h6>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card-body px-0 mt-5 d-none d-lg-block">
                <div class="swiper-container quick-links-swiper ">
                    <div class="swiper-wrapper">
                        @foreach ($actions->reverse()->take(8) ?? [] as $action)
                            <div class="swiper-slide" style="width: auto;">
                                <div class="p-0 text-center">
                                    <a href="{{ route($action['route']) }}" target="_blank" class="text-center py-3">
                                        <span class="icon icon-lg icon-circle icon-soft-dark">
                                            <i class="{{ $action['icon'] }}"></i>
                                        </span>
                                        <h6 class="card-title mt-2 mt-lg-3 small">{{ $action['label'] }}</h6>
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="col-lg-6 mb-3 mb-lg-5 order-2 order-lg-1">
        <div class="row  g-1">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="card-header-title ">@lang('My Wallets')</h4>
                <div class="">
                    <button class="btn btn-sm btn-white btn-icon owl-prev"><i class="bi-chevron-left"></i></button>
                    <button class="btn btn-sm btn-white btn-icon owl-next"><i class="bi-chevron-right"></i></button>
                </div>
            </div>
            @if($wallets->isNotEmpty())
                <div class="owl-carousel owl-theme card-carousel ">
                    @foreach($wallets as $wallet)
                        <div class="card wallet-card bg-gradient-primary text-white rounded-4 shadow-sm" style="max-width: 25rem;">
                            <div class="card-body">
                                <div class="d-flex align-items-center justify-content-between mb-5">
                                    <div>
                                        <small class="text-white">{{ __(@$wallet->currency?->name) }}</small>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="wallet-balance mt-4">
                                        <h2 class="fw-bold mb-0">{{ $wallet->currency?->symbol }}{{ fractionNumber(getAmount(@$wallet->totalBalance)) }}</h2>
                                        <span class="small text-white">@lang('Available Balance')</span>
                                    </div>
                                    <div class="wallet-logo mt-4">
                                        <img class="avatar avatar-sm rounded-circle shadow-lg"
                                             src="{{ getFile( $wallet->currency?->driver, $wallet->currency?->logo ) }}"
                                             alt="{{ __(@$wallet->currency?->code) }}" />
                                    </div>
                                </div>

                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center p-4">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                    <p class="mb-0">@lang("No wallet found")</p>
                </div>
            @endif
        </div>
    </div>


    <!--table-link none-->
    <div class="col-lg-6 mb-3 mb-lg-5 order-1 order-lg-2 d-none d-lg-block">
        <div class="card h-100">
            <div class="card-header card-header-content-between">
                <h4 class="card-header-title">@lang('Quick Links')</h4>
            </div>
            <div class="card-body quick-link-height p-3">

                <ul class="list-group list-group-flush list-group-no-gutters">

                    @foreach ($actions->take(3) ?? [] as $action)
                        <li class="list-group-item p-2 border-0">
                            <div class="d-flex">
                                <div class="icon icon-sm icon-soft-info icon-circle">
                                    <i class="{{ $action['icon'] }}"></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h5 class="mb-1">{{ $action['label'] }}</h5>
                                            <span class="d-block fs-6 text-body">
                                                @lang('Get started with :label in just a tap.', ['label' => $action['label']])
                                            </span>
                                        </div>
                                        <div class="col-auto">
                                            <a class="btn btn-xs btn-soft-secondary link" href="{{ route($action['route']) }}" target="_blank">
                                                {{ $action['label'] }}
                                                <i class="bi-box-arrow-up-right ms-1"></i>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>


    <!--card-link none-->
    <div class="col-lg-6 mb-3 mb-lg-5 d-none  order-1 order-lg-2">
        <div class=" h-100">
            <div class="card-header card-header-content-between">
                <h4 class="card-header-title">@lang('Quick Links')</h4>
            </div>
            <div class="card-body ">

                <div class="row justify-content-start align-items-center g-3">
                    @foreach ($actions->take(8) ?? [] as $action)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="card card-hover-shadow p-0">
                                <a href="{{ route($action['route']) }}" target="_blank"
                                   class="card-body text-center px-0 py-3">
                                    <span class="icon icon-sm icon-circle icon-success ">
                                        <i class="{{ $action['icon'] }} "></i>
                                    </span>
                                    <h6 class="card-title mt-2">{{ $action['label'] }}</h6>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

            </div>
        </div>
    </div>

</div>


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/owl.carousel.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/themes/nova/css/swiper.min.css') }}">
@endpush


@push('style')
    <style>
        .wallet-card {
            background: linear-gradient(90deg, rgba(28, 29, 36, 1) 0%, rgba(58, 68, 84, 1) 100%);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            height: 200px !important;
        }

        .wallet-balance h2 {
            font-size: 2rem;
            line-height: 1.2;
            color: #fff;
        }

        .wallet-card small,
        .wallet-card span {
            opacity: 0.9;
        }

        .wallet-card::before {
            content: "";
            position: absolute;
            top: -40px;
            right: -40px;
            width: 100px;
            height: 100px;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            overflow: hidden;
        }
        .dropdown-menu {
            z-index: 1050;
        }

        .card-carousel {
            height: 100%;
        }
        .card-carousel .wallet-card {
            margin-right: 52px;
        }
        .card-carousel .owl-stage-outer {
            height: 100%;
            padding: 10px 0;
        }
        .card-carousel .owl-stage-outer .owl-stage {
            height: 100%;
        }
        .card-carousel .owl-stage-outer .owl-stage .owl-item {
            height: 100%;
        }
        @media (min-width: 600px) {
            .card-carousel .wallet-card {
                margin-right: 52px;
            }
        }
        @media (min-width: 992px) {
            .card-carousel .wallet-card {
                margin-right: 112px;
            }
        }
        @media (min-width: 1400px) {
            .card-carousel .wallet-card {
                margin-right: 52px;
            }
            .quick-links-swiper .icon.icon-sm {
                font-size: 1rem !important;
                width: 3.40625rem !important;
                height: 3.40625rem !important;
            }
        }
        @media (min-width: 1600px) {
            .card-carousel .wallet-card {
                margin-right: 112px;
            }
        }

        @media (max-width: 375px) {
            .wallet-balance h2 {
                font-size: 1.5rem !important;
            }
        }

        .quick-link-height {
            height: 5.25rem;
            overflow: hidden;
            overflow-y: auto;
        }

    </style>
@endpush


@push('script')
    <script>

        if ($('.quick-links-swiper').length) {
            const swiper = new Swiper('.quick-links-swiper', {
                preloadImages: false,
                loop: true,
                centeredSlides: false,
                resistance: true,
                resistanceRatio: 0.6,
                slidesPerView: 6,
                speed: 1400,
                spaceBetween: 30,
                parallax: false,
                effect: "slide",
                active: 'active',
                autoplay: {
                    delay: 2000,
                    disableOnInteraction: false
                },
                pagination: {
                    el: ".swiper-pagination",
                    clickable: true,
                },
                navigation: {
                    nextEl: '.slider-button-next4',
                    prevEl: '.slider-button-prev4',
                },
                breakpoints: {
                    1400: {
                        slidesPerView: 6,
                    },
                    991: {
                        slidesPerView: 4,
                    },
                    640: {
                        slidesPerView: 4,
                    },
                }
            });
        }
    </script>

@endpush
