@extends(userLayout())
@section('title', trans('Request New Card'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Request New Card')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Request New Card')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">{{$virtualCardMethod->name}} @lang('Card')</h4>
                        <p class="mt-2">{{$virtualCardMethod->info_box}}</p>
                    </div>
                    <form action="{{route('user.virtual.card.orderSubmit')}}" method="post"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">
                            @if($virtualCardMethod)
                                <div class="row mb-3">
                                    <div class="col-md-12">
                                        <label for="Currency" class="form-label">@lang('Card Currency')</label>
                                        <div class="tom-select-custom">
                                            <select class="js-select form-select" autocomplete="off" name="currency"
                                                    id="currency"
                                                    data-hs-tom-select-options='{
                                            "placeholder": "Select Currency..."
                                          }'>
                                                @foreach($virtualCardMethod->currency as $singleCurrency)
                                                    <option value="{{ $singleCurrency }}">{{$singleCurrency}}</option>
                                                @endforeach
                                            </select>
                                            @error('currency')
                                            <span class="text-danger">{{$message}}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            @endif
                            <div class="row mb-4">
                                @if(isset($virtualCardMethod->form_field))
                                    @forelse($virtualCardMethod->form_field as $k => $v)
                                        @if ($v->type == 'text' || $v->type == 'email' || $v->type == 'date')
                                            <div class="col-md-6 mb-3">
                                                <label for="{{ trans($v->field_level) }}"
                                                       class="form-label">{{ trans($v->field_level) }}
                                                    @if ($v->validation != 'required')
                                                        (@lang('optional'))
                                                    @endif
                                                </label>
                                                <input type="{{$v->type}}" class="form-control" name="{{ $k }}"
                                                       value="{{old($k)}}" @if ($v->validation == 'required')  @endif>
                                                @error($k)
                                                <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        @elseif($v->type == 'textarea')
                                            <div class="col-md-6 mb-3">
                                                <label for="{{ trans($v->field_level) }}"
                                                       class="form-label">{{ trans($v->field_level) }}
                                                    @if ($v->validation != 'required')
                                                        (@lang('optional'))
                                                    @endif
                                                </label>
                                                <textarea class="form-control" name="{{ $k }}"
                                                           @if ($v->validation == 'required') @endif>{{old($k)}}</textarea>
                                                @error($k)
                                                <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        @elseif($v->type == 'file')
                                            <div class="col-md-6 mb-3">
                                                <label for="basicFormFile" class="js-file-attach form-label"
                                                       data-hs-file-attach-options='{
                                                  "textTarget": "[for=\"customFile\"]"
                                                 }'>{{ trans($v->field_level) }} @if ($v->validation != 'required')
                                                        (@lang('optional'))
                                                    @endif</label>
                                                <input class="form-control" type="file" id="basicFormFile"
                                                       name="{{$k}}" @if ($v->validation == 'required')   @endif>
                                                @error($k)
                                                <span class="text-danger">{{$message}}</span>
                                                @enderror
                                            </div>
                                        @endif
                                    @empty
                                    @endforelse
                                @endif
                            </div>

                            <button id="submit" type="submit"
                                    class="btn btn-primary">@lang('Apply Now')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('css-lib')
    <script src="{{ asset("assets/admin/js/hs-file-attach.min.js") }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        (function () {
            HSCore.components.HSTomSelect.init('.js-select')
        })();
    </script>
@endpush
