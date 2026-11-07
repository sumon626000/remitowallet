@extends('admin.layouts.app')
@section('page_title',__('Bill Providers'))
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
                                                           href="javascript:void(0);">@lang('Bill Providers')</a>
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
                    <h3 class="mb-0">{{count($billMethods)}} @lang('Providers')</h3>
                </div>
            </div>
            <div class="row row-cols-1 row-cols-sm-2 row-cols-xl-3">
                @if(!empty($billMethods))
                    @foreach($billMethods as $method)
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
                                                   href="{{route('admin.bill.method.activated',$method->id)}}">@lang('Make Active')</a>
                                                @if($method->code == 'flutterwave')
                                                    <a class="dropdown-item" data-bs-toggle="modal"
                                                       data-bs-target="#selectCurrency"
                                                       data-name="{{$method->methodName}}"
                                                       data-code="{{$method->code}}"
                                                       href="#">@lang('Balance')</a>
                                                @else
                                                    <a class="dropdown-item showAmount" data-bs-toggle="modal"
                                                       data-bs-target="#showAmount"
                                                       data-name="{{$method->methodName}}"
                                                       data-code="{{$method->code}}"
                                                       href="#">@lang('Balance')</a>
                                                @endif
                                            </div>
                                        </div>
                                        <!-- End Dropdown -->
                                    </div>
                                </div>
                                <div class="card-body text-center">
                                    <div class="avatar avatar-xl avatar-circle avatar-centered mb-3">
                                        <img class="avatar-img" src="{{getFile($method->driver,$method->logo)}}"
                                             alt="{{$method->methodName}}">
                                        @if($method->is_active)
                                            <span class="avatar-status avatar-sm-status avatar-status-success"></span>
                                        @endif
                                    </div>
                                    <h3 class="mb-1">
                                        <a class="text-dark" href="#">{{$method->methodName}}</a>
                                    </h3>

                                    <div class="mb-3">
                                        <i class="bi-building me-1"></i>
                                        <span>@lang('Providers')</span>
                                    </div>
                                </div>
                                <div class="card-footer">
                                    <div class="row justify-content-between align-items-center">
                                        <div class="col-auto py-1">
                                            <a class="fs-6 text-body"
                                               href="javascript:void(0)">{{$method->convert_rate ? count((array) $method->convert_rate) : 0}} @lang('currencies')</a>
                                        </div>

                                        <div class="col-auto py-1">
                                            <a class="btn btn-outline-primary"
                                               href="{{ route('admin.bill.method.edit',$method->id) }}"><i
                                                    class="fa-light fa-edit"></i> @lang('Edit')</a>
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
    <div id="showAmount" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold titleShow"
                        id="primary-header-modalLabel"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-center bouncing-loader d-none">
                        <div class="spinner-border" style="width: 3rem; height: 3rem;" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                    <h1 class="text-center" id="nbr"></h1>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

    <div id="selectCurrency" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold titleShow"
                        id="primary-header-modalLabel"></h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="tom-select-custom tom-select-custom-with-tags">
                        <select class="js-select form-select currencySelect"
                                name="selectCurrency" autocomplete="off" required>
                            <option value="AOA">AOA</option>
                            <option value="AUD">AUD</option>
                            <option value="BRL">BRL</option>
                            <option value="BRL">CAD</option>
                            <option value="EGP">EGP</option>
                            <option value="ETB">ETB</option>
                            <option value="EUR">EUR</option>
                            <option value="GBP">GBP</option>
                            <option value="GHS">GHS</option>
                            <option value="ILS">ILS</option>
                            <option value="INR">INR</option>
                            <option value="JPY">JPY</option>
                            <option value="KES">KES</option>
                            <option value="MAD">MAD</option>
                            <option value="MUR">MUR</option>
                            <option value="MWK">MWK</option>
                            <option value="MZN">MZN</option>
                            <option value="NGN">NGN</option>
                            <option value="RWF">RWF</option>
                            <option value="SLL">SLL</option>
                            <option value="TZS">TZS</option>
                            <option value="UGX">UGX</option>
                            <option value="USD">USD</option>
                            <option value="XAF">XAF</option>
                            <option value="XOF">XOF</option>
                            <option value="ZAR">ZAR</option>
                            <option value="ZMW">ZMW</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-soft-primary showAmount" data-name="Flutterwave"
                            data-code="flutterwave" data-bs-toggle="modal"
                            data-bs-target="#showAmount">@lang('Confirm')</button>
                </div>
            </div>
        </div>
    </div>
@endsection


@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        $(document).on("click", ".showAmount", function () {
            console.log("ok")
            $("#nbr").text('');
            $('.bouncing-loader').removeClass('d-none');
            let name = $(this).data('name');
            let code = $(this).data('code');
            $('.titleShow').text(`${name} Balance:`);
            let currencyCode = $('.currencySelect').find(':selected').val();
            getBalance(code, currencyCode);
        });

        function getBalance(code, currencyCode = null) {
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                url: "{{ route('admin.fetch.balance') }}",
                data: {code: code, currencyCode: currencyCode},
                dataType: 'json',
                type: "post",
                success: function (data) {
                    if (data.status == 'success') {
                        $('.bouncing-loader').addClass('d-none');
                        $("#nbr").text(`${data.balance} ${data.currencyCode}`)
                    }
                }
            });
        }

        HSCore.components.HSTomSelect.init('.js-select', {
            maxOptions: 500
        })
    </script>
@endpush
