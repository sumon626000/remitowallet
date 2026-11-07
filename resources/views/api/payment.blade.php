@extends(template().'layouts.app')
@section('title',__('Payment'))
@section('content')

    <div class="main-content pt-100 pb-100 publicView">
        <section class="section">
            <div class="container-fluid" id="container-wrapper">
                <div class="row justify-content-md-center">
                    <div class="col-lg-4">
                        <div class="card mb-4 card-primary shadow">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold card-title">@lang('Select Payment Method')</h6>
                            </div>
                            <div class="card-body">
                                <form
                                    action="{{ route('make.payment.confirm', [$mode,$apiOrder->utr]) }}"
                                    method="post">
                                    @csrf
                                    <div class="row">
                                        <input type="hidden" name="currency" id="currency"
                                               value="{{$apiOrder->currency_id ?? null}}">
                                        <input type="hidden" name="amount" id="amount" value="{{$apiOrder->amount ?? null}}">
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <div class="row payment-method-input">
                                                    @foreach($methods ?? [] as $key => $method)
                                                        <div class="col-md-3  col-sm-3 col-4">
                                                            <div class="form-check form-check-inline ">
                                                                <input class="form-check-input methodId" type="radio"
                                                                       name="methodId" id="{{ $key }}"
                                                                       value="{{ $method->id }}" {{ old('methodId') == $method->id || $key == 0 ? ' checked' : ''}}>
                                                                <label class="form-check-label" for="{{ $key }}">
                                                                    <img src="{{ $method->gatewayImg() }}">
                                                                </label>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="submit" id="submit" class="mt-3 ms-lg-3 cmn--btn"
                                                    disabled>@lang('Pay Now')</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card mb-4 card-primary shadow">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold card-title">@lang('Details')</h6>
                            </div>
                            <div class="card-body showCharge">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('script')
    <script>
        'use strict';
        $(function () {
            let currency_id = '{{$apiOrder->currency_id}}';
            let CurrSymbol = "{{optional($apiOrder->currency)->symbol}}"

            let feedback = $('.showCharge');

            $('[data-toggle="tooltip"]').tooltip();
            let amountStatus = false;
            let amount = '{{$apiOrder->amount}}';
            let transaction_type_id = "{{ config('transactionType.deposit') }}";
            let methodId = $("input[type='radio'][name='methodId']:checked").val();

            checkAmount(amount, currency_id, transaction_type_id, methodId)

            function clearMessage(fieldId) {
                $(fieldId).removeClass('is-valid')
                $(fieldId).removeClass('is-invalid')
                $(fieldId).closest('div').find(".invalid-feedback").html('');
                $(fieldId).closest('div').find(".is-valid").html('');
            }

            $(document).on('click, change, input', "#amount, #charge_from, #currency, .methodId", function (e) {
                let methodId = $("input[type='radio'][name='methodId']:checked").val();
                if (!isNaN(amount) && amount > 0) {
                    checkAmount(amount, currency_id, transaction_type_id, methodId)
                } else {
                    clearMessage(feedback)
                    feedback.html('')
                }
            });

            function checkAmount(amount, currency_id, transaction_type_id, methodId) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('deposit.checkAmount') }}",
                    dataType: "json",
                    data: {
                        'amount': amount,
                        'currency_id': currency_id,
                        'transaction_type_id': transaction_type_id,
                        'methodId': methodId,
                    }
                })
                    .done(function (response) {
                        clearMessage(feedback)
                        if (response.status) {
                            $(feedback).html('')
                            amountStatus = true;
                            showCharge(response)
                        } else {
                            amountStatus = false;
                            showCharge(response)
                            $(feedback).html(`<h6 class="text-danger">${response.message}</h6>`)
                        }
                        submitButton()
                    });
            }

            function submitButton() {
                if (amountStatus) {
                    $("#submit").removeAttr("disabled");
                } else {
                    $("#submit").attr("disabled", true);
                }
            }

            function showCharge(response) {
                let txnDetails = `
						<ul class="list-group">
							<li class="list-group-item d-flex justify-content-between">
								<span>{{ __('Requested Amount') }}</span>
								<span> {{ @(getAmount($apiOrder->amount ?? null)) }} {{ @__(optional($apiOrder?->currency)->code) }}</span>
							</li>
							<li class="list-group-item d-flex justify-content-between">
							    <span>{{ __('Transfer Charge') }}</span>
							    <span class="text-danger"> ${CurrSymbol} ${response.charge}</span>
						    </li>
							<li class="list-group-item d-flex justify-content-between">
								<span>{{ __('Payable Amount') }}</span>
								<span class="text-info"> ${CurrSymbol} ${response.payable_amount}</span>
							</li>
							<li class="list-group-item d-flex justify-content-between">
								<span>{{ __('Received') }}</span>
								<span class="text-info"> ${CurrSymbol} ${response.amount}</span>
							</li>
						</ul>
						`
                $('.showCharge').html(txnDetails)
            }
        });
    </script>
@endpush
