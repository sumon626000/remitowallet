@extends('admin.layouts.app')
@section('page_title',__('Success Message'))

@section('content')
    <div class="content container-fluid mt-5">
        <div class="row justify-content-lg-center">
            <div class="col-lg-8">
                <div id="successMessageContent">
                    <div class="text-center">
                        <img class="img-fluid mb-3 customImageStyle" alt="..." data-hs-theme-appearance="default"
                             src="{{ asset('assets/admin/img/oc-hi-five.svg') }}">
                        <img class="img-fluid mb-3 customImageStyle" alt="..." data-hs-theme-appearance="dark"
                             src="{{ asset('assets/admin/img/oc-hi-five-light.svg') }}">
                        <div class="mb-4">
                            <h2>@lang("Successful")</h2>
                            <p>
                                @lang("New")
                                <span class="fw-semibold text-dark">{{ ucfirst($user->type) }}</span>
                                @lang("has been successfully created.")
                            </p>
                        </div>
                        <div class="d-flex justify-content-center">
                            <a class="btn btn-white me-3" href="{{ route('admin.dashboard') }}">
                                <i class="bi-chevron-left ms-1"></i> {{ __("Back to Dashboard") }}
                            </a>
                            <a class="btn btn-primary" href="{{ route('admin.users.add',$user->type) }}">
                                <i class="bi-person-plus-fill me-1"></i> @lang("Add more")
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection





