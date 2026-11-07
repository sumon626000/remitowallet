@extends('admin.layouts.app')
@section('page_title', __('Bill Details'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Bill')</li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        @if($billDetails->last_api_error)
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
                                    class="text-danger">{{$billDetails->last_api_error}}</span></p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 card-header-title"> @lang('Bill Information')</h4>
                        <div class="d-flex justify-content-end">
                            <h6 class="m-0 card-header-title"> {{dateTime($billDetails->created_at)}}</h6>
                        </div>
                    </div>

                    <div class="card-body">
                        <ul class="step step-icon-sm">
                            <li class="step-item">
                                <div class="step-content-wrapper">
                                    <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                    <div class="step-content">
                                        <h5 class="mb-1">
                                            <a class="text-dark"
                                               href="#">@lang('Category')</a>
                                        </h5>
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="iconExample1" class="form-control"
                                                   value="{{ucfirst(str_replace("_"," ",$billDetails->category_name))}}">

                                            <a class="js-clipboard input-group-append input-group-text"
                                               href="javascript:;"
                                               data-hs-clipboard-options='{
                                                           "contentTarget": "#iconExample1",
                                                           "classChangeTarget": "#iconExampleLinkIcon1",
                                                           "defaultClass": "bi-clipboard",
                                                           "successClass": "bi-check"
                                                         }'>
                                                <i id="iconExampleLinkIcon1" class="bi-clipboard"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-content-wrapper">
                                    <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                    <div class="step-content">
                                        <h5 class="mb-1">
                                            <a class="text-dark"
                                               href="#">@lang('Type')</a>
                                        </h5>
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="iconExample2" class="form-control"
                                                   value="{{$billDetails->type}}">

                                            <a class="js-clipboard input-group-append input-group-text"
                                               href="javascript:;"
                                               data-hs-clipboard-options='{
                                                           "contentTarget": "#iconExample2",
                                                           "classChangeTarget": "#iconExampleLinkIcon2",
                                                           "defaultClass": "bi-clipboard",
                                                           "successClass": "bi-check"
                                                         }'>
                                                <i id="iconExampleLinkIcon2" class="bi-clipboard"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </li>
                            @if(!empty($billDetails->customer) && gettype($billDetails->customer) == 'object')
                                @foreach($billDetails->customer as $key => $info)
                                    <li class="step-item">
                                        <div class="step-content-wrapper">
                                            <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                            <div class="step-content">
                                                <h5 class="mb-1">
                                                    <a class="text-dark"
                                                       href="#">{{snake2Title($key)}}</a>
                                                </h5>
                                                <div class="input-group input-group-merge">
                                                    <input type="text" id="iconExample{{$key}}" class="form-control"
                                                           value="{{$info->$key}}">

                                                    <a class="js-clipboard input-group-append input-group-text"
                                                       href="javascript:;"
                                                       data-hs-clipboard-options='{
                                                           "contentTarget": "#iconExample{{$key}}",
                                                           "classChangeTarget": "#iconExampleLinkIcon{{$key}}",
                                                           "defaultClass": "bi-clipboard",
                                                           "successClass": "bi-check"
                                                         }'>
                                                        <i id="iconExampleLinkIcon{{$key}}" class="bi-clipboard"></i>
                                                    </a>
                                                </div>

                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            @endif
                            <li class="step-item">
                                <div class="step-content-wrapper">
                                    <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                    <div class="step-content">
                                        <h5 class="mb-1">
                                            <a class="text-dark"
                                               href="#">@lang('Country Code')</a>
                                        </h5>
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="iconExample6" class="form-control"
                                                   value="{{$billDetails->country_name}}">

                                            <a class="js-clipboard input-group-append input-group-text"
                                               href="javascript:;"
                                               data-hs-clipboard-options='{
                                                           "contentTarget": "#iconExample6",
                                                           "classChangeTarget": "#iconExampleLinkIcon6",
                                                           "defaultClass": "bi-clipboard",
                                                           "successClass": "bi-check"
                                                         }'>
                                                <i id="iconExampleLinkIcon6" class="bi-clipboard"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-content-wrapper">
                                    <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                    <div class="step-content">
                                        <h5 class="mb-1">
                                            <a class="text-dark"
                                               href="#">@lang('Amount')</a>
                                        </h5>
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="iconExample3" class="form-control"
                                                   value="{{getAmount($billDetails->amount,basicControl()->fraction_number)}} {{$billDetails->currency}}">

                                            <a class="js-clipboard input-group-append input-group-text"
                                               href="javascript:;"
                                               data-hs-clipboard-options='{
                                                           "contentTarget": "#iconExample3",
                                                           "classChangeTarget": "#iconExampleLinkIcon3",
                                                           "defaultClass": "bi-clipboard",
                                                           "successClass": "bi-check"
                                                         }'>
                                                <i id="iconExampleLinkIcon3" class="bi-clipboard"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-content-wrapper">
                                    <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                    <div class="step-content">
                                        <h5 class="mb-1">
                                            <a class="text-dark"
                                               href="#">@lang('Charge')</a>
                                        </h5>
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="iconExample4" class="form-control"
                                                   value="{{getAmount($billDetails->charge,basicControl()->fraction_number)}} {{$billDetails->currency}}">

                                            <a class="js-clipboard input-group-append input-group-text"
                                               href="javascript:;"
                                               data-hs-clipboard-options='{
                                                           "contentTarget": "#iconExample4",
                                                           "classChangeTarget": "#iconExampleLinkIcon4",
                                                           "defaultClass": "bi-clipboard",
                                                           "successClass": "bi-check"
                                                         }'>
                                                <i id="iconExampleLinkIcon4" class="bi-clipboard"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </li>
                            <li class="step-item">
                                <div class="step-content-wrapper">
                                    <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                    <div class="step-content">
                                        <h5 class="mb-1">
                                            <a class="text-dark"
                                               href="#">@lang('Payable Amount')</a>
                                        </h5>
                                        <div class="input-group input-group-merge">
                                            <input type="text" id="iconExample5" class="form-control"
                                                   value="{{getAmount($billDetails->payable_amount,basicControl()->fraction_number)}} {{$billDetails->currency}}">

                                            <a class="js-clipboard input-group-append input-group-text"
                                               href="javascript:;"
                                               data-hs-clipboard-options='{
                                                           "contentTarget": "#iconExample5",
                                                           "classChangeTarget": "#iconExampleLinkIcon5",
                                                           "defaultClass": "bi-clipboard",
                                                           "successClass": "bi-check"
                                                         }'>
                                                <i id="iconExampleLinkIcon5" class="bi-clipboard"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="m-0 card-title">@lang('Transaction')</h4>
                        @if($billDetails->status == 2)
                            <span class="badge bg-soft-warning text-warning">
                              <span class="legend-indicator bg-warning"></span>@lang('Pending')
                            </span>
                        @elseif($billDetails->status == 3)
                            <span class="badge bg-soft-success text-success">
                              <span class="legend-indicator bg-success"></span>@lang('Completed')
                            </span>
                        @elseif($billDetails->status == 4)
                            <span class="badge bg-soft-danger text-danger">
                              <span class="legend-indicator bg-danger"></span>@lang('Return')
                            </span>
                        @elseif($billDetails->status == 5)
                            <span class="badge bg-soft-info text-info">
                              <span class="legend-indicator bg-info"></span>@lang('Processing')
                            </span>
                        @endif
                    </div>

                    <div class="card-body">
                        <ul class="list-group list-group-flush list-group-no-gutters">
                            <li class="list-group-item" title="Full Name">
                                <a class="d-flex align-items-center"
                                   href="{{route('admin.user.view.profile',$billDetails->user_id)}}">
                                    {!! $billDetails->user?->profilePicture() !!}
                                    <div class="flex-grow-1 ms-3">
                                        <span class="text-body text-inherit">{{ $billDetails->user?->fullname }}</span>
                                    </div>
                                    <div class="flex-grow-1 text-end">
                                        <i class="bi-chevron-right text-body"></i>
                                    </div>
                                </a>
                            </li>

                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Bill method')</h5>
                                    <a class="link"
                                       href="javascript:void(0);">{{ __(optional($billDetails->method)->methodName) }}</a>
                                </div>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Transaction Id')</h5>
                                    <a class="link"
                                       href="javascript:void(0);">{{ __($billDetails->utr) }}</a>
                                </div>
                            </li>

                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Exchange Rate')</h5>
                                    <p class="text-dark">1 {{optional($billDetails->walletCurrency)->symbol}} <i
                                            class="fas fa-exchange-alt"></i> {{getAmount($billDetails->exchange_rate,basicControl()->fraction_number)}} {{$billDetails->currency}}
                                    </p>
                                </div>
                            </li>

                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Pay In Base')</h5>
                                    <span
                                        class="text-success">{{getAmount($billDetails->pay_amount_in_base,basicControl()->fraction_number)}} {{optional($billDetails->walletCurrency)->code}}</span>
                                </div>
                            </li>


                            @if($billDetails->status == 2)
                                <div class=" mt-4">
                                    <button data-bs-target="#confirmModal" data-bs-toggle="modal"
                                            data-route="{{route('admin.bill.pay.confirm',$billDetails->utr)}}"
                                            class="btn btn-success btn-block confirmButton"><i
                                            class="fa-light fa-check"></i> @lang('Pay Bill') {{getAmount($billDetails->amount,basicControl()->fraction_number)}} {{$billDetails->currency}}
                                    </button>
                                    <button class="btn btn-danger btn-block returnButton"
                                            data-bs-target="#confirmModal"
                                            data-route="{{route('admin.bill.pay.return',$billDetails->utr)}}"
                                            data-bs-toggle="modal"><i
                                            class="fa-light fa-undo"></i> @lang('Return Bill') {{getAmount($billDetails->payable_amount,basicControl()->fraction_number)}} {{$billDetails->currency}}
                                    </button>
                                </div>
                            @endif

                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="confirmModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="modalLabel">@lang('Confirmation !')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
                </div>
                <form action="" method="post" id="confirmForm">
                    @csrf
                    <div class="modal-body">
                        <p>@lang('Are you sure you want to confirm this action?')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Confirm')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection


@push('js-lib')
    <script src="{{ asset('assets/admin/js/clipboard.min.js') }}"></script>
@endpush

@push('script')
    <script>
        (function () {
            HSCore.components.HSClipboard.init('.js-clipboard')
        })();

        $(document).on('click', '.confirmButton,.returnButton', function () {
            let submitUrl = $(this).data('route');
            $('#confirmForm').attr('action', submitUrl)
        })
    </script>
@endpush
