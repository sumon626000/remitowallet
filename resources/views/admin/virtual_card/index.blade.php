@extends('admin.layouts.app')
@section('page_title',__('Card Providers'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0);">@lang('Dashboard')</a></li>
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="javascript:void(0);">@lang('Card Providers')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@yield('page_title')</h1>
                </div>
            </div>
        </div>
        <div class="content container-fluid">
            <div class="row align-items-center mb-5">
                <div class="col">
                    <h3 class="mb-0">{{count($virtualCardMethods)}} @lang('Providers')</h3>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3">
                @if(!empty($virtualCardMethods))
                    @foreach($virtualCardMethods as $method)
                        <div class="col mb-3 mb-lg-5">
                            <!-- Card -->
                            <div class="card h-100">
                                <div class="card-pinned">
                                    <div class="card-pinned-top-end">
                                        <!-- Dropdown -->
                                        <div class="dropdown">
                                            <button type="button"
                                                    class="btn btn-ghost-secondary btn-icon btn-sm rounded-circle"
                                                    id="connectionsDropdown2" data-bs-toggle="dropdown"
                                                    aria-expanded="false">
                                                <i class="bi-three-dots-vertical"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-sm dropdown-menu-end"
                                                 aria-labelledby="connectionsDropdown2">
                                                <a class="dropdown-item"
                                                   href="{{ route('admin.virtual.cardStatusCng', $method->id) }}">@lang('Make Active')</a>
                                            </div>
                                        </div>
                                        <!-- End Dropdown -->
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl avatar-circle avatar-centered mb-3">
                                        <img class="avatar-img" src="{{getFile($method->driver,$method->image)}}"
                                             alt="{{$method->name}}">
                                        @if($method->status)
                                            <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                        @endif
                                    </div>
                                    <h3 class="mb-1">
                                        <a class="text-dark" href="#">{{$method->name}}</a>
                                    </h3>

                                    <div class="mb-3">
                                        <i class="bi-building me-1"></i>
                                        <span>@lang('Providers')</span>
                                    </div>
                                    <ul class="list-inline mb-0">
                                        <li class="list-inline-item"><a
                                                class="badge bg-soft-secondary text-secondary p-2"
                                                href="#">{{$method->debit_currency}}</a></li>
                                    </ul>
                                </div>
                                <div class="card-footer">
                                    <div class="row justify-content-between align-items-center">
                                        <div class="col-auto py-1">
                                            <a class="fs-6 text-body"
                                               href="javascript:void(0)">{{count($method->currency)}} @lang('currencies')</a>
                                        </div>

                                        <div class="col-auto py-1">
                                            <a class="btn btn-outline-primary"
                                               href="{{ route('admin.virtual.cardEdit', $method->id) }}"><i class="fa-light fa-edit"></i> @lang('Edit')</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
@endsection


@push('css-lib')
@endpush

@push('js-lib')
@endpush

@push('script')
    <script>
        'use strict';
        (function () {
            HSCore.components.HSTomSelect.init('.js-select', {
                maxOptions: 500
            })
        })();
    </script>
@endpush
