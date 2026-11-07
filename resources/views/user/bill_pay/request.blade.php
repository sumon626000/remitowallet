@extends(userLayout())
@section('title', trans('Pay Bill'))

@push('style')
    <style>
        .bill-tabs .nav-link{
            cursor: pointer;
        }
    </style>
@endpush

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
                            <li class="breadcrumb-item active" aria-current="page">@lang('Bill')</li>
                        </ol>
                    </nav>

                    <h1 class="page-header-title">@lang('Pay Bill')</h1>
                </div>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header card-header-content-md-between">
                        <h4 class="card-header-title">@lang('Pay Bill')</h4>
                        <x-btn.info route="user.pay.bill.list">@lang('History')</x-btn.info>
                    </div>
                    <form action="{{ route('user.pay.bill.submit') }}" method="post">
                        @csrf
                        <div class="card-body">
                            <div class="mb-4">
                                <!-- Nav -->
                                <ul class="nav nav-segment bill-tabs">
                                    <li class="nav-item">
                                        <label class="nav-link active" for="bill1">
                                            <input type="radio" value="airtime" class="btn-check d-none" name="category" id="bill1" autocomplete="off" checked />
                                            <i class="fal fa-rocket nav-icon"></i> @lang('AirTime')
                                        </label>
                                    </li>
                                    <li class="nav-item">
                                        <label class="nav-link" for="bill2">
                                            <input type="radio" value="power" class="btn-check d-none" name="category" id="bill2" autocomplete="off" />
                                            <i class="fas fa-bolt nav-icon"></i> @lang('Electricity')
                                        </label>
                                    </li>
                                    <li class="nav-item">
                                        <label class="nav-link" for="bill3">
                                            <input type="radio" value="internet" class="btn-check d-none" name="category" id="bill3" autocomplete="off" />
                                            <i class="fab fa-battle-net nav-icon"></i> @lang('Internet')
                                        </label>
                                    </li>
                                    <li class="nav-item">
                                        <label class="nav-link" for="bill4">
                                            <input type="radio" value="toll" class="btn-check d-none" name="category" id="bill4" autocomplete="off" />
                                            <i class="fad fa-dolly-empty nav-icon"></i> @lang('Toll')
                                        </label>
                                    </li>
                                    <li class="nav-item">
                                        <label class="nav-link" for="bill5">
                                            <input type="radio" value="cables" class="btn-check d-none" name="category" id="bill5" autocomplete="off" />
                                            <i class="fas fa-tv nav-icon"></i> @lang('Cable Tv')
                                        </label>
                                    </li>
                                    <li class="nav-item">
                                        <label class="nav-link" for="bill6">
                                            <input type="radio" value="data_bundle" class="btn-check d-none" name="category" id="bill6" autocomplete="off" />
                                            <i class="fas fa-server nav-icon"></i> @lang('Data Bundle')
                                        </label>
                                    </li>
                                </ul>

                                <!-- End Nav -->
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="from_wallet" class="form-label">@lang('From Wallet')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select" autocomplete="off" name="from_wallet"
                                                id="from_wallet"
                                                data-hs-tom-select-options='{
                                            "placeholder": "Select Wallet..."
                                          }'>
                                            <option value="">@lang('Select From Wallet')</option>
                                            @if(!empty($currencies))
                                                @foreach($currencies as $key => $currency)
                                                    <option data-from_code="{{ __($currency->code) }}"
                                                            data-currencytype="{{ $currency->currency_type }}"
                                                            value="{{ $currency->id }}">
                                                        {{ __($currency->code) }} - {{ __($currency->name) }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                        @error('from_wallet')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label for="from_wallet" class="form-label">@lang('Country')</label>
                                    <div class="tom-select-custom">
                                        <select class="js-select form-select country" autocomplete="off" name="country"
                                                id="country"
                                                data-hs-tom-select-options='{
                                            "placeholder": "Select Country..."
                                          }'>
                                            <option value="">@lang('Select Country')</option>
                                            @foreach($countries->sortBy('asc') as $key => $country)
                                                <option value="{{ $key }}" data-currency="{{$country['currency']}}">
                                                    {{$country['name']}}</option>
                                            @endforeach
                                        </select>
                                        @error('country')
                                        <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="dynamic-services" class="form-label">@lang('Services')</label>
                                    <div class="tom-select-custom">
                                        <select class=" form-select" autocomplete="off" name="service"
                                                id="dynamic-services"
                                                data-hs-tom-select-options='{
                                            "placeholder": "Select Services..."
                                          }'></select>

                                            @error('service')
                                            <span class="text-danger">{{$message}}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="dynamicForm">

                            </div>

                            <div class="showAmount">

                            </div>
                            <button type="submit" class="btn btn-primary mt-3">@lang('Continue')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

<x-assets :flatpickr="false"/>

@push('script')
    <script>
        'use strict';

        var category = 'airtime';
        var currency = "";
        var phoneCode = "";
        var countryList = @json(config('country'));

        $(document).on("change", "input[name='category']", function () {
            category = $("input[name='category']:checked").val();
        });

        $(document).on("click",'.nav-link',function (){
            $('.nav-link').removeClass('active');
            $(this).addClass('active');
        });

        $(document).on('change', '.country', function () {
            $('.dynamicForm').html('');
            $('.showAmount').html('');
            var code = $(this).find(':selected').val();
            for (let i = 0; i < countryList.length; i++) {
                if (countryList[i].code == code) {
                    phoneCode = countryList[i].phone_code;
                }
            }
            fetchService(category, code);
        })

        function fetchService(category, code) {
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                url: "{{ route('user.fetch.services') }}",
                data: {category: category, code: code},
                dataType: 'json',
                type: "post",
                success: function (data) {
                    if (data.status == 'success') {
                        let services = data.data;
                        showServices(services);
                    }
                }
            });
        }

        function showServices(services) {
            $('#dynamic-services').html('');
            var options = `<option disabled value="" selected>@lang("Select Service")</option>`;
            for (let i = 0; i < services.length; i++) {
                options += `<option value="${services[i].id}" data-percent="${services[i].percent_charge}"
                data-fixed="${services[i].fixed_charge}" data-min="${services[i].min_amount}" data-max="${services[i].max_amount}"
                data-currency="${services[i].currency}" data-amount="${services[i].amount}" data-label="${services[i].label_name}">${services[i].type}</option>`;
            }
            $('#dynamic-services').html(options);
        }

        $(document).on('change', '#dynamic-services', function () {
            let label = $(this).find(':selected').data("label");
            currency = $('.country').find(':selected').data('currency');
            const inputString = label;
            const delimiter = ",";
            const stringToArray = inputString.split(delimiter);

            let amount = $(this).find(':selected').data("amount");

            if (amount == -1) {
                let serviceId = $(this).find(':selected').val();
                getMultiAmount(serviceId);
            } else {
                let min = $(this).find(':selected').data("min");
                let max = $(this).find(':selected').data("max");

                $('.showAmount').html('');
                let amountShow = `<div class="row mb-3"><div class="col-md-12">
					<label for="Amount" class="form-label">@lang('Amount')
                ${max > 0 ? `<sup> <i class="fas fa-info-circle"></i>
							You can pay min ${min} ${currency} and max ${max} ${currency}</sup>` : ''}
					</label>
					<div class="input-group">
						<input class="form-control" type="text" id="Amount" name="amount"
							   placeholder="@lang('Enter Amount')" required>
						<span class="input-group-text" id="basic-addon1">${currency}</span>
					</div></div>
					@error('amount')
                <span class="text-danger">{{$message}}</span>
					@enderror
                </div>`;

                $('.showAmount').html(amountShow);

                $('#Amount').val("")
                $('#Amount').attr('readonly', false);

                if (parseInt(amount) > 0) {
                    $('#Amount').val(amount)
                    $('#Amount').attr('readonly', true);
                }
            }
            dynamicInput(stringToArray);
        });

        function getMultiAmount(serviceId) {
            $.ajax({
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf_token"]').attr('content')},
                url: "{{ route('user.fetch.amount') }}",
                data: {serviceId: serviceId},
                dataType: 'json',
                type: "post",
                success: function (data) {
                    if (data.status == 'success') {
                        let resAmounts = data.data;
                        showAmount(resAmounts);
                    }
                }
            });
        }

        function showAmount(resAmounts) {
            $('#dynamic-amounts').html('');
            $('.showAmount').html('');
            var amountShow = `<div class="row mb-3"><div class="col-md-12">
						<label for="dynamic-amounts" class="form-label">@lang('Amount')</label>
						<div class="input-group">
							<select class="form-control form-select" id="dynamic-amounts" name="amount" required>

							</select>
							<span class="input-group-text" id="basic-addon1">${currency}</span>
						</div></div>
						@error('amount')
            <span class="text-danger">{{$message}}</span>
						@enderror
            </div>`;

            $('.showAmount').html(amountShow);

            var options = `<option disabled value="" selected>@lang("Select Amount")</option>`;
            for (let i = 0; i < resAmounts.length; i++) {
                options += `<option value="${resAmounts[i].id}">${resAmounts[i].description}</option>`;
            }
            $('#dynamic-amounts').html(options);
        }

        function dynamicInput(stringToArray) {
            $('.dynamicForm').html('');
            let form = '';
            for (let i = 0; i < stringToArray.length; i++) {
                const formattedString = formatString(stringToArray[i]);
                form += `
				<div class="row my-3">
				    <div class="col-md-12">
					<label for="phone" class="form-label">${formattedString}</label>
					<div class="input-group">
					${stringToArray[i] == 'Recipient_Phone' ?
                    `<span class="input-group-text" id="basic-addon1">${phoneCode}</span>` : ''}
					<input id="telephone" type="text" class="form-control" name="${stringToArray[i]}" placeholder="${formattedString}" required>
					</div>
					</div>
					</div>
					@error('customer')
                <span class="text-danger">{{$message}}</span>
					@enderror
                `;
            }

            $('.dynamicForm').append(form);
        }

        function formatString(input) {
            const stringWithSpaces = input.replace(/_/g, ' ');
            const formattedString = stringWithSpaces.charAt(0).toUpperCase() + stringWithSpaces.slice(1);
            return formattedString;
        }

        $(document).on('keyup', '#Amount', function () {
            this.value = this.value.replace(/[^0-9.]/g, '');
        });

    </script>
@endpush
