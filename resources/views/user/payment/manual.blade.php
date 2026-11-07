@extends(userLayout())
@section('title')
    {{ 'Pay with '.optional($deposit->gateway)->name ?? '' }}
@endsection

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Payment')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">{{ 'Pay with '.optional($deposit->gateway)->name ?? '' }}</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="card col-md-8 ">
                <div class="card-body">
                    <h3 class="title text-center">{{trans('Please follow the instruction below')}}</h3>
                    <p class="text-center mt-2 ">{{trans('You have requested to deposit')}} <b
                            class="text--base">{{getAmount($deposit->amount)}}
                            {{config('basic.base_currency')}}</b> , {{trans('Please pay')}}
                        <b class="text--base">{{getAmount($deposit->payable_amount)}} {{$deposit->payment_method_currency}}</b> {{trans('for successful payment')}}
                    </p>

                    <p class=" mt-2 ">
                        <?php echo optional($deposit->gateway)->note; ?>
                    </p>

                    <form action="{{ route('addFund.fromSubmit', $deposit->trx_id) }}" method="post"
                          enctype="multipart/form-data">
                        @csrf
                        @if(optional($deposit->gateway)->parameters)
                            @foreach($deposit->gateway->parameters as $k => $v)
                                @if($v->type == "text")
                                    <div class="mb-3">
                                        <label class="form-label">{{trans($v->field_label)}}
                                            @if($v->validation == 'required') <span class="text-danger">*</span> @endif
                                        </label>
                                        <input type="text" name="{{$k}}" class="form-control bg-transparent"
                                               @if($v->validation == "required") required @endif>
                                        @if ($errors->has($k))
                                            <span class="text-danger">{{ trans($errors->first($k)) }}</span>
                                        @endif
                                    </div>
                                @elseif($v->type == "number")
                                    <div class="mb-3">
                                        <label class="form-label">{{trans($v->field_label)}}
                                            @if($v->validation == 'required')<span class="text-danger">*</span> @endif
                                        </label>
                                        <input type="text" name="{{$k}}" class="form-control bg-transparent"
                                               @if($v->validation == "required") required @endif>
                                        @if ($errors->has($k))
                                            <span class="text-danger">{{ trans($errors->first($k)) }}</span>
                                        @endif
                                    </div>
                                @elseif($v->type == "textarea")
                                    <div class="mb-3">
                                        <label class="form-label">{{trans($v->field_label)}}
                                            @if($v->validation == 'required')<span class="text-danger">*</span> @endif
                                        </label>
                                        <textarea name="{{$k}}" class="form-control bg-transparent" rows="3"
                                                  @if($v->validation == "required") required @endif></textarea>
                                        @if ($errors->has($k))
                                            <span class="text-danger">{{ trans($errors->first($k)) }}</span>
                                        @endif
                                    </div>
                                @elseif($v->type == "file")
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">{{trans($v->field_label)}}
                                            @if($v->validation == 'required')<span class="text-danger">*</span> @endif
                                        </label>
                                        <span class="avatar avatar-xxl avatar-square d-block ">
                                      <img class="avatar  avatar-xxl " src="{{ asset('assets/admin/img/oc-browse-file.svg') }}"
                                           id="image_preview_container"
                                           alt="..">
                                    </span>
                                        <input type="file" name="{{$k}}" id="image" accept="image/*"
                                               class="form-control mt-0"
                                               @if($v->validation == "required") required @endif>
                                        @error($k)
                                        <span class="text-danger">@lang($message)</span>
                                        @enderror
                                    </div>
                                @endif
                            @endforeach
                        @endif

                        <button type="submit" class="btn btn-primary w-100 mt-3">
                            <span>@lang('Confirm Now')</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('script')
    <script>
        'use strict'
        $(document).on("change",'#image',function () {
                let reader = new FileReader();
                reader.onload = (e) => {
                    $('#image_preview_container').attr('src', e.target.result);
                }
                reader.readAsDataURL(this.files[0]);
            });
    </script>
@endpush
