@extends(userLayout())
@section('title',__('Product Stock Create'))

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Store')</li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Product Stock')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Product Stock Create')</h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Product Stock Create')</h4>
                    </div>
                    <form action="{{ route('user.stock.create') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select productSelect" autocomplete="off"
                                                name="product"
                                                id="product">
                                            <option>@lang('Select Product')</option>
                                            @if(!empty($products))
                                                @foreach($products as $key => $product)
                                                    <option
                                                        value="{{ $product->id }}" {{old('product') == $product->id ? 'selected':''}}>
                                                        {{ __($product->name) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('product')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row addedField">

                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-3">@lang('Create Stock')</button>
                        </div>
                    </form>
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
        var productId;

            $(document).on('change', '#product', function () {
                productId = $(this).val();
                attrCollect(productId);
            });

            $(document).on('click', '.delete_desc', function () {
                $(this).closest('.input-group').parent().remove();
            });

            function attrCollect(productId) {
                $('.addedField').html('');
                $.ajax({
                    url: "{{ route('user.stock.attr.fetch') }}",
                    method: "get",
                    data: {
                        productId: productId,
                    },
                    success: function (res) {
                        if (res.status == 'success') {
                            attrAppend(res.data);
                        }
                    }
                });
            }

            function attrAppend(data) {
                $('.addedField').append(data)
            }


            $(document).on('click', '.removeContentDiv', function () {
                var sss = $(this).closest('.column-form').remove();
            });


            $(document).on('click', '.copyFormData', function () {
                var sss = $(this).closest('.column-form').clone();
                var $len = parseInt($('.column-form').length);

                $(sss).find('.attrId').attr('name', 'attrName[' + $len + '][]');
                $(".addedField").append(sss);
                sss.find('.removeContentDiv').css('display', 'initial')
                sss.find('.copyFormData').css('display', 'none')
            })

            $(document).on('click', '.removeFormData', function () {
                var sss = $(this).closest('.row').remove();
            });


        (function () {
            HSCore.components.HSTomSelect.init('.js-select')
        })();
    </script>
@endpush

