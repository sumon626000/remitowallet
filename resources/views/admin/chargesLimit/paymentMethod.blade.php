@extends('admin.layouts.app')
@section('page_title', __('Add Gateway Charge'))
@section('content')

    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Currency')</li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $currency->code ?? null }}</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Payment Methods Charge')</h1>
                </div>
            </div>
        </div>

        <x-notification-box
            message="Set payment methods charge limit and status for this currency"
        />


        <div class="row justify-content-md-center">
            <div class="col-lg-10">
                <div class="card mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h4 class="m-0">@lang('Edit Charges & Limit For ') {{ __($currency->code) }}</h4>
                        <a href="{{route('admin.currency.index')}}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-arrow-left"></i> @lang('Back')</a>
                    </div>
                    <div class="card-body" id="depositMethodArea">
                        <div class="row">
                            <div class="col-xl-3 col-lg-4">
                                <div class="tabScroll">
                                    <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist"
                                         aria-orientation="vertical">
                                        @foreach($methods as $key => $method)
                                            <a class="nav-link media " data-toggle="pill"
                                               href=".payment-method-form" role="tab"
                                               @click.prevent="getCharge({{$currency->id}}, {{$method->id}})"
                                               aria-selected="{{ $key == 0 ? 'true' : '' }}">
                                                <span class="tabImage avatar avatar-sm avatar-circle">
                                                    <img src="{{ $method->gatewayImg() }}" class="avatar-img" alt="@lang('Image')">
                                                </span>
                                                {{ __($method->name) }}
                                            </a>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-9 col-lg-8">
                                <div class="tab-content" id="v-pills-tabContent">
                                    <div class="tab-pane fade show active payment-method-form" role="tabpanel">
                                        <form action="#" method="post" @submit.prevent="updateCharge()">
                                            @csrf
                                            <div class="row">
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="convention_rate">@lang('Convention Rate')</label>
                                                        <div class="input-group input-group-sm">
                                                            <div class="input-group-prepend">
                                                                <span class="input-group-text">@lang('1 ') {{ __($currency->code) }} @lang(' =')</span>
                                                            </div>
                                                            <input type="text" class="form-control"
                                                                   placeholder="@lang('eg:- 0.00')"
                                                                   v-model="items.convention_rate"
                                                                   :class="{'is-invalid' : hasErrors.convention_rate.length}">
                                                            <div class="input-group-append" v-cloak>
                                                                <span class="input-group-text">@{{ items.method_currency }}</span>
                                                            </div>
                                                            <div :class="{'invalid-feedback' : hasErrors.convention_rate.length}">
                                                                @{{ hasErrors.convention_rate }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="min_limit">@lang('Minimum Deposit Limit')</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" placeholder="@lang('eg:- 0.00')"
                                                                   v-model="items.min_limit"
                                                                   class="form-control"
                                                                   :class="{'is-invalid' : hasErrors.min_limit.length}">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">{{ __($currency->code) }}</span>
                                                            </div>
                                                            <div :class="{'invalid-feedback' : hasErrors.min_limit.length}">
                                                                @{{ hasErrors.min_limit }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="max_limit">@lang('Maximum Deposit Limit')</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" placeholder="@lang('eg:- 0.00')"
                                                                   v-model="items.max_limit"
                                                                   class="form-control"
                                                                   :class="{'is-invalid' : hasErrors.max_limit.length}">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">{{ __($currency->code) }}</span>
                                                            </div>
                                                            <div :class="{'invalid-feedback' : hasErrors.max_limit.length}">
                                                                @{{ hasErrors.max_limit }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <div class="form-group">
                                                        <label class="form-label" for="percentage_charge">@lang('Percentage Charge')</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" placeholder="@lang('eg:- 0.00')"
                                                                   v-model="items.percentage_charge"
                                                                   class="form-control"
                                                                   :class="{'is-invalid' : hasErrors.percentage_charge.length}">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">@lang('%')</span></div>
                                                            <div :class="{'invalid-feedback' : hasErrors.percentage_charge.length}">
                                                                @{{ hasErrors.percentage_charge }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group">
                                                        <label class="form-label" for="fixed_charge">@lang('Fixed Charge')</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="text" placeholder="@lang('eg:- 0.00')"
                                                                   v-model="items.fixed_charge"
                                                                   class="form-control"
                                                                   :class="{'is-invalid' : hasErrors.fixed_charge.length}">
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">{{ __($currency->code) }}</span>
                                                            </div>
                                                            <div :class="{'invalid-feedback' : hasErrors.fixed_charge.length}">
                                                                @{{ hasErrors.fixed_charge }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">

                                                    <label class="row form-check form-switch" for="breadcrumbSwitch">
                                                        <span class="col-8 col-sm-9 ms-0">
                                                          <span class="text-dark">@lang("Status")
                                                              <i class="bi-question-circle text-body ms-1"
                                                                 data-bs-toggle="tooltip" data-bs-placement="top"
                                                                 title="@lang("Enable or disable")"></i>
                                                          </span>
                                                        </span>
                                                        <span class="col-4 col-sm-3 text-end">
                                                            <input type="checkbox" class="form-check-input" name="status"
                                                                   id="is_active"
                                                                   :class="{'is-invalid' : hasErrors.is_active.length}"
                                                                   v-model="items.is_active"
                                                                   :checked="items.is_active == 1">
                                                        </span>
                                                    </label>
                                                    <div :class="{'invalid-feedback' : hasErrors.is_active.length}">
                                                        @{{ hasErrors.is_active }}
                                                    </div>

                                                </div>
                                                <div class="col-md-12 mt-3">
                                                    <div class="form-group">
                                                        <button type="submit"
                                                                class="btn btn-primary btn-sm btn-block">@lang('Save Changes')</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection


@push('scripts')
    <script>
        'use strict'

        let depositMethodArea = new Vue({
            el: "#depositMethodArea",
            data: {
                items: [],
                hasErrors: {
                    'convention_rate': '',
                    'max_limit': '',
                    'min_limit': '',
                    'percentage_charge': '',
                    'fixed_charge': '',
                    'is_active': ''
                },
            },
            beforeMount() {
                this.getCharge("{{@$currency->id}}", "{{@$methods[0]->id}}");
            },
            methods: {
                getCharge(currency_id, payment_method_id) {
                    let app = this;
                    let url = "{{ route('admin.get.deposit.charge',['currency_id','payment_method_id']) }}";
                    url = url.replace('currency_id', currency_id);
                    url = url.replace('payment_method_id', payment_method_id);
                    this.setErrorBlank()
                    axios.get(url)
                        .then(function (res) {
                            app.items = res.data;
                        })
                },
                updateCharge() {
                    let app = this;
                    let input = this.items;
                    this.setErrorBlank()
                    axios.post("{{ route('admin.set.deposit.charge') }}", input)
                        .then(function (response) {
                            Notiflix.Notify.success(response.data.message);
                        })
                        .catch(function (errors) {
                            let getError = errors.response.data[0];
                            for (var error in getError) {
                                app.hasErrors[error] = getError[error][0];
                            }
                        });

                },
                setErrorBlank() {
                    this.hasErrors.convention_rate = '';
                    this.hasErrors.max_limit = '';
                    this.hasErrors.min_limit = '';
                    this.hasErrors.percentage_charge = '';
                    this.hasErrors.fixed_charge = '';
                    this.hasErrors.is_active = '';
                }
            }
        });
    </script>
    @endpush
