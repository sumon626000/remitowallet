@extends(userLayout())
@section('title', trans('Banks & Cards'))
@section('content')
    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Banks & Cards')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Banks & Cards')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-md-8">
                <!-- Card -->
                <div class="card">
                    <div class="card-header border-bottom">
                        <h4 class="card-header-title">@lang('Banks & Cards')</h4>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <div class="mb-4">
                            @if(!empty($cardOrder))
                                @if($cardOrder->status == 0 || $cardOrder->status == 3)
                                    <p>@lang('Your virtual card request is pending.')</p>
                                @endif
                                @if($cardOrder->status == 2)
                                    <div class="media align-items-center d-flex justify-content-start">
                                        <p>@lang('Your virtual card request is rejected by authority.')
                                            <a class="btn btn-ghost-secondary btn-sm"
                                               href="#" data-bs-target="#rejectReason" data-bs-toggle="modal">
                                                <i class="bi-exclamation me-1"></i> @lang('Reason')
                                            </a>
                                            @if($cardOrder->resubmitted == 1)
                                                <a class="btn btn-ghost-secondary btn-sm"
                                                   href="{{route('user.virtual.card.orderReSubmit')}}">
                                                    <i class="bi-arrow-90deg-right me-1"></i> @lang('Resubmit Now')
                                                </a>
                                            @endif
                                        </p>
                                    </div>
                                @endif
                                @if($cardOrder->status == 4)
                                    <div class="media align-items-center d-flex justify-content-start">
                                        <p>@lang('Your virtual card request is generated please make it complete.')
                                            <a class="btn btn-ghost-secondary btn-sm"
                                               href="{{route('user.order.confirm',$cardOrder->id)}}">
                                                <i class="bi-arrow-90deg-right me-1"></i> @lang('Confirm')
                                            </a>
                                        </p>
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- List Group -->
                        <ul class="list-group mb-5">
                            @if(count($approveCards)>0)
                                @foreach($approveCards as $card)
                                    <!-- Item -->
                                    <li class="list-group-item">
                                        <div class="mb-2">
                                            <h5>{{$card->name_on_card}}
                                                <span
                                                    class="badge {{($card->is_active == 'Active') ? 'bg-success':'bg-danger'}} ms-1">{{$card->is_active}}</span>
                                                @if($card->status == 5)
                                                    <span class="text-danger small ms-1">@lang('Requested Block')</span>
                                                @endif
                                            </h5>
                                        </div>

                                        <!-- Media -->
                                        <div class="d-flex v-card-flex">
                                            <div class="flex-shrink-0">
                                                <img class="avatar avatar-sm"
                                                     src="{{asset('assets/admin/img/oc-add-card-dark.svg')}}"
                                                     data-hs-theme-appearance="default">
                                                <img class="avatar avatar-sm"
                                                     src="{{asset('assets/admin/img/oc-add-card.svg')}}"
                                                     data-hs-theme-appearance="dark">
                                            </div>

                                            <div class="flex-grow-1 ms-lg-3 ms-0">
                                                <div class="row">
                                                    <div class="col-sm mb-3 mb-sm-0">
                                                <span
                                                    class="d-block text-dark">{{$card->card_number}} | {{$card->currency}}</span>
                                                        <small class="d-block text-muted">{{$card->cvv}}
                                                            - @lang('Valid Thru:')
                                                            {{\Carbon\Carbon::parse($card->expiry_date)->format('m/y')}}</small>
                                                    </div>
                                                    <!-- End Col -->

                                                    <div class="col-sm-auto">
                                                        <div class="d-flex gap-3 actions">
                                                            @if($card->status != 9)
                                                                @php
                                                                    $cardCurrencyCheck = cardCurrencyCheck($card->id);
                                                                @endphp
                                                                <a class="btn btn-white btn-sm addFundBtn"
                                                                   data-bs-toggle="modal"
                                                                   data-bs-target="#addFundModal"
                                                                   data-min="{{$cardCurrencyCheck['MinimumAmount']}}"
                                                                   data-max="{{$cardCurrencyCheck['MaximumAmount']}}"
                                                                   data-code="{{$cardCurrencyCheck['currencyCode']}}"
                                                                   data-percent="{{$cardCurrencyCheck['PercentCharge']}}"
                                                                   data-fixed="{{$cardCurrencyCheck['FixedCharge']}}"
                                                                   data-route="{{route('fund.initialize',['card',$card->id])}}"
                                                                >
                                                                    <i class="bi-currency-dollar me-1"></i> @lang('Add Fund')
                                                                </a>
                                                                @if($card->status != 5 && $card->status != 6)
                                                                    <a class="btn btn-white btn-sm blockRqst"
                                                                       href="javascript:;"
                                                                       data-bs-toggle="modal"
                                                                       data-bs-target="#blockRqst"
                                                                       data-route="{{route('user.virtual.cardBlock',$card->id)}}">
                                                                        <i class="bi-lock me-1"></i> @lang('Block Card')
                                                                    </a>
                                                                @endif
                                                            @endif
                                                            <a class="btn btn-white btn-sm"
                                                               href="{{route('user.virtual.cardTransaction',$card->card_Id)}}">
                                                                <i class="fa-light fa-exchange me-1"></i> @lang('Transaction')
                                                            </a>
                                                        </div>
                                                    </div>
                                                    <!-- End Col -->
                                                </div>
                                                <!-- End Row -->
                                            </div>
                                        </div>
                                        <!-- End Media -->
                                    </li>
                                    <!-- End Item -->
                                @endforeach
                            @endif
                        </ul>
                        <!-- End List Group -->

                        <div class="row">
                            <div class="col-lg-6">
                                <!-- Card -->
                                <a class="card card-dashed card-centered" href="{{route('user.virtual.card.order')}}">
                                    <div class="card-body card-dashed-body py-8">
                                        <img class="avatar avatar-lg avatar-4x3 mb-2"
                                             src="{{asset('assets/admin/img/oc-add-card-dark.svg')}}"
                                             alt="Image Description"
                                             data-hs-theme-appearance="default">
                                        <img class="avatar avatar-lg avatar-4x3 mb-2"
                                             src="{{asset('assets/admin/img/oc-add-card.svg')}}"
                                             alt="Image Description" data-hs-theme-appearance="dark">
                                        <span><i class="bi-plus"></i> @lang('Add a new card')</span>
                                    </div>
                                </a>
                                <!-- End Card -->
                            </div>
                            <!-- End Col -->
                        </div>
                        <!-- End Row -->
                    </div>
                    <!-- End Body -->
                </div>
                <!-- End Card -->
            </div>
        </div>
    </div>

    @if(!empty($cardOrder))
        @if($cardOrder->status == 2)
            <!-- Modal -->
            <div id="rejectReason" class="modal fade" tabindex="-1" role="dialog"
                 aria-labelledby="exampleModalTopCoverTitle" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content">
                        <!-- Header -->
                        <div class="modal-top-cover bg-dark text-center">
                            <figure class="position-absolute end-0 bottom-0 start-0">
                                <svg preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg" x="0px" y="0px"
                                     viewBox="0 0 1920 100.1">
                                    <path fill="#fff" d="M0,0c0,0,934.4,93.4,1920,0v100.1H0L0,0z"/>
                                </svg>
                            </figure>

                            <div class="modal-close">
                                <button type="button" class="btn-close btn-close-light" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                            </div>
                        </div>
                        <!-- End Header -->

                        <div class="modal-top-cover-icon">
                            <span class="icon icon-lg icon-light icon-circle icon-centered shadow-sm">
                              <i class="bi-receipt fs-2"></i>
                            </span>
                        </div>

                        <div class="modal-body">
                            <p>{{$cardOrder->reason}}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Modal -->
        @endif
    @endif

    <div class="modal fade" id="blockRqst" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">@lang('Block Confirmation')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="post" class="blockForm">
                    @csrf
                    <div class="modal-body">
                        <p>@lang('Are You sure to send block request for this card ?')</p>
                        <div class="row">
                            <div class="col-md-12">
                                <label>@lang('Reason For Block')</label>
                                <textarea class="form-control" name="reason"></textarea>
                                @error('reason')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Add fund Modal -->
    <div class="modal fade" id="addFundModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">@lang('Fund Add on Virtual Card')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="" method="Get" id="addFundForm">
                    <div class="modal-body">
                        <small class="text-cap mb-2">@lang('Transaction Overview')</small>
                        <ul class="list-group mb-4">
                            <li class="list-group-item text-dark">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>@lang('Minimum Fund Amount')</span>
                                    <span id="minimumAdd">0 USD</span>
                                </div>
                            </li>
                            <li class="list-group-item text-dark">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span>@lang('Maximum Fund Amount')</span>
                                    <span id="maximumAdd">0 USD</span>
                                </div>
                            </li>
                            <li class="list-group-item list-group-item-light text-danger">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong>@lang('Charge')</strong>
                                    <strong id="fundCharge">0 USD</strong>
                                </div>
                            </li>
                        </ul>
                        <div class="mb-4">
                            <label for="editCardNameLabel" class="form-label">@lang('Amount you want to add')</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">USD</span>
                                <input type="text" class="form-control" name="cardFundAmount" placeholder="500"
                                       aria-label="Username"
                                       aria-describedby="basic-addon1" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Continue') <i class="bi-chevron-right"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- End Modal -->

@endsection
@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        $(document).on('click', '.blockRqst', function () {
            var route = $(this).data('route');
            $('.blockForm').attr('action', route);
        })

        $(document).on('click', '.addFundBtn', function () {
            let min = $(this).data('min');
            let max = $(this).data('max');
            let code = $(this).data('code');
            let percent = $(this).data('percent');
            let fixed = $(this).data('fixed');
            $('#minimumAdd').text(`${min} ${code}`);
            $('#maximumAdd').text(`${max} ${code}`);
            $('#fundCharge').text(`${percent}%+${fixed} ${code}`);

            $('#addFundForm').attr('action', $(this).data('route'));
        })
    </script>
@endpush


@push('style')
    <style>
        @media only screen and (max-width: 767px) {

            .actions {
                flex-direction: column;
            }

            .v-card-flex {
                flex-direction: column;
            }
        }

    </style>
@endpush
