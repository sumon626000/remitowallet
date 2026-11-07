@extends('admin.layouts.app')
@section('page_title', __('Request Details'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="{{ route('admin.dashboard') }}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Virtual Card')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Request List')</li>

                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        @if($cardOrderDetail->last_error)
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
                                    class="text-danger">@lang($cardOrderDetail->last_error)</span></p>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="card-title m-0">@lang('Request For')
                            - {{$cardOrderDetail->cardMethod->name}} @lang('Virtual Card')</h4>
                        <div class="d-flex justify-content-end">
                            @if($cardOrderDetail->status == 2 || $cardOrderDetail->status == 3)
                                <a href="javascript:void(0)"
                                   data-bs-target="#rejectReason"
                                   data-bs-toggle="modal"
                                   class="btn btn-secondary btn-sm mx-2">@lang('Rejected Reason')</a>
                            @endif
                            <a href="{{route('admin.virtual.cardOrder')}}"
                               class="btn btn-primary btn-sm"><i
                                    class="bi bi-arrow-left me-2"></i>@lang('Back')</a>
                        </div>
                    </div>

                    <div class="card-body">
                        @if($cardOrderDetail->form_input)
                            <ul class="step step-icon-sm">
                                @forelse($cardOrderDetail->form_input as $k => $v)
                                    <li class="step-item">
                                        <div class="step-content-wrapper">
                                            <span class="step-icon step-icon-soft-dark step-icon-pseudo"></span>
                                            <div class="step-content">
                                                <h5 class="mb-1">
                                                    <a class="text-dark"
                                                       href="#">{{ __(ucfirst(str_replace('_',' ', $v->field_level))) }}</a>
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
                        {!! $cardOrderDetail->statusMessage !!}
                    </div>

                    <div class="card-body">
                        <ul class="list-group list-group-flush list-group-no-gutters">
                            <li class="list-group-item" title="Full Name">
                                <a class="d-flex align-items-center"
                                   href="{{route('admin.user.view.profile',$cardOrderDetail->user_id)}}">
                                    {!! $cardOrderDetail->user?->profilePicture() !!}
                                    <div class="flex-grow-1 ms-3">
                                        <span
                                            class="text-body text-inherit">{{ $cardOrderDetail->user?->fullname }}</span>
                                    </div>
                                    <div class="flex-grow-1 text-end">
                                        <i class="bi-chevron-right text-body"></i>
                                    </div>
                                </a>
                            </li>

                            <li class="list-group-item" title="Card Method">
                                <a class="d-flex align-items-center"
                                   href="{{ route('admin.virtual.cardEdit',$cardOrderDetail->cardMethod?->id) }}">
                                    <div class="icon icon-soft-info icon-circle">
                                        <i class="bi-credit-card"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <span
                                            class="text-body text-inherit">{{ $cardOrderDetail->cardMethod?->name }}</span>
                                    </div>
                                    <div class="flex-grow-1 text-end">
                                        <i class="bi-chevron-right text-body"></i>
                                    </div>
                                </a>
                            </li>
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h5>@lang('Currency')</h5>
                                    <a class="link" href="javascript:void(0);">{{ $cardOrderDetail->currency }}</a>
                                </div>
                            </li>
                        </ul>

                        <div class="d-flex justify-content-between mt-4">
                            <button class="btn btn-success btn-block mr-2 mt-2"
                                    data-bs-target="#approveModal"
                                    data-bs-toggle="modal">@lang('Approve')</button>
                            @if($cardOrderDetail->status != 2)
                                <button class="btn btn-danger btn-block mr-2 mt-2"
                                        data-bs-target="#rejectModal"
                                        data-bs-toggle="modal">@lang('Reject')</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="approveModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Approve Confirmation')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                </div>
                <form action="{{route('admin.virtual.cardOrderApprove',$cardOrderDetail->id)}}" method="get">
                    <div class="modal-body">
                        <p>@lang('Are you want to approve this card request ?')</p>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div id="rejectModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Reject Confirmation')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{route('admin.virtual.cardOrderRejected',$cardOrderDetail->id)}}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="row ">
                            <div class="selectgroup w-100">
                                <label class="form-label">@lang('Allow to user resubmitted ?')</label>

                                <input type="radio" class="btn-check mx-2" name="resubmitted" id="success-outlined"
                                       value="1" checked>
                                <label class="btn btn-sm btn-outline-success" for="success-outlined">@lang('Yes')</label>

                                <input type="radio" class="btn-check" name="resubmitted" id="danger-outlined" value="0">
                                <label class="btn btn-sm btn-outline-danger" for="danger-outlined">@lang('No')</label>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <lable class="form-label">@lang('Reasons')</lable>
                                <textarea class="form-control" name="reason" required></textarea>
                            </div>
                            @error('reason')<span class="text-danger ml-2">{{$message}}</span>@enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary change-yes">@lang('Yes')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if($cardOrderDetail->status == 2 || $cardOrderDetail->status == 3)
        <div id="rejectReason" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="primary-header-modalLabel"
             aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title text-dark font-weight-bold"
                            id="primary-header-modalLabel">@lang('Rejected Reason ')</h4>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <lable class="form-label">@lang('Reasons')</lable>
                                <textarea class="form-control" readonly>{{$cardOrderDetail->reason}}</textarea>
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

    @if ($errors->any())
        <script>
            "use strict";
            @foreach ($errors->unique() as $error)
            Notiflix.Notify.failure("{{ trans($error) }}");
            @endforeach
        </script>
    @endif
@endpush
