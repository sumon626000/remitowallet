@push('css')
    <style>
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
        }

        @media (min-width: 1600px) {
            .card-carousel .wallet-card {
                margin-right: 112px;
            }
        }

        .wallet-card {
            min-height: 170px;
            color: #333; /* Dark grey text for contrast */
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            padding: 5px;
        }

        .wallet-card .avatar {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            padding: 5px;
        }

        .wallet-card .avatar img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 50%;
        }

        .wallet-card .dropdown a {
            color: rgba(255, 255, 255, 0.7);
        }

        .wallet-card .dropdown-menu {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .wallet-card h3 {
            font-size: 1.4rem;
            font-weight: bold;
            margin-bottom: 0;
        }

    </style>
@endpush


<div class="row  g-1 mb-2 mt-1">
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-1 card-header-title">@lang('User Wallet Balance')</h4>
        <div class="">
            <button class="btn btn-sm btn-white btn-icon owl-prev"><i class="bi-chevron-left"></i></button>
            <button class="btn btn-sm btn-white btn-icon owl-next"><i class="bi-chevron-right"></i></button>
        </div>
    </div>
    <div class="owl-carousel owl-theme card-carousel ">
        @foreach($wallets as $wallet)
            <div class="card wallet-card bg-light user-card">
                <div class="card-body">
                    <div class="d-flex  justify-content-between">
                        <div>
                            <div class="card-title d-flex align-items-start ">
                                <div class="avatar avatar-sm avatar-circle">
                                    <img class="avatar-img avatar-sm"
                                         src="{{ $wallet->currency?->getImage() }}"
                                         alt="{{$wallet->currency?->code  }}"/>
                                </div>
                            </div>
                            <span class="text-dark">{{ __(@$wallet->currency?->name) }}</span>
                            <h3 class="card-title text-nowrap my-2 ">
                                {{ __(@$wallet->currency?->symbol) }} {{ (fractionNumber(getAmount(@$wallet->totalBalance))) }}
                            </h3>
                        </div>
                        <div class="col-auto">
                            <div class="dropdown">
                                <a href="#" class="text-body" id="walletDropdown" data-bs-toggle="dropdown"
                                   aria-expanded="false">
                                    <i class="bi-three-dots-vertical"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end bg-light" aria-labelledby="walletDropdown">
                                    <a class="dropdown-item text-dark"
                                       href="{{ route('admin.wallet.summary', ['code' => $wallet->currency?->code]) }}">
                                        @lang('Transaction Summary')
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>


</div>


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/global/css/owl.carousel.min.css') }}">
@endpush

@push('js-lib')
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
                    600: {items: 3},
                    992: {items: 3, margin: -100},
                    1400: {items: 4},
                    1600: {items: 4, margin: -100}
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
