@extends('admin.layouts.app')
@section('page_title', __('Manage Theme'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Themes Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Manage Theme')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary m-0 m-md-4 my-4 m-md-0 shadow">
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            @foreach($allThemes as $key => $theme)
                                <div class="col-sm-6 col-lg-6 mb-3 mb-lg-5 themeImageCol">
                                    <div class="select-theme">
                                        <label class="form-control" for="formControlRadio{{$key}}">
                                            <span class="form-check">
                                                <input type="radio" class="form-check-input" name="theme"
                                                       data-theme_name="{{$theme['name']}}" value="{{$key}}"
                                                       id="formControlRadio{{$key}}" @checked(basicControl()->theme == $key)>
                                                <img class="img-fluid w-100 themeImage"
                                                     src="{{ asset($theme['preview_link']) }}"
                                                     alt="...">
                                            </span>
                                        </label>
                                    </div>
                                    <div class="text-center">
                                        <h5 class="mb-0 bg-warning p-3">@lang($theme['name'])</h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card card-primary m-0 m-md-4 my-4 m-md-0 shadow">
                    <div class="card-header">
                        <h4>@lang('Manage Default User Layout')</h4>
                    </div>
                    <div class="card-body">
                        <div class="row d-flex justify-content-center">
                            @foreach(['horizontal','vertical'] as $key => $layout)
                                <div class="col-sm-6 col-lg-6 mb-3 mb-lg-5 themeImageCol">
                                    <div class="select-theme">
                                        <label class="form-control" for="formControlRadio{{$key}}">
                                            <span class="form-check">
                                                <input type="radio" class="form-check-input userLayout" name="user_layout"
                                                       data-layout="{{$layout}}" value="{{$key}}"
                                                       id="formControlRadio{{$key}}"
                                                    @checked(config('basic.default_user_layout') == $layout)>
                                                <img class="img-fluid layout-img"
                                                     src="{{ asset("assets/user/img/{$layout}_layout.webp") }}"
                                                     alt="...">
                                            </span>
                                        </label>
                                    </div>
                                    <div class="text-center">
                                        <h5 class="mb-0 bg-warning p-3">{{ ucfirst($layout) }}</h5>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('css')
    <style>
        .layout-img{
            max-height: 320px;
        }
    </style>
@endpush

@push('script')
    <script>
        "use strict";

        document.addEventListener("DOMContentLoaded", function () {
            let isDemo = "{{ config('demo.IS_DEMO') }}";

            function notify(type, message) {
                Notiflix.Notify[type](message);
            }

            function sendRequest(url, params) {
                var query = Object.keys(params).map(function (key) {
                    return encodeURIComponent(key) + '=' + encodeURIComponent(params[key]);
                }).join('&');

                fetch(url + '?' + query)
                    .then(function (response) {
                        return response.json();
                    })
                    .then(function (data) {
                        if (data.message) {
                            notify("success", data.message);
                        }
                    })
                    .catch(function (error) {
                        console.error("Error:", error);
                        notify("failure", "Something went wrong.");
                    });
            }

            let themeInputs = document.querySelectorAll('input[name="theme"]');
            for (var i = 0; i < themeInputs.length; i++) {
                themeInputs[i].addEventListener("change", function () {
                    if (this.checked) {
                        if (isDemo) {
                            notify("info", "This is a demo version, you can't change theme.");
                            return;
                        }
                        sendRequest("{{ route('admin.themeUpdate') }}", {
                            theme: this.value,
                            theme_name: this.getAttribute("data-theme_name")
                        });
                    }
                });
            }

            let layoutInputs = document.querySelectorAll('input[name="user_layout"]');
            for (var j = 0; j < layoutInputs.length; j++) {
                layoutInputs[j].addEventListener("change", function () {
                    if (this.checked) {
                        if (isDemo) {
                            notify("info", "This is a demo version, you can't change layout.");
                            return;
                        }
                        sendRequest("{{ route('admin.userLayoutUpdate') }}", {
                            user_layout: this.getAttribute("data-layout")
                        });
                    }
                });
            }
        });
    </script>
@endpush








