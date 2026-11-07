@extends('admin.layouts.app')
@section('page_title', __('Wallet Summary'))
@section('content')

<div class="bg-dark">
    <div class="content container-fluid dashboard-height mb-2">

        <div class="page-header page-header-light">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item breadcrumb-link" >@lang('Wallet')</li>
                            <li class="breadcrumb-item breadcrumb-link" >@lang($code??'')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@yield('page_title')</h1>
                </div>
            </div>
        </div>

        @include('admin.transaction.wallet_trx_graph')

        <div class="row mb-3 pb-5">
            @include('admin.transaction.wallet_trx_list')
            @include('admin.transaction.wallet_overview')
        </div>

    </div>
</div>
@endsection


















