@extends('admin.layouts.app')
@section('page_title', __('Addon Manager'))
@section('content')
    <div class="content container-fluid">
        <x-page-header menu="Settings Panel"/>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-7">
                <div class="d-grid gap-3 gap-lg-5">
                    <div id="socialAccountsSection" class="card">
                        <div class="card-header">
                            <h4 class="card-title">@lang('Addon Manager')</h4>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-lg list-group-flush list-group-no-gutters">

                                @foreach($modules as $module)
                                    <div class="list-group-item">
                                        <div class="d-flex">
                                            <div class="flex-shrink-0 icon icon-md icon-soft-primary">
                                                <i class="{{ $module['icon'] }} fs-2"></i>
                                            </div>
                                            <div class="flex-grow-1 ms-3">
                                                <div class="row align-items-center">
                                                    <div class="col-sm mb-2 mb-sm-0">
                                                        <h4 class="mb-0">
                                                            {{ $module['name'] }}
                                                            @if($module['enabled'])
                                                                <span class="badge bg-soft-success text-success">@lang('Enabled')</span>
                                                            @else
                                                                <span class="badge bg-soft-danger text-danger">@lang('Disabled')</span>
                                                            @endif
                                                        </h4>
                                                        <p class="fs-5 text-body mb-0">{{ $module['description'] }}</p>

                                                        @if($module['enabled'])
                                                            <div class="flex-grow-1 mt-2">
                                                                <span class="fw-semibold me-1">Configure {{ $module['name'] }} settings</span>
                                                                <a class="link" href="{{ $module['route_link'] }}">Click here</a>
                                                            </div>
                                                        @endif

                                                    </div>
                                                    <div class="col-sm-auto">
                                                        <form method="POST"
                                                              action="{{ route('admin.addon.toggle', $module['name']) }}">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="btn btn-sm {{ $module['enabled'] ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                                {{ $module['enabled'] ? 'Deactivate' : 'Activate' }}
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection
