@extends(userLayout())
@section('title',__('Attribute Create'))

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Attribute Create')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Attribute Create')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Attribute Create')</h4>
                    </div>
                    <form action="{{ route('user.attr.create') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="Attribute Name">@lang('Attribute Name')</label>
                                        <input type="text" value="{{ old('name') }}"
                                               name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               autocomplete="off" required>
                                        <div class="invalid-feedback">
                                            @error('name') @lang($message) @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="row form-check form-switch my-4"
                                           for="instant_delivery">
                                            <span class="col-8 col-sm-9 ms-0">
                                              <span class="d-block text-dark">@lang("Status")</span>
                                              <span
                                                  class="d-block fs-5">@lang("Enable Status to operation the Attribute")</span>
                                            </span>
                                        <span class="col-4 col-sm-3 text-end">
                                                    <input type="hidden" value="0" name="status"/>
                                                    <input
                                                        class="form-check-input editStatus @error('status') is-invalid @enderror"
                                                        type="checkbox" name="status"
                                                        id="status" value="1"
                                                    >
                                                </span>
                                        @error('status')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <button type="button"
                                            class="btn btn-success btn-sm float-right" id="generate"><i
                                            class="fa fa-plus-circle"></i>
                                        @lang('Add Field')</button>
                                </div>
                            </div>
                            <div class="row addedField mt-3">
                            </div>
                            <button type="submit"
                                    class="btn btn-primary mt-3">@lang('Create Attribute')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';
        $(document).on('click', '#generate', function () {
            var form = `<div class="col-md-6  mt-2">
                                <div class="form-group">
                                    <div class="input-group">
                                        <input name="field_name[]" class="form-control " type="text" value="" required placeholder="{{trans('Attribute Info')}}">
                                        <span class="input-group-btn">
                                            <button class="btn btn-danger delete_desc" type="button">
                                                <i class="fa fa-times"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div> `;

            $('.addedField').append(form)
        });

        $(document).on('click', '.delete_desc', function () {
            $(this).closest('.input-group').parent().remove();
        });
    </script>
@endpush

