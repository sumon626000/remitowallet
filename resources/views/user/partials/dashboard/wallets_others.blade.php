

@props(['basic'])

@php
    $services = [
        'transfer','request','exchange','redeem','escrow','voucher','deposit','payout','invoice',
        'qr_payment','virtual_card','bill_payment'
    ];
    $allActions = [
        'deposit'       => ['route' => 'fund.initialize', 'icon' => 'bi-wallet', 'bg' => 'bg-deposit', 'label' => __('Deposit')],
        'transfer'      => ['route' => 'user.transfer.initialize', 'icon' => 'bi-send-check', 'bg' => 'bg-transfer', 'label' => __('Transfer')],
        'exchange'      => ['route' => 'user.exchange.initialize', 'icon' => 'bi-currency-exchange', 'bg' => 'bg-exchange', 'label' => __('Exchange')],
        'payout'        => ['route' => 'user.payout', 'icon' => 'bi-cash-coin', 'bg' => 'bg-payout', 'label' => __('Payout')],
        'bill_payment'  => ['route' => 'user.pay.bill', 'icon' => 'bi-receipt-cutoff', 'bg' => 'bg-paybill', 'label' => __('Pay Bill')],
        'request'       => ['route' => 'user.requestMoney.initialize', 'icon' => 'bi-currency-dollar', 'bg' => 'bg-request', 'label' => __('Request')],
        'redeem'        => ['route' => 'user.redeem.initialize', 'icon' => 'bi-box', 'bg' => 'bg-receive', 'label' => __('Redeem')],
        'virtual_card'  => ['route' => 'user.virtual.card', 'icon' => 'bi-credit-card-2-front', 'bg' => 'bg-paybill', 'label' => __('V-Card')],
        'invoice'       => ['route' => 'user.invoice.create', 'icon' => 'bi-receipt', 'bg' => 'bg-invoice', 'label' => __('Invoice')],
        'qr_payment'    => ['route' => 'user.qr.code', 'icon' => 'bi-qr-code-scan', 'bg' => 'bg-qr', 'label' => __('QR Payment')],
        'voucher'       => ['route' => 'user.voucher.createRequest', 'icon' => 'bi-ticket', 'bg' => 'bg-kyc', 'label' => __('Voucher')],

        'play_game'     => ['route' => 'user.play.game', 'icon' => 'bi-graph-up-arrow', 'bg' => 'bg-exchange', 'label' => __('FX Trade')],
        '2step'         => ['route' => 'user.twostep.security', 'icon' => 'bi-shield-check', 'bg' => 'bg-transfer', 'label' => __('2Step Security')],
        'pin_manage'    => ['route' => 'user.securityPin.manage', 'icon' => 'bi-key', 'bg' => 'bg-security', 'label' => __('Security Pin')],
        'kyc'           => ['route' => 'user.verification.kyc', 'icon' => 'bi-person-check', 'bg' => 'bg-kyc', 'label' => __('Verification')],
        'notification'  => ['route' => 'user.notifications', 'icon' => 'bi-bell', 'bg' => 'bg-qr', 'label' => __('Notification')],
        'ticket'        => ['route' => 'user.ticket.list', 'icon' => 'bi-headset', 'bg' => 'bg-exchange', 'label' => __('Support')],
        'profile'       => ['route' => 'user.profile', 'icon' => 'bi-person', 'bg' => 'bg-deposit', 'label' => __('Profile')],
        'transaction'   => ['route' => 'user.transaction', 'icon' => 'bi-arrow-left-right', 'bg' => 'bg-payout', 'label' => __('Transaction')],
    ];

    $enabled = collect($allActions)->filter(function ($_, $key) use ($services, $basic) {
        return in_array($key, $services) && data_get($basic, $key) == 1;
    });
    $fallback = collect($allActions)->reject(function ($_, $key) use ($services) {
        return in_array($key, $services) || $key === 'play_game';
    });
    $actions = collect(['play_game' => $allActions['play_game']])->concat($enabled)->concat($fallback)->values();
@endphp

<div class="row d-flex flex-column flex-lg-row">

    <!-- Mobile View with Swiper -->
    <div class="col-lg-6 mb-3 mb-lg-5  order-1 order-lg-2">
        <div class="h-100">
            <div class="card-header card-header-content-between">
                <h4 class="card-header-title ms-3">@lang('Quick Links')</h4>
            </div>
            <div class="card-body px-0">
                <div class="swiper-container quick-links-swiper ">
                    <div class="swiper-wrapper">
                        @foreach ($actions->take(8) ?? [] as $action)
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

                                <div class="col-auto">
                                    <div class="dropdown">
                                        <a href="#" class="text-white"  id="walletDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="bi-three-dots-vertical"></i>
                                        </a>
                                        <div class="dropdown-menu dropdown-menu-end" aria-labelledby="walletDropdown">
                                            <a class="dropdown-item"
                                               href="{{ route('fund.initialize', ['receive_currency' => $wallet->currency->code]) }}">
                                                @lang('Add Fund')
                                            </a>
                                            <a class="dropdown-item"
                                               href="{{ route('user.transfer.initialize', ['currency' => $wallet->currency->code]) }}">
                                                @lang('Transfer Money')
                                            </a>
                                            <a class="dropdown-item"
                                               href="{{ route('user.exchange.initialize', ['currency' => $wallet->currency->code]) }}">
                                                @lang('Exchange Money')
                                            </a>
                                        </div>
                                    </div>
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
    <div class="col-lg-6 mb-3 mb-lg-5 d-none order-1 order-lg-2">
        <div class="card h-100">
            <div class="card-header card-header-content-between">
                <h4 class="card-header-title">@lang('Quick Links')</h4>
            </div>
            <div class="card-body quick-link-height p-3">

                <ul class="list-group list-group-flush list-group-no-gutters">

                    @foreach ($actions->take(8) ?? [] as $action)
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
                                            <a class="link" href="{{ route($action['route']) }}" target="_blank">
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
                                    <span class="icon icon-sm icon-circle icon-success {{ $action['bg'] }} ">
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


        .bg-deposit {
            background: linear-gradient(135deg, #32cc8c, #00b894);
        }
        .bg-payout {
            background: linear-gradient(135deg, #f0932b, #ff6f00);
        }
        .bg-transfer {
            background: linear-gradient(135deg, #4facfe, #00f2fe);
        }
        .bg-exchange {
            background: linear-gradient(135deg, #ff4b2b, #ff416c);
        }
        .bg-paybill {
            background: linear-gradient(135deg, #8e44ad, #9b59b6);
        }
        .bg-request {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }
        .bg-receive {
            background: linear-gradient(135deg, #1abc9c, #16a085);
        }
        .bg-kyc {
            background: linear-gradient(135deg, #5bcdfb, #3ce1e7);
        }
        .bg-invoice {
            background: linear-gradient(135deg, #7f8c8d, #95a5a6);
        }
        .bg-security {
            background: linear-gradient(135deg, #f1c40f, #f39c12);
        }
        .bg-qr {
            background: linear-gradient(135deg, #16a085, #27ae60);
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
