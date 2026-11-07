@extends(userLayout())
@section('title',__('Store Create'))

@section('content')
    <div class="content {{ containerClass() }}" id="storeCreate">
        <div class="page-header">
            <div class="row align-items-center">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Store')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Store Create')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Store Create')</h4>
                    </div>
                    <form action="{{ route('user.store.create') }}" method="post"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            <div class="row mb-4">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Store Name">@lang('Store Name') </label>
                                        <input type="text"
                                               name="name"
                                               value="{{ old('name') }}"
                                               class="form-control @error('name') is-invalid @enderror"
                                               autocomplete="off" required>
                                        <div class="invalid-feedback">
                                            @error('name') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Shipping Charge">@lang('Shipping Charge')</label>
                                        <select name="shipping_charge"
                                                class="form-select">
                                            <option
                                                value="1" {{old('shipping_charge') == '1' ? 'selected':''}}>@lang('Active')</option>
                                            <option
                                                value="0" {{old('shipping_charge') == '0' ? 'selected':''}}>@lang('Inactive')</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('shipping_charge') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Delivery Note">@lang('Delivery Note')</label>
                                        <select name="delivery_note"
                                                class="form-select">
                                            <option
                                                value="disabled" {{old('delivery_note') == 'disabled' ? 'selected':''}}>@lang('Disable')</option>
                                            <option
                                                value="enable" {{old('delivery_note') == 'enable' ? 'selected':''}}>@lang('Enable')</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('delivery_note') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-8">
                                    <label for="Image">@lang('Store Link')</label>
                                    <div class="input-group">
                                        <input type="text" value="{{route('public.view')}}"
                                               class="form-control" readonly>
                                        <div class="input-group-prepend width-50">
                                            <input type="text" v-on:keyup="storeLinkCheck" name="link"
                                                   value=""
                                                   class="form-control">
                                        </div>
                                    </div>
                                    <span class="text-danger float-right">@{{ msg }}</span>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="Status">@lang('Status')</label>
                                        <select name="status"
                                                class="form-select">
                                            <option
                                                value="1" {{old('status') == '1' ? 'selected':''}}>@lang('Active')</option>
                                            <option
                                                value="0" {{old('status') == '0' ? 'selected':''}}>@lang('Inactive')</option>
                                        </select>
                                        <div class="invalid-feedback">
                                            @error('status') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="Status">@lang('Store Image')</label>
                                    <label class="form-check form-check-dashed" for="logoUploader">
                                        <img id="logoImg"
                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                             src="{{ asset('assets/admin/img/oc-browse-file.svg') }}"
                                             alt="Image Description" data-hs-theme-appearance="default">
                                        <img id="logoImg"
                                             class="avatar avatar-xl avatar-4x3 avatar-centered h-100 mb-2"
                                             src="{{ asset('assets/admin/img/oc-browse-file-light.svg') }}"
                                             alt="Image Description" data-hs-theme-appearance="dark">
                                        <span class="d-block">@lang("Browse your file here")</span>
                                        <input type="file" class="js-file-attach form-check-input" name="image"
                                               id="logoUploader" data-hs-file-attach-options='{
                                                      "textTarget": "#logoImg",
                                                      "mode": "image",
                                                      "targetAttr": "src",
                                                      "allowTypes": [".png", ".jpeg", ".jpg"]
                                                   }'>
                                    </label>
                                    <h5>@lang('Image size should be ') {{config('filelocation.store.size')}} @lang('for better resolution')</h5>
                                    @error('image')
                                    <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="Store Description">@lang('Store Description')</label>
                                        <textarea name="short_description" rows="5"
                                                  class="form-control form-control-sm">{{old('short_description')}}</textarea>
                                    </div>
                                </div>
                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-3">@lang('Create store')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>
@endsection

<x-assets :tomselect="false" :flatpickr="false" :fileattach="true"/>

@push('script')

    <script>
        'use strict';
        var newApp = new Vue({
            el: "#storeCreate",
            data: {
                link: {},
                msg: ''
            },
            mounted() {
            },
            methods: {
                storeLinkCheck(link) {
                    let storeLink = this.link;
                    storeLink.link = link.target.value;
                    storeLink.storeId = '-1';
                    var _this = this;
                    axios.post("{{ route('user.store.link.check') }}", this.link)
                        .then(function (response) {
                            if (response.data.status == 'success') {
                                _this.msg = response.data.msg;
                            }
                            if (response.data.status == 'notFound') {
                                _this.msg = '';
                            }
                        })
                        .catch(function (error) {
                            let errors = error.response.data;
                        });
                },
            },
        })

    </script>
@endpush

