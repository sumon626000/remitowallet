@extends(userLayout())
@section('title',__('Product Update'))

@section('content')
    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item"><a class="breadcrumb-link"
                                                           href="{{route('user.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Product Update')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Product Update')</h1>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Product Update')</h4>
                    </div>
                    <form action="{{ route('user.product.edit',$product->id) }}" method="post"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Store">@lang('Store') <sup
                                                class="text-danger">*</sup></label>
                                        <div class="tom-select-custom tom-select-custom-with-tags">
                                            <select class="js-select form-select"
                                                    name="store[]" autocomplete="off"
                                                    multiple
                                                    data-hs-tom-select-options='{
                                                        "placeholder": "Select Store",
                                                      }' required>
                                                @forelse($stores as $store)
                                                    <option
                                                        value="{{$store->id}}" @foreach($product->productStores as $productStore)
                                                        {{$productStore->store_id == $store->id ? 'selected':''}}
                                                        @endforeach>{{$store->name}}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </div>
                                        <div class="invalid-feedback mt-3">
                                            @error('store') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Category">@lang('Category') <sup
                                                class="text-danger">*</sup></label>
                                        <select name="category"
                                                class="form-control form-control-sm @error('category') is-invalid @enderror"
                                                required>
                                            <option disabled selected
                                                    value="">@lang('Select Category')</option>
                                            @forelse($categories as $category)
                                                <option
                                                    value="{{$category->id}}" {{$product->category_id == $category->id ? 'selected':''}}>{{$category->name}}</option>
                                            @empty
                                            @endforelse
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('category') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Attribute Name">@lang('Product Name') <sup
                                                class="text-danger">*</sup></label>
                                        <input type="text" value="{{ $product->name }}"
                                               name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               autocomplete="off" required>
                                        <div class="invalid-feedback">
                                            @error('name') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Price">@lang('Price') <sup
                                                class="text-danger">*</sup></label>
                                        <div class="input-group">
                                            <input type="text" value="{{ $product->price }}"
                                                   name="price"
                                                   class="form-control @error('price') is-invalid @enderror"
                                                   autocomplete="off" required>
                                            <div class="input-group-prepend">
                                                        <span
                                                            class="form-control currencyCode">{{optional(auth()->user()->storeCurrency)->code}}</span>
                                            </div>
                                            <div class="invalid-feedback">
                                                @error('price') @lang($message) @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Attribute Name">@lang('Attribute Name')</label>
                                        <div class="tom-select-custom tom-select-custom-with-tags">
                                            <select class="js-select form-select"
                                                    name="attribute[]" autocomplete="off"
                                                    multiple
                                                    data-hs-tom-select-options='{
                                                        "placeholder": "Select Attribute",
                                                      }' required>
                                                @forelse($productsAttrs as $productsAttr)
                                                    <option value="{{$productsAttr->id}}"
                                                    @foreach($product->productAttrs as $productAttr)
                                                        {{$productAttr->attributes_id == $productsAttr->id ? 'selected':''}}
                                                        @endforeach>{{$productsAttr->name}}</option>
                                                @empty
                                                @endforelse
                                            </select>
                                        </div>
                                        <div class="invalid-feedback mt-3">
                                            @error('attributes') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="SKU">@lang('SKU') <sup
                                                class="text-danger">*</sup></label>
                                        <input type="text" value="{{ $product->sku }}"
                                               name="sku"
                                               class="form-control @error('sku') is-invalid @enderror"
                                               autocomplete="off" required>
                                        <div class="invalid-feedback">
                                            @error('sku') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Tag">@lang('Tag')</label>
                                        <input type="text" value="{{ $product->tag }}"
                                               name="tag"
                                               class="form-control @error('tag') is-invalid @enderror"
                                               autocomplete="off" required>
                                        <div class="invalid-feedback">
                                            @error('tag') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-3">

                                    <div class="list-group-item">
                                        <div class="d-flex">
                                            <div class="flex-grow-1">
                                                <div class="row align-items-center">
                                                    <div class="col">
                                                        <span class="d-block text-dark">@lang("Status")</span>
                                                        <p class="fs-5 text-body mb-0">@lang("Product show on shop page")</p>
                                                    </div>
                                                    <div class="col-auto">
                                                        <div class="form-check form-switch">
                                                            <input type="hidden" name="status" value="0">
                                                            <input type="checkbox" class="form-check-input"
                                                                   name="status"
                                                                   id="isActiveSwitch"
                                                                   value="1" {{$product->status == 1 ? 'checked':''}}>
                                                            <label class="form-check-label"
                                                                   for="isActiveSwitch"></label>
                                                        </div>
                                                        <span class="invalid-feedback">
                                                                    @error('status') @lang($message) @enderror
                                                                </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-4 mt-4">
                                    <button type="button"
                                            class="btn btn-success btn-sm float-right" id="generate"><i
                                            class="fa fa-plus-circle"></i>
                                        @lang('Add Product Image')</button>
                                </div>
                            </div>
                            <div class="row addedField">
                                <div class="col-md-3">
                                    <label for="Status">@lang('Thumbnail Image')</label>
                                    <label class="form-check form-check-dashed" for="logoUploader">
                                        <img id="logoImg"
                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                             src="{{ getFile($product->driver,$product->thumbnail) }}"
                                             alt="Image Description" data-hs-theme-appearance="default">
                                        <img id="logoImg"
                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                             src="{{ getFile($product->driver,$product->thumbnail) }}"
                                             alt="Image Description" data-hs-theme-appearance="dark">
                                        <span class="d-block">@lang("Browse your file here")</span>
                                        <input type="file" class="js-file-attach form-check-input" name="thumbnail"
                                               id="logoUploader" data-hs-file-attach-options='{
                                                      "textTarget": "#logoImg",
                                                      "mode": "image",
                                                      "targetAttr": "src",
                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                   }'>
                                    </label>
                                    <h5>@lang('Image size should be ') {{config('filelocation.product.thumbnail')}} @lang('for better resolution')</h5>
                                    @error('thumbnail')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                @forelse($product->productImages as $image)
                                    <div class="col-md-3">
                                        <label for="Status">@lang('Thumbnail Image')</label>
                                        <label class="form-check form-check-dashed" for="logoUploader">
                                            <img id="logoImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                 src="{{ getFile($image->driver,$image->image) }}"
                                                 alt="Image Description" data-hs-theme-appearance="default">
                                            <img id="logoImg"
                                                 class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                                 src="{{ getFile($image->driver,$image->image) }}"
                                                 alt="Image Description" data-hs-theme-appearance="dark">
                                            <span class="d-block">@lang("Browse your file here")</span>
                                            <button class="btn btn-white removeFile z9 deleteImage" type="button"
                                                    data-bs-target="#productImageDelete"
                                                    data-bs-toggle="modal"
                                                    data-route="{{route('user.product.image.delete',$image->id)}}">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            <input type="file" class="js-file-attach form-check-input" name="thumbnail"
                                                   id="logoUploader" data-hs-file-attach-options='{
                                                      "textTarget": "#logoImg",
                                                      "mode": "image",
                                                      "targetAttr": "src",
                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                   }'>
                                        </label>
                                        <h5>@lang('Image size should be ') {{config('filelocation.product.thumbnail')}} @lang('for better resolution')</h5>
                                        @error('thumbnail')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @empty
                                @endforelse
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label
                                            for="Description">@lang('Description')</label>
                                        <textarea class="summernote"
                                                  name="description">{{$product->description}}</textarea>
                                        <div class="invalid-feedback">
                                            @error('description') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label
                                            for="Description">@lang('Instruction')</label>
                                        <textarea
                                            class="summernote"
                                            name="instruction"
                                            rows="5">{{$product->instruction}}</textarea>
                                        <div class="invalid-feedback">
                                            @error('description') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-3">@lang('Update Product')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @include('user.store.product.modal')
@endsection
@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/summernote-bs5.min.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/summernote-bs5.min.js') }}"></script>
    <script src="{{ asset("assets/admin/js/hs-file-attach.min.js") }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        $(document).ready(function () {
            $('.summernote').summernote({
                height: 200,
            });

            new HSFileAttach('.js-file-attach')
            $('.js-select').each(function () {
                new TomSelect(this, {
                    plugins: {
                        remove_button: {
                            title: '',
                        },
                    },
                    create: true,
                });
            });
        });

        $(document).on('click', '.deleteImage', function () {
            var route = $(this).data('route');
            $('.deleteProductImageForm').attr('action', route)
        })

        let counter = 0;

        $("#generate").on('click', function () {
            counter++;
            var form = `<div class="col-md-3">
                                    <label for="Status">@lang('Image')</label>
                                    <label class="form-check form-check-dashed" for="logoUploader_${counter}">
                                        <img id="logoImg_${counter}"
                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                             src="{{ asset('assets/admin/img/oc-browse-file.svg') }}"
                                             alt="Image Description" data-hs-theme-appearance="default">
                                        <img id="logoImg_${counter}"
                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                             src="{{ asset('assets/admin/img/oc-browse-file-light.svg') }}"
                                             alt="Image Description" data-hs-theme-appearance="dark">
                                        <span class="d-block">@lang("Browse your file here")</span>
                                        <button class="btn btn-white delete_desc removeFile z9" type="button">
                                                <i class="fa fa-trash"></i>
                                        </button>
                                        <input type="file" class="js-file-attach form-check-input" name="image[]"
                                               id="logoUploader_${counter}" data-hs-file-attach-options='{
                                                      "textTarget": "#logoImg_${counter}",
                                                      "mode": "image",
                                                      "targetAttr": "src",
                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                   }'>
                                    </label>
                                    <h5>@lang('Image size should be ') {{config('filelocation.product.size')}} @lang('for better resolution')</h5>
                                    @error('thumbnail')
            <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
            </div>`;
            $('.addedField').append(form)
            new HSFileAttach('.js-file-attach')

            $(document).on('click', '.delete_desc', function () {
                $(this).closest('.form-check').parents('.col-md-3').remove();
            });
        });

    </script>
@endpush

