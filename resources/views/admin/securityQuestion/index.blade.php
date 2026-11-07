@extends('admin.layouts.app')
@section('page_title', __('Security Questions'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0)">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Settings')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Security Questions')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Security Questions')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <h2 class="card-title h4 mt-3">@lang("Languages")</h2>
                            <a href="{{ route('admin.securityQuestion.create') }}"
                               class="btn btn-primary btn-sm">@lang("Add New")
                            </a>
                        </div>
                        <div class="table-responsive position-relative">
                            <table
                                class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                id="supported_currency_table">
                                <thead class="thead-light">
                                <tr>
                                    <th>@lang('Question')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($securityQuestions as $key => $securityQuestion)
                                    <tr>
                                        <td>{{ __($securityQuestion->question) }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a class="btn btn-white btn-sm"
                                                   href="{{ route('admin.securityQuestion.edit', $securityQuestion) }}">
                                                    <i class="bi-pencil-square me-1"></i> @lang('Edit')
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <div class="text-center p-4">
                                            <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                                            <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                                            <p class="mb-0">@lang("No data to show")</p>
                                        </div>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

