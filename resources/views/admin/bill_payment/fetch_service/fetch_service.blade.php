@extends('admin.layouts.app')
@section('page_title',__('Service List'))

@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <h1 class="page-header-title">@yield('page_title')</h1>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.dashboard') }}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Bill Provider')</li>
                            <li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="card-title m-0">@lang('Service List')</h4>
                <a href="#"
                   class="btn btn-primary bulk-yes"><i class="bi-plus"></i>@lang('Bulk Add')</a>
            </div>

            <div class="table-responsive">
                <table class="table table-thead-bordered table-nowrap table-align-middle card-table">
                    <thead class="thead-light">
                    @include('admin.bill_payment.fetch_service.dynamic.'.$billMethod->code)
                </table>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict'
        var api_service = '{{$api_service}}'
        var methodId = '{{$billMethod->id}}'
        $(document).on('click', '.bulk-yes', function (e) {
            Notiflix.Loading.standard('Please wait, Its take few time');
            e.preventDefault();
            var allVals = [];
            $(".row-tic:checked").each(function () {
                allVals.push($(this).attr('data-id'));
            });

            var res = allVals;

            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                url: "{{ route('admin.bill.add.bulk.service') }}",
                data: {res: res, api_service: api_service, methodId: methodId},
                dataType: 'json',
                type: "post",
                success: function (data) {
                    Notiflix.Loading.remove();
                    if (data.status == 'success') {
                        window.location.href = data.route;
                    }
                    if (data.status == 'error') {
                        Notiflix.Loading.remove();
                        Notiflix.Notify.failure("You are not selected any value");
                    }
                },
            });
        });

        $(document).on('click', '#singleAdd', function () {
            Notiflix.Loading.standard('Please wait, Its take few time');
            var res = $(this).data('resource');
            var _this = this;

            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                url: "{{ route('admin.bill.add.service') }}",
                data: {res: res, api_service: api_service, methodId: methodId},
                dataType: 'json',
                type: "post",
                success: function (data) {
                    Notiflix.Loading.remove();
                    if (data.status == 'success') {
                        $(_this).attr('class', 'btn btn-sm btn-success')
                        $(_this).prop("disabled", true)
                        $(_this).text('Added')
                        Notiflix.Notify.success("Successfully Added");
                    }
                    if (data.status == 'error') {
                        Notiflix.Loading.remove();
                        Notiflix.Notify.failure(data.msg);
                    }
                },
                error:function (data){
                    Notiflix.Loading.remove();
                }
            });
        });

        $(document).on('click', '#check-all', function () {
            $('input:checkbox').not(this).prop('checked', this.checked);
        });

        $(document).on('change', ".row-tic", function () {
            let length = $(".row-tic").length;
            let checkedLength = $(".row-tic:checked").length;
            if (length == checkedLength) {
                $('#check-all').prop('checked', true);
            } else {
                $('#check-all').prop('checked', false);
            }
        });
    </script>
@endpush
