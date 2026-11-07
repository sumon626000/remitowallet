@extends(userLayout())
@section('title', trans('Confirm Card Request'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Confirm Card Request')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Confirm Card Request')</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">{{optional($order->cardMethod)->name}} @lang('Card Request') </h4>
                    </div>
                    <form action="{{ route('user.order.confirm',$orderId) }}" method="post">
                        @csrf
                        <div class="card-body">
                            <ol class="list-group list-group-numbered">
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Currency')</div>
                                    </div>
                                    <span> {{ trans($order->currency) }} </span>
                                </li>
                                @if($order->form_input)
                                    @forelse($order->form_input as $k => $v)
                                        <li class="list-group-item d-flex justify-content-between align-items-start">
                                            <div class="ms-2 me-auto">
                                                <div class="fw-semibold">{{ @$v->field_level }}</div>
                                            </div>
                                            <span>{{ @$v->field_value }}</span>
                                        </li>
                                    @empty
                                    @endforelse
                                @endif
                            </ol>

                            @if(in_array('virtual_card',$enable_for))
                                <div class="row mt-4">
                                    <div>
                                        <label for="security_pin"
                                               class="form-label">@lang('Security Pin')</label>
                                        <input type="password" class="form-control" name="security_pin"
                                               id="security_pin"
                                               placeholder="@lang('Please enter your security PIN')"
                                               value="{{ old('security_pin') }}">
                                    </div>

                                    @error('security_pin')
                                    <span class="text-danger">{{$message}}</span>
                                    @enderror
                                </div>
                            @endif
                            <button id="submit" type="submit"
                                    class="btn btn-primary mt-4">@lang('Confirm Card Request')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

