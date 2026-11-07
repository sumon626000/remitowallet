@extends(userLayout())
@section('title', __('Success Message'))

@section('content')
    <div class="content {{ containerClass() }} mt-5">
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
                            <p>{{ session('message', __("Your action has been successfully completed.")) }}</p>
                        </div>
                        <div class="d-flex justify-content-center">
                            <a class="btn btn-white me-3" href="{{ session('back_route', route('user.dashboard')) }}">
                                <i class="bi-chevron-left ms-1"></i> {{ session('back_text', __("Back to Dashboard")) }}
                            </a>
                            <a class="btn btn-primary" href="{{ session('next_route', route('user.dashboard')) }}">
                                 {{ session('next_text', __("Go to Next")) }} <i class="bi-chevron-right me-1"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
