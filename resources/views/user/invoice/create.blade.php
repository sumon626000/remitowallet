@extends(userLayout())
@section('title',__('Invoice Create'))

@section('content')

    <div class="content {{ containerClass() }}" id="invoice-app" v-cloak>
        <x-page-header menu="Services" />

        <div class="row">
            <div class="col-md-6 mb-3 mb-lg-0">
                <div class="card mb-3 mb-lg-5">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Invoice Create')</h4>
                    </div>
                    <div class="card-body">
                        <form action="" method="post">
                            @csrf
                            <div class="row mb-2">
                                <div class="form-group col-md-6">
                                    <label class="form-label" for="">@lang('Customer Email')</label>
                                    <input type="email" name="email" v-model="invoice.customer_email"
                                           v-on:keyup="customerEmail" class="form-control">
                                    <span class="text-danger customer_email"></span>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="form-label" for="">@lang('Invoice Number')</label>
                                    <input type="text" class="form-control" v-model="invoice.invoice_number"
                                           v-on:keyup="invoiceNumber" placeholder=""/>
                                    <span class="text-danger invoice_number"></span>
                                </div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-md-6">
                                    <div class="form-group search-currency-dropdown">
                                        <label class="form-label" for="currency">@lang('Currency')</label>
                                        <a href="javascript:void(0)" title="charges and limits"
                                           data-target="#chargeLimit" data-toggle="modal"
                                           v-if="invoice.currency != ''">
                                            <i class="fa fa-info-circle"></i>
                                        </a>
                                        <div class="tom-select-custom">
                                            <select id="currency" name="currency" v-model="invoice.currency"
                                                    @change="onChange($event)" class="js-select form-select"
                                                    data-hs-tom-select-options='{
                                                      "placeholder": "Select Currency...", "hideSearch": false
                                                    }'
                                            >
                                                <option value="" disabled>Select Currency...</option>
                                                <option v-for="(obj, index) in currencies" :value="obj.id"
                                                        :currencySymbol="obj.symbol" :currencycode="obj.code">
                                                    @{{ obj.code }} - @{{ obj.name }}
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="form-group col-12">
                                    <label class="form-label" for="note">@lang('Note')</label>
                                    <textarea class="form-control" v-model="invoice.note"
                                        placeholder="@lang('Payment request note (optional)')"
                                        cols="30" rows="3"></textarea>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label class="form-label" for="">@lang('Payment Frequency')</label>
                                <div class="col-sm mb-2 mb-sm-0">
                                    <label class="form-control" for="radio1">
                                      <span class="form-check">
                                        <input type="radio" name="flexRadioDefault" id="radio1"
                                               class="form-check-input"
                                               @click="payment('1')" :checked="invoice.payment == '1'" />
                                        <span class="form-check-label">@lang('One time')</span>
                                      </span>
                                    </label>
                                </div>
                                <div class="col-sm mb-2 mb-sm-0">
                                    <label class="form-control" for="radio2">
                                      <span class="form-check">
                                        <input type="radio" name="flexRadioDefault" id="radio2"
                                               class="form-check-input"
                                               @click="payment('2')" :checked="invoice.payment == '2'" />
                                        <span class="form-check-label">@lang('Weekly')</span>
                                      </span>
                                    </label>
                                </div>
                                <div class="col-sm mb-2 mb-sm-0">
                                    <label class="form-control" for="radio3">
                                      <span class="form-check">
                                        <input type="radio" name="flexRadioDefault" id="radio3"
                                               class="form-check-input"
                                               @click="payment('3')" :checked="invoice.payment == '3'" />
                                        <span class="form-check-label">@lang('Monthly')</span>
                                      </span>
                                    </label>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group col-md-6" v-if="invoice.payment == 1">
                                    <label class="form-label" for="">@lang('Due Date')</label>
                                    <input type="text" class="js-flatpickr form-control flatpickr-custom"
                                           id="due_date" v-model="invoice.due_date" placeholder="Select dates">
                                    <span class="text-danger due_date"></span>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="form-group col-md-6" v-if="invoice.payment == 2 || invoice.payment == 3">
                                    <label class="form-label" for="">@lang('Number of payments')</label>
                                    <input type="number" class="form-control" v-model="invoice.num_payment" />
                                    <span class="text-danger num_payment"></span>
                                </div>
                                <div class="form-group col-md-6" v-if="invoice.payment == 2 || invoice.payment == 3">
                                    <label class="form-label" for="">@lang('First Payment Date')</label>
                                    <input type="text" class="js-flatpickr form-control flatpickr-custom"
                                           id="first_pay_date" v-model="invoice.first_pay_date" placeholder="Select date">
                                    <span class="text-danger first_pay_date"></span>
                                </div>
                            </div>

                            <div class="row  float-right">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-primary btn-sm btn-block add-service-btn"
                                        data-bs-target="#addService" data-bs-toggle="modal"
                                        @click="makeEmptyItem">
                                        @lang('Add services')
                                    </button>
                                </div>
                            </div>

                        </form>
                        @include('user.invoice.serviceList')
                    </div>
                </div>
            </div>

            @include('user.invoice.report')

        </div>

        @include('user.invoice.modalForm')
    </div>

@endsection

<x-assets :flatpickr="false" />

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush
@push('js-lib')
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict';
        function initializeFlatpickr() {
            HSCore.components.HSFlatpickr.init('.js-flatpickr', {
                altInput: true,
                altFormat: "Y-m-d", // Display format
                dateFormat: "d/m/Y", // Value format
                onChange: function (selectedDates, dateStr, instance) {
                    const target = instance.input;
                    const vueModel = target.getAttribute('v-model');
                    if (vueModel) {
                        const app = window.app || {};
                        const modelParts = vueModel.split('.');
                        if (modelParts.length > 1) {
                            let obj = app;
                            for (let i = 0; i < modelParts.length - 1; i++) {
                                obj = obj[modelParts[i]];
                            }
                            obj[modelParts[modelParts.length - 1]] = dateStr;
                        }
                    }
                }
            });
        }

        $(document).ready(function () {
            initializeFlatpickr();
        });
        $(document).on('change', '.js-flatpickr', function () {
            initializeFlatpickr();
        });
    </script>
@endpush


@push('script')
	<script>
		"use strict";
		var newApp = new Vue({
			el: "#invoice-app",
			data: {
				date: new Date(),
				options: {
					format: 'DD/MM/YYYY',
					useCurrent: false,
				},
				item: {
					invoice_tab: "",
					themeName: "",
					frontColor: "",
					title: '',
					invoiceNumber: '',
					description: '',
					price: '',
					quantity: '',
					customer: []
				},
				currencies: [], symbol: '', code: '',
				subtotal: 0, maxLimit: '', minLimit: '', charges: '',
				oldPrice: 0,
				oldQuantity: 0,
				taxRate: 0,
				vatRate: 0,
				tax: 0,
				vat: 0,
				grandTotal: 0,
				customers: [], clickBtn: '',
				itemId: '',
				showModal: false,
				title_error: '',
				description_error: '',
				price_error: '',
				quantity_error: '',
				customer_email_error: '', due_date_error: '',
				invoice: {
					invoice_number: '', payment: '', due_date: '', num_payment: '', currency: '',
					first_pay_date: '', customer_email: '', note: '', items: []
				},
				items: {title: '', description: '', price: '', quantity: ''},
			},
			mounted() {
				this.item.invoice_tab = 1;
				this.item.themeName = 'city-lights';
				this.item.frontColor = 'black';
				this.invoice.payment = 1;
				this.currencies = @json($currencies);
			},
            watch: {
                'invoice.payment': function () {
                    this.$nextTick(() => {
                        initializeFlatpickr();
                    });
                }
            },
			methods: {
				toggleModal() {
					this.showModal = !this.showModal;
				},
				saveInvoice(buttonName) {
					if (buttonName == 'send') {
						this.saveAndSendError()
					}

					let invoice = this.invoice;
					invoice.customer_id = this.item.customer.id;
					invoice.invoice_tab = this.item.invoice_tab;
					invoice.theme_name = this.item.themeName;
					invoice.front_color = this.item.frontColor;
					invoice.subtotal = this.subtotal;
					invoice.tax = this.tax;
					invoice.taxRate = this.taxRate;
					invoice.vat = this.vat;
					invoice.vatRate = this.vatRate;
					invoice.garndtotal = this.grandTotal;
					invoice.button_name = buttonName;


					axios.post("{{ route('user.invoice.store') }}", this.invoice)
						.then(function (response) {
							if (response.data.status == 'success') {
								window.location.href = response.data.url;
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
							errors = errors.errors
							for (let err in errors) {
								let selector = document.querySelector("." + err);
								if (selector) {
									selector.innerText = `${errors[err]}`;
								}
							}
						});
				},
				onChange(event) {
					this.symbol = event.target.options[event.target.options.selectedIndex].getAttribute('currencySymbol');
					this.code = event.target.options[event.target.options.selectedIndex].getAttribute('currencyCode');

					let currency = {};
					currency.id = event.target.value;

					var _this = this;
					axios.post("{{ route('user.currency.check') }}", currency)
						.then(function (response) {
							if (response.data.status == 'success') {
								_this.maxLimit = response.data.value.max_limit;
								_this.minLimit = response.data.value.min_limit;
								_this.charges = response.data.value.fixed_charge + '+' + response.data.value.percentage_charge + '%';
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
							errors = errors.errors
							for (let err in errors) {
								let selector = document.querySelector("." + err);
								if (selector) {
									selector.innerText = `${errors[err]}`;
								}
							}
						});

				},
				payment(payment) {
					this.invoice.payment = payment;
				},
				editItem(index) {
					this.itemId = index;

					this.item.title = this.invoice.items[index].title;
					this.item.description = this.invoice.items[index].description;
					this.item.price = this.invoice.items[index].price;
					this.item.quantity = this.invoice.items[index].quantity;

					this.calculateSubtotalEdit(this.item.price, 0, this.item.quantity)
				},
				addServices() {
					this.serviceError();
					if (this.item.title && this.item.price && !isNaN(this.item.price) && this.item.quantity && !isNaN(this.item.quantity)) {
						this.invoice.items.push({
							title: this.item.title,
							description: this.item.description,
							price: this.item.price,
							quantity: this.item.quantity
						});

						this.calculateSubtotal(this.item.price, this.item.quantity)
						this.taxVatall();
						$('#addService').modal('hide');
						this.makeEmptyItem();
						this.showModal = false;
					}
				},
				editService() {
					this.serviceError();
					if (this.item.title && this.item.description && this.item.price && !isNaN(this.item.price) && this.item.quantity && !isNaN(this.item.quantity)) {
						this.invoice.items.splice(this.itemId, 1, {
							title: this.item.title,
							description: this.item.description,
							price: this.item.price,
							quantity: this.item.quantity
						});

						this.calculateSubtotalEdit(this.oldPrice, this.item.price, this.oldQuantity, this.item.quantity);
						this.makeEmptyItem();
						$('#editModal').modal('hide');
					}
				},
				removeItem(index) {
					this.itemId = index;
					this.item.price = this.invoice.items[index].price;
					this.item.quantity = this.invoice.items[index].quantity;
					this.calculateSubtotalDelete(this.item.price, this.item.quantity)
					this.taxVatall();
					this.invoice.items.splice(index, 1);
				},
				calculateSubtotal(price, quantity) {
					var total = parseFloat(price) * parseInt(quantity);
					this.subtotal += total;
					this.grandTotal = this.subtotal;
				},
				calculateSubtotalEdit(oldPrice, newPrice, oldQuantity, newQuantity) {
					this.oldPrice = oldPrice;
					this.oldQuantity = oldQuantity;
					var newPrice = newPrice;
					var newQuantity = newQuantity;

					var oldTotal = parseFloat(this.oldPrice) * parseInt(this.oldQuantity);

					var newTotal = newPrice * newQuantity;

					var sub = parseFloat(this.subtotal) - oldTotal;

					var add = sub + newTotal;
					if (!isNaN(add)) {
						this.subtotal = add
						this.taxVatall();
					}
				},
				taxVatall() {
					this.tax = (parseFloat(this.taxRate) * parseFloat(this.subtotal) / 100);
					this.vat = (parseFloat(this.vatRate) * parseFloat(this.subtotal) / 100);
					this.totalCalculation(this.tax, this.vat);
				},
				calculateSubtotalDelete(price, quantity) {
					var oldTotal = parseFloat(price) * parseInt(quantity);
					this.subtotal -= oldTotal;
					this.tax = 0;
					this.vat = 0;
					this.totalCalculation(0, 0)
				},
				calculateTax(tax) {
					this.tax = tax.target.value
					this.tax = (parseFloat(this.tax) * parseFloat(this.subtotal) / 100);
					this.taxRate = tax.target.value;
					this.totalCalculation(this.tax, this.vat);
				},
				calculateVat(vat) {
					this.vat = vat.target.value
					this.vat = (parseFloat(this.vat) * parseFloat(this.subtotal) / 100);
					this.vatRate = vat.target.value;
					this.totalCalculation(this.tax, this.vat);
				},
				totalCalculation(tax, vat) {
					this.grandTotal = parseFloat(this.subtotal) + parseFloat(tax) + parseFloat(vat);
				},
				invoiceNumber(invoice) {
					this.item.invoiceNumber = invoice.target.value

				},
				customerEmail(invoice) {
					this.invoice.customer_email = invoice.target.value;
				},
				sendInvoice() {
					this.invoice.customer_email = this.item.customer.email_address;
				},
				serviceError() {
					if (!this.item.title) {
						this.title_error = 'Title is required';
					}

					if (!this.item.price) {
						this.price_error = 'Price is required';
					}

					if (isNaN(this.item.price)) {
						this.price_error = 'Invalid Price';
					}

					if (!this.item.quantity) {
						this.quantity_error = 'Quantity is required';
					}

					if (isNaN(this.item.quantity)) {
						this.quantity_error = 'Quantity Price';
					}
				},
				saveAndSendError() {
					if (!this.invoice.customer_email) {
						this.customer_email_error = 'Email is required';
					}
					if (this.maxLimit < this.grandTotal) {
						Notiflix.Notify.failure("Gradtotal Must e less than Max Limit");
					}
					if (this.minLimit > this.grandTotal) {
						Notiflix.Notify.failure("Gradtotal Must e large than Min Limit");
					}
				},
				makeEmptyItem() {
					this.item.title = '';
					this.item.description = '';
					this.item.price = '';
					this.item.quantity = '';
					this.makeEmptyError();
				},
				makeEmptyError() {
					this.title_error = '';
					this.description_error = '';
					this.price_error = '';
					this.quantity_error = '';
				}
			},
			filters: {
				decimalFiltered(value) {
					if (value) {
						return value.toFixed(2)
					}
				}
			},
		})
	</script>
	@if ($errors->any())
		@php
			$collection = collect($errors->all());
			$errors = $collection->unique();
		@endphp
		<script>
			"use strict";
			@foreach ($errors as $error)
			Notiflix.Notify.failure("{{ trans($error) }}");
			@endforeach
		</script>
	@endif
@endpush
