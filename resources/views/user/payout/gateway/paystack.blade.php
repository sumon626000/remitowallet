@extends(userLayout())
@section('title',__('Payout'))

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Payout')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('Payout Confirm')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5 h-100">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Paystack Payout Confirm')</h4>
                    </div>
                    <form action="{{ userRoute('payout.paystack',$payout->trx_id) }}"
                          method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">

                            <div class="col-md-12 input-box dynamic-bank d-none mt-3">
                                <label class="form-label" for="dynamic-bank">@lang('Select Bank')</label>
                                <select id="dynamic-bank" name="bank"
                                        class="form-control cmn-select2">
                                </select>
                                @error('bank')
                                <span class="text-danger">{{$message}}</span>
                                @enderror
                            </div>

                            @if(isset($payoutMethod->inputForm))
                                @foreach($payoutMethod->inputForm as $key => $value)
                                    @if($value->type == 'text')
                                        <div class="input-box mt-3">
                                            <label class="form-label" for="{{ $value->field_name }}">
                                                @lang(snake2Title($value->field_name))</label>
                                            <input type="text" name="{{ $value->field_name }}"
                                                   placeholder="{{ __(snake2Title($value->field_name)) }}"
                                                   autocomplete="off"
                                                   value="{{ old(snake2Title($value->field_name)) }}"
                                                   class="form-control @error($value->field_name)) is-invalid @enderror">
                                            <div class="invalid-feedback">
                                                @error($value->field_name) @lang($message) @enderror
                                            </div>
                                        </div>
                                    @elseif($value->type == 'textarea')
                                        <div class="input-box">
                                            <label class="form-label" for="{{ $value->field_name }}">
                                                @lang(snake2Title($value->field_name))</label>
                                            <textarea
                                                class="form-control @error($value->field_name) is-invalid @enderror"
                                                name="{{$value->field_name}}"
                                                rows="5">{{ old(snake2Title($value->field_name)) }}</textarea>
                                            <div
                                                class="invalid-feedback">@error($value->field_name) @lang($message) @enderror</div>
                                        </div>
                                    @elseif($value->type == 'file')
                                        <div class="input-box mt-3 mb-4">
                                            <label class="form-label">@lang('Choose File')</label>
                                            <div id="image-preview" class="image-preview">
                                                <label for="image-upload"
                                                       id="image-label">@lang('Choose File')</label>
                                                <input type="file" name="{{ $value->field_name }}"
                                                       class="form-control @error($value->field_name) is-invalid @enderror"
                                                       id="image-upload"/>
                                            </div>
                                            <div class="invalid-feedback">
                                                @error($value->field_name) @lang($message) @enderror
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                            @if($payoutMethod->automatic_payout_permisstion)
                                <div class="input-box mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" name="automatic_payout_permisstion" type="checkbox" value="1"/>
                                        <label class="form-check-label" for="tandc">
                                            @lang('Automatic Payout')
                                        </label>
                                    </div>
                                </div>
                            @endif

                            @if(in_array('payout', $enable_for))
                                <div class="input-box mt-3 security-block">
                                    <label class="form-label" for="security_pin">@lang('Security Pin')</label>
                                    <input type="password" name="security_pin"
                                           placeholder="@lang('Please enter your security PIN')"
                                           autocomplete="off"
                                           value="{{ old('security_pin') }}"
                                           class="form-control @error('security_pin') is-invalid @enderror">
                                    <div class="invalid-feedback">
                                        @error('security_pin') @lang($message) @enderror
                                    </div>
                                    <div class="valid-feedback"></div>
                                </div>
                            @endif

                            <button id="submit" type="submit" class="btn btn-primary mt-4" >@lang('Submit')</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Transaction Details')</h4>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush list-group-no-gutters showCharge">
                            <x-list-item label="Payout Method" value="{{ __($payoutMethod->name) }}" class="text-info fw-semibold"/>
                            <x-list-item label="Request Amount" value="{{ getAmount($payout->amount) . ' ' . $payout->payout_currency_code }}" class=" fw-semibold"/>
                            <x-list-item label="Charge" value="{{ getAmount($payout->charge) . ' ' . $payout->payout_currency_code }}" class="text-danger"/>
                            <x-list-item label="Net Amount" value="{{ (getAmount($payout->net_amount)) }} {{ $payout->payout_currency_code }}" class="text-info fw-semibold"/>
                            <x-list-item label="Net Amount In Base Currency" value="{{ getAmount($payout->net_amount_in_base_currency) . ' ' . basicControl()->base_currency }}" class="text-success fw-semibold" />
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

@endsection



@push('script')
    <script type="text/javascript">
        'use strict';

        let currency_code = "{{old('currency_code')}}";
        if (currency_code) {
            getBankForm(currency_code);
        }

        $(document).ready(function () {
            let currencyCode = $(".transfer-currency").val();
            $('.dynamic-bank').addClass('d-none');
            getBankForm(currencyCode);


            $(document).on("change", "#dynamic-bank", function () {
                let type = $(this).find(':selected').data('type');
                $('.type').val(type);
            })

        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function getBankForm(currencyCode) {
            $.ajax({
                url: "{{userRoute('payout.getBankList')}}",
                type: "post",
                data: {
                    currencyCode,
                },
                success: function (response) {
                    if (response.data != null) {
                        showBank(response.data)
                    }
                }
            });
        }

        function showBank(bankLists) {
            $('#dynamic-bank').html(``);
            let options = `<option disabled selected>@lang("Select Bank")</option>`;
            for (let i = 0; i < bankLists.length; i++) {
                options += `<option value="${bankLists[i].code}" data-type="${bankLists[i].type}">${bankLists[i].name}</option>`;
            }
            $('.dynamic-bank').removeClass('d-none');
            $('#dynamic-bank').html(options);
            $('.cmn-select2').select2();
        }

    </script>

@endpush
