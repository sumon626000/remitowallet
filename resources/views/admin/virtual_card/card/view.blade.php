@extends('admin.layouts.app')
@section('page_title', __('Card Details'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.dashboard') }}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Virtual Card')</li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        @if($cardView->last_error)
            <div class="alert alert-soft-dark mb-5" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img class="avatar avatar-xl alert_image" src="{{ asset('assets/admin/img/oc-megaphone.svg') }}"
                             alt="Announce" data-hs-theme-appearance="default">
                        <img class="avatar avatar-xl alert_image"
                             src="{{ asset('assets/admin/img/oc-megaphone-light.svg') }}"
                             alt="Announce" data-hs-theme-appearance="dark">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">@lang("Last Api error message :- ") <span
                                    class="text-danger">@lang($cardView->last_error)</span></p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if($cardView->cardMethod?->code == 'stripe' && $cardView->status == 8)
            <div class="alert alert-soft-dark mb-5" role="alert">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <img class="avatar avatar-xl alert_image" src="{{ asset('assets/admin/img/oc-megaphone.svg') }}"
                             alt="Announce" data-hs-theme-appearance="default">
                        <img class="avatar avatar-xl alert_image"
                             src="{{ asset('assets/admin/img/oc-megaphone-light.svg') }}"
                             alt="Announce" data-hs-theme-appearance="dark">
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="d-flex align-items-center">
                            <p class="mb-0">@lang("Stripe add fund info :- ")
                                <span class="text-danger">
                                    @lang("Funding for Stripe virtual cards must be added manually, as Stripe's API does not support direct funding for virtual cards.")
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 card-header-title">{{$cardView->cardMethod?->name}} @lang('Virtual Card')</h4>
                        <div class="d-flex justify-content-end">
                            @if($cardView->status == 5 || $cardView->status == 7 )
                                <a href="javascript:void(0)"
                                   data-bs-target="#rejectReason"
                                   data-bs-toggle="modal"
                                   class="btn btn-sm btn-secondary mx-2">@lang('Reason')</a>
                            @endif
                            <a href="{{route('admin.virtual.cardList','all')}}" class="btn btn-primary btn-sm">
                                <i class="bi-arrow-left"></i> @lang('Back')
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($cardView->card_info)
                            <ul class="step step-icon-sm">
                                @forelse($cardView->card_info as $k => $v)
                                    <li class="step-item">
                                        <div class="step-content-wrapper">
                                            <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                            <div class="step-content">
                                                <h5 class="mb-1">
                                                    <a class="text-dark"
                                                       href="#">{{ __(ucfirst(str_replace('_',' ', $v->field_name))) }}</a>
                                                </h5>
                                                <div class="input-group input-group-merge">
                                                    <input type="text" id="iconExample{{$k}}" class="form-control"
                                                           value="{{ @$v->field_value }}">

                                                    <a class="js-clipboard input-group-append input-group-text"
                                                       href="javascript:;"
                                                       data-hs-clipboard-options='{
                                                           "contentTarget": "#iconExample{{$k}}",
                                                           "classChangeTarget": "#iconExampleLinkIcon{{$k}}",
                                                           "defaultClass": "bi-clipboard",
                                                           "successClass": "bi-check"
                                                         }'>
                                                        <i id="iconExampleLinkIcon{{$k}}" class="bi-clipboard"></i>
                                                    </a>
                                                </div>

                                            </div>
                                        </div>
                                    </li>
                                @empty
                                @endforelse
                            </ul>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 card-title">@lang('User Details')</h4>
                        {!! $cardView->statusMessage !!}
                    </div>

                    <div class="card-body">
                        <ul class="list-group list-group-flush list-group-no-gutters">
                            <li class="list-group-item" title="Full Name">
                                <a class="d-flex align-items-center"
                                   href="{{route('admin.user.view.profile',$cardView->user_id)}}">
                                    {!! $cardView->user->profilePicture() !!}
                                    <div class="flex-grow-1 ms-3">
                                        <span class="text-body text-inherit">{{ $cardView->user?->fullname }}</span>
                                    </div>
                                    <div class="flex-grow-1 text-end">
                                        <i class="bi-chevron-right text-body"></i>
                                    </div>
                                </a>
                            </li>

                            <li class="list-group-item" title="Card Method">
                                <a class="d-flex align-items-center"
                                   href="{{ route('admin.virtual.cardEdit',$cardView->cardMethod?->id) }}">
                                    <div class="icon icon-soft-info icon-circle">
                                        <i class="bi-credit-card"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <span class="text-body text-inherit">{{ $cardView->cardMethod?->name }}</span>
                                    </div>
                                    <div class="flex-grow-1 text-end">
                                        <i class="bi-chevron-right text-body"></i>
                                    </div>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Currency')</h5>
                                    <a class="link" href="javascript:void(0);">{{ $cardView->currency }}</a>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Balance')</h5>
                                    <a class="link"
                                       href="javascript:void(0);">{{ $cardView->balance }} {{ $cardView->currency }}</a>
                                </div>
                            </li>

                            @if($cardView->status == 8)
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>@lang('Fund Amount')</h5>
                                        <span
                                            class="text-success">{{$cardView->fund_amount}} {{$cardView->currency}}</span>
                                    </div>
                                </li>
                                <li class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5>@lang('Add Fund Charge')</h5>
                                        <span
                                            class="text-danger">{{$cardView->fund_charge}} {{$cardView->currency}}</span>
                                    </div>
                                </li>
                            @endif

                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Card Order Charge')</h5>
                                    <p class="text-danger">{{$cardView->charge}} {{optional($cardView->chargeCurrency)->code}}</p>
                                </div>
                            </li>

                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Expiry Date')</h5>
                                    <p class="text-danger">{{ $cardView->expiry_date }}</p>
                                </div>
                            </li>

                            @if($cardView->status == 8)
                                <div class=" mt-4">
                                    <button data-bs-target="#returnFund" data-bs-toggle="modal"
                                            class="btn btn-info btn-block "><i class="fa-light fa-undo"></i> @lang('Return') {{$cardView->fund_amount +$cardView->fund_charge}} {{$cardView->currency}}</button>
                                    <button class="btn btn-success btn-block"
                                            data-bs-target="#approveFund"
                                            data-bs-toggle="modal"><i class="fa-light fa-check"></i> @lang('Approve') {{$cardView->fund_amount}} {{$cardView->currency}}</button>
                                </div>
                            @endif

                        </ul>

                        <div class="d-flex justify-content-between mt-4">
                            @if($cardView->status == 7)
                                <button data-bs-target="#unblockModal" data-bs-toggle="modal"
                                        class="btn btn-success btn-block "><i class="fa-light fa-unlock"></i> @lang('Unblock')</button>
                            @endif
                            @if($cardView->status != 7)
                                <button class="btn btn-danger btn-block"
                                        data-bs-target="#blockModal"
                                        data-bs-toggle="modal"><i class="fa-light fa-lock"></i> @lang('Block')</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if($cardView->status == 8)
        <div id="returnFund" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold"
                            id="primary-header-modalLabel">@lang('Return Confirmation')</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <form action="{{route('admin.virtual.cardFundReturn',$cardView->id)}}" method="post">
                        @csrf
                        <div class="modal-body">
                            <p>@lang('Are you really want to return this fund ?')</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div id="approveFund" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold"
                            id="primary-header-modalLabel">@lang('Approve Confirmation')</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <form action="{{route('admin.virtual.cardFundApprove',$cardView->id)}}" method="post">
                        @csrf
                        <div class="modal-body">
                            <p>@lang('Are you really want to approve this fund ?')</p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif


    <div id="unblockModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Unblock Confirmation')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form action="{{route('admin.virtual.cardUnBlock',$cardView->id)}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <p>@lang('Are you want to unblock this card ?')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <div id="blockModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalLabel">@lang('Block Confirmation')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form action="{{route('admin.virtual.cardBlock',$cardView->id)}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <lable>@lang('Reasons')</lable>
                                <textarea class="form-control" name="reason" required></textarea>
                            </div>
                            @error('reason')<span class="text-danger ml-2">{{$message}}</span>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if($cardView->status == 5 || $cardView->status == 7)
        <div id="rejectReason" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="primary-header-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold"
                            id="primary-header-modalLabel">@lang('Block Reason ')</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <lable class="form-label my-2">@lang('Reasons')</lable>
                                <textarea class="form-control" readonly>{{$cardView->reason}}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection



@push('js-lib')
    <script src="{{ asset('assets/admin/js/clipboard.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function () {
            HSCore.components.HSClipboard.init('.js-clipboard')
        })();
    </script>
@endpush
