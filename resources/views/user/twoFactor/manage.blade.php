@extends(userLayout())
@section('title',__('Manage PIN uses'))

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
                    <h1 class="page-header-title">@lang('Manage PIN uses')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card mb-4">
                    <div class="card-header card-header-content-sm-between">
                        <h5 class="card-header-title mb-2 mb-sm-0">@lang('Manage PIN uses')</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ userRoute('securityPin.manage') }}" method="post">
                            @csrf
                            <div class="row">
                                @php $hasActiveServices = false; @endphp
                                @foreach($transactionType as $key => $value)
                                    @if($basicControl->{$key})
                                        @php $hasActiveServices = true; @endphp
                                        <div class="col-md-3">
                                            <div class="form-check mb-3">
                                                <input type="checkbox" class="form-check-input"
                                                       id="{{ $key.'-'.$value }}"
                                                       value="{{$key}}"
                                                       name="enable_for[]"
                                                       @checked(in_array($key,$enable_for))
                                                >
                                                <label class="form-check-label" for="{{ $key.'-'.$value }}">
                                                    {{ str_headline($key) }}</label>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if(!$hasActiveServices)
                                    <div class="col-12 text-center p-4">
                                        <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="..." data-hs-theme-appearance="default">
                                        <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="..." data-hs-theme-appearance="dark">
                                        <p class="mb-0">@lang("No active service available")</p>
                                    </div>
                                @endif
                            </div>
                            @if(isset($twoFactorSetting->security_pin))
                                <div class="form-group mt-3 security-block">
                                    <label class="form-label" for="security_pin">@lang('Security Pin')</label>
                                    <input type="password" name="security_pin"
                                           placeholder="@lang('Please enter your security PIN')" autocomplete="off"
                                           value="{{ old('security_pin') }}"
                                           class="form-control @error('security_pin') is-invalid @enderror">
                                    <div class="invalid-feedback">
                                        @error('security_pin') @lang($message) @enderror
                                    </div>
                                    <div class="valid-feedback"></div>
                                </div>
                            @endif
                            <button type="submit" id="submit"
                                    class="btn btn-primary btn-sm mt-3">@lang('Save Changes')</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>



@endsection
