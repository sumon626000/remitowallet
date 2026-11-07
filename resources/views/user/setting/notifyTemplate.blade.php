@extends(userLayout())
@section('title',trans('Notifications'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Notifications Permission')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-12 mb-3 mb-lg-0">
                <form action="{{ userRoute('notificationPermission') }}" method="post">
                    @csrf
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <div class="">
                                <h4 class="card-header-title">@lang('Notify Permission Request')</h4>
                                <p class="mt-1">
                                    @lang('We need permission from your browser to show notifications.')
                                    <strong>@lang('Request Permission')</strong>
                                </p>
                            </div>
                            <div class="btn-area">
                                <button type="submit" class="btn btn-primary btn-sm">@lang('Save Changes')</button>
                            </div>
                        </div>

                        <div class="card-body">

                            <div class="cmn-table mt-20">
                                <div class="table-responsive">
                                    <table class="table align-middle">
                                        <thead class="thead-light">
                                        <tr>
                                            <th style="width: 15%;" scope="col">@lang('Type')</th>
                                            <th style="width: 5%;" scope="col">📧 @lang('Email')</th>
                                            <th style="width: 5%;" scope="col">💬 @lang('SMS')</th>
                                            <th style="width: 5%;" scope="col">💻 @lang('Push')</th>
                                            <th style="width: 3%;" scope="col">📱 @lang('In App')</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @forelse($notificationTemplates as $key => $item)
                                            <tr>
                                                <td data-label="Type">
                                                    <div class="d-flex align-items-center">
                                                        <span>{{ $item->name }}</span>
                                                    </div>
                                                </td>
                                                <td data-label="📧 Email">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                               role="switch" name="email_key[]"
                                                               value="{{$item->template_key ?? ""}}"
                                                               {{ !$item->email ? 'disabled':'' }}
                                                               id="emailSwitch"
                                                            {{ in_array($item->template_key, optional($user->notifypermission)->template_email_key ?? []) ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td data-label="💬 SMS">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                               role="switch" name="sms_key[]"
                                                               value="{{ $item->template_key ?? "" }}"
                                                               {{ !$item->sms ? 'disabled' : '' }}
                                                               id="pushSwitch"
                                                            {{ in_array($item->template_key, optional($user->notifypermission)->template_sms_key ?? []) ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                                <td data-label="💻 Push">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                               role="switch" name="push_key[]"
                                                               value="{{ $item->template_key ?? "" }}"
                                                               {{ !$item->push ? 'disabled' : '' }}
                                                               id="pushSwitch"
                                                            {{ in_array($item->template_key, optional($user->notifypermission)->template_push_key ?? []) ? 'checked' : '' }}>
                                                    </div>
                                                </td>

                                                <td data-label="📱 App">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox"
                                                               role="switch" name="in_app_key[]"
                                                               value="{{$item->template_key ?? ""}}"
                                                               id="appSwitch"
                                                            {{!$item->in_app ? 'disabled':''}}
                                                            {{ in_array($item->template_key, optional($user->notifypermission)->template_in_app_key ?? []) ? 'checked' : '' }}>
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <th colspan="100%" class="text-center text-dark">
                                                    <div class="no_data_iamge text-center">
                                                        <img style="height: 200px;width: 200px" src="{{ asset(config('filelocation.not_found')) }}">
                                                    </div>
                                                    <p class="text-center">@lang('Notification Template List is empty here!.')</p>
                                                </th>
                                            </tr>
                                        @endforelse
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <!-- Cmn table end -->
                        </div>

                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection
