@extends(userLayout())
@section('title', trans('Identity Verify'))

@section('content')

    <div class="content {{ containerClass() }}">
        <div class="page-header border-0 mb-0">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Settings")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">{{ $kyc->name ?? "Identity" }} @lang("Verification")</h1>
                </div>
            </div>
        </div>

        <div class="row g-2">
            @if($userKyc && $userKyc->status == 0)

                <div class="shadow p-3 mb-3 alert alert-soft-warning mb-lg-3" role="alert">
                    <div class="alert-box d-flex flex-wrap align-items-center">
                        <div class="flex-shrink-0">
                            <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone.svg') }}" alt="..." data-hs-theme-appearance="default">
                            <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone-light.svg') }}" alt="..." data-hs-theme-appearance="dark">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ __("Notice!") }}</h3>
                            <div class="d-flex align-items-center">
                                <p class="mb-0 text-body fw-semibold">
                                    @lang('Your') @lang($userKyc->kyc_type) @lang('verification is pending.')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($userKyc && $userKyc->status == 1)
                <div class="shadow p-3 mb-3 alert alert-soft-success mb-lg-3" role="alert">
                    <div class="alert-box d-flex flex-wrap align-items-center">
                        <div class="flex-shrink-0">
                            <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone.svg') }}" alt="..." data-hs-theme-appearance="default">
                            <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone-light.svg') }}" alt="..." data-hs-theme-appearance="dark">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ __("Notice!") }}</h3>
                            <div class="d-flex align-items-center">
                                <p class="mb-0 text-body fw-semibold">
                                    @lang('Your') @lang($userKyc->kyc_type) @lang('verification has already been completed.')
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            @elseif($userKyc && $userKyc->status == 2)
                <div class="shadow p-3 mb-3 alert alert-soft-danger mb-lg-3" role="alert">
                    <div class="alert-box d-flex flex-wrap align-items-center">
                        <div class="flex-shrink-0">
                            <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone.svg') }}" alt="..." data-hs-theme-appearance="default">
                            <img class="avatar avatar-lg" src="{{ asset('assets/admin/img/oc-megaphone-light.svg') }}" alt="..." data-hs-theme-appearance="dark">
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h3 class="mb-1">{{ __("Notice!") }}</h3>
                            <div class="d-flex align-items-center">
                                <p class="mb-0 text-body fw-semibold">
                                    @lang('Your') @lang($userKyc->kyc_type) @lang('verification has been rejected.')
                                </p>
                            </div>
                        </div>
                        <button class="btn btn-danger text-nowrap" data-bs-target="#rejectReason" data-bs-toggle="modal">@lang('Reason')</button>
                    </div>
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    @if(!$userKyc  || $userKyc->status == 2)
                        <form action="{{ userRoute('kyc.verification.submit') }}" method="post" enctype="multipart/form-data">
                            @csrf
                            <div class="card">
                                <div class="card-header border-0 text-start text-md-center">
                                    <h5 class="card-title">{{ trans('KYC Information') }}</h5>
                                    <p>{{ trans('Verify your process instantly.') }}</p>
                                </div>
                                <div class="card-body ">
                                    <div class="row g-2">
                                        <input type="hidden" name="type" value="{{ $item->id }}">
                                        @foreach($item->input_form as $k => $value)
                                            <div class="col-12">
                                                <label class="form-label">{{ $value->field_label }}</label>
                                                @if($value->type == "file")
                                                    <div class="image-area">
                                                        <img id="upload" class="avatar-img avatar avatar-xl avatar-square"
                                                             src="{{ asset(config('filelocation.default')) }}" alt="...">
                                                    </div>
                                                    <div class="col-md-6 d-flex mt-2 mb-2">
                                                        <div class="gap-3 align-items-center">
                                                            <label for="formFile" class="form-label">@lang('Upload File')</label>
                                                            <input class="form-control" type="file" id="formFile"
                                                                   name="{{ @$value->field_name }}" onchange="previewImage('upload')">
                                                        </div>
                                                    </div>
                                                    @if($errors->has(@$value->field_name))
                                                        <div class="error text-danger">@lang($errors->first(@$value->field_name)) </div>
                                                    @endif
                                                @else
                                                    @if($value->type == "textarea")
                                                        <textarea class="form-control" id="" cols="30" rows="5" name="{{ $value->field_name }}" placeholder="{{ $value->field_label }}"
                                                        >{{ old($value->field_name, $userKyc->kyc_info->{$value->field_name}->field_value ?? '') }}</textarea>
                                                    @else
                                                        <input type="{{ $value->type }}" class="form-control" name="{{ $value->field_name }}"
                                                               value="{{ old($value->field_name,  $userKyc->kyc_info->{$value->field_name}->field_value ?? '') }}"
                                                               placeholder="{{ $value->field_label }}"
                                                               @if($value->type == "number") onkeyup="this.value = this.value.replace (/^\.|[^\d\.]/g, '')" @endif
                                                               @if($value->validation == "required") required @endif
                                                        >
                                                    @endif
                                                    @if($errors->has($value->field_name))
                                                        <div class="error text-danger">@lang($errors->first($value->field_name)) </div>
                                                    @endif
                                                @endif
                                            </div>
                                        @endforeach

                                        <div class="btn-area">
                                            <button type="submit" class="btn btn-primary mt-2">
                                                {{ ($userKyc && $userKyc->status == 2) ? trans('Re-submit') : trans('Submit') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    @endif

                </div>
            </div>
        </div>
    </div>

    @if($userKyc && $userKyc->status == 2)
        <div id="rejectReason" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="reason-modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="describeModalLabel"> @lang('Rejected Reason !')</h4>
                        <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        @lang($userKyc->reason)
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@endsection
