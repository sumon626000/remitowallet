@extends(userLayout())
@section('title',__('Payout Preview'))
@section('content')

    <div class="content {{ containerClass() }}">
        <x-page-header menu="Payout"/>

        <div class="row">
            <div class="col-lg-6 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5 h-100">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('FlutterWave Payout Confirm')</h4>
                    </div>
                    <form action="{{ userRoute('payout.flutterwave',$payout->trx_id) }}"
                          method="post" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body">

                            <div class="tom-select-custom">
                                <label class="form-label" for="bank">@lang('Select Transfer')</label>
                                <select class="js-select js-datatable-filter form-select form-select-sm bank"
                                        id="bank" name="transfer_name"
                                        data-hs-tom-select-options='{ "placeholder": "Search Bank...",
                                            "hideSearch": false, "searchInDropdown": true, "dropdownWidth": "100%"
                                        }'
                                >
                                    <option value="''"  selected disabled
                                            data-option-template='<span class="d-flex align-items-center">
                                                <span class="legend-indicator bg-warning"></span>Select a bank</span>'>
                                    </option>

                                    @foreach($payoutMethod->banks as $bank)
                                        <option value="{{$bank}}" {{old('transfer_name') == $bank ?'selected':''}}>{{$bank}}</option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-12 input-box dynamic-bank d-none mt-3">
                                <label class="form-label" for="dynamic-bank">@lang('Select Bank')</label>
                                <select id="dynamic-bank" name="bank"
                                        class="form-control cmn-select2">
                                </select>
                                @error('bank') <span class="text-danger">{{$message}}</span> @enderror
                            </div>

                            <div class="row dynamic-input mt-3">

                            </div>


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
                <div class="card">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Transaction Details')</h4>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush list-group-no-gutters showCharge">
                            <x-list-item label="Payout Method" value="{{ __($payoutMethod->name) }}" class="text-info fw-semibold"/>
                            <x-list-item label="Payout Currency" value="{{ __($payout->payout_currency_code) }}" class="fw-semibold"/>
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

<x-assets :flatpickr="false"/>

@push('script')
    <script type="text/javascript">
        'use strict';

        var bankName = null;
        var payAmount = '{{$payout->amount}}'
        var baseCurrency = "{{basicControl()->base_currency}}"
        var transferName = "{{old('transfer_name')}}";
        if (transferName) {
            getBankForm(transferName);
        }


        $(document).ready(function () {
            $(document).on("change", ".bank", function () {
                bankName = $(this).val();
                $('.dynamic-bank').addClass('d-none');
                getBankForm(bankName);
            })
        });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function getBankForm(bankName) {
            $.ajax({
                url: "{{userRoute('payout.getBankForm')}}",
                type: "post",
                data: {
                    bankName,
                },
                success: function (response) {
                    if (response.bank != null) {
                        showBank(response.bank.data)
                    }
                    showInputForm(response.input_form)
                }
            });
        }

        function showBank(bankLists) {
            $('#dynamic-bank').html(``);
            var options = `<option disabled selected>@lang("Select Bank")</option>`;
            for (let i = 0; i < bankLists.length; i++) {
                options += `<option value="${bankLists[i].code}">${bankLists[i].name}</option>`;
            }

            $('.dynamic-bank').removeClass('d-none');
            $('#dynamic-bank').html(options);
            $('.cmn-select2').select2();
        }

        function showInputForm(form_fields) {
            $('.dynamic-input').html(``);
            var output = "";

            for (let field in form_fields) {
                let newKey = field.replace('_', ' ');
                output += `<div class="col-md-6 mt-3">
                                <label class="text-capitalize">${newKey}</label>
                                <input type="text" name="${field}" value="" class="form-control" required>
                          </div>`
            }
            $('.dynamic-input').html(output);
        }

    </script>

@endpush
