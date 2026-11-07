@extends('layouts.storeMaster')
@section('page_title', __('Cart'))
@section('content')
	<!-- cart section -->
	<section class="cart-section" id="cart-app" v-cloak>
		<div class="container">
			<div class="row">
				<div class="col">
					<div class="table-wrapper table-responsive">
						<table class="table table-striped">
							<thead>
							<tr>
								<th scope="col">@lang('Product')</th>
								<th scope="col">@lang('Availability')</th>
								<th scope="col">@lang('Price')</th>
								<th scope="col">@lang('Quantity')</th>
								<th scope="col">@lang('Total')</th>
								<th scope="col">@lang('Attributes')</th>
								<th scope="col">@lang('Action')</th>
							</tr>
							</thead>
							<tbody>
							<tr v-for="(obj, index) in cart_item" :value="obj.id">
								<td>
									<img :src="obj.image" class="img-fluid" alt="..."/>
									<span>@{{ obj.name }}</span>
								</td>
								<td>@lang('Available')</td>
								<td>{{currencySymbol($link)}}@{{ obj.price }}</td>
								<td>
									<div class="quantity">
										<button class="btn-inc-dec" @click.prevent="minus(obj)">-</button>
										<input type="text" class="form-control" :value="obj.count"/>
										<button class="btn-inc-dec cartBtnDis" :id="obj.id" @click.prevent="plus(obj)">
											+
										</button>
									</div>
								</td>
								<td>{{currencySymbol($link)}}@{{ obj.price * obj.count }}</td>
								<td v-cloak>
                                    <span v-for="(attributesName, index) in JSON.parse(obj.attributesName)">
                                        <span v-for="key of Object.keys(attributesName)">
                                            @{{ key }}:
                                        </span>
                                        <span v-for="value of Object.values(attributesName)">
                                            @{{ value }}
                                        </span>
                                    </span>
								</td>
								<td>
									<button class="action-btn danger delete-item" @click.prevent="remove(obj)">
										<i class="fas fa-trash" aria-hidden="true"></i>
									</button>
								</td>
							</tr>
							</tbody>
						</table>
					</div>
				</div>
			</div>
			<div class="row mt-5 bottom">
				<div class="col-6">
					<h4 class="mb-5">@lang('Total Item:') @{{ total }}</h4>
					<a href="{{route('public.view',$link)}}" class="btn-custom">@lang('Continue Shopping')</a>
				</div>
				<div class="col-6 text-end">
					<h4 class="mb-5">@lang('Cart Total:') {{currencySymbol($link)}}@{{ cart_total }}</h4>
					<a href="{{route('public.checkout',$link)}}" class="btn-custom">@lang('Place order')</a>
				</div>
			</div>
		</div>
	</section>
@endsection
@push('extra_scripts')
	<script>
		'use script'
		var newApp = new Vue({
			el: "#cart-app",
			data: {
				cart_item: [],
				total: 0,
				cart_total: 0,
				productObj: {},
				test: '',
			},
			mounted() {
				let _this = this;
				_this.cartItem();
				_this.cart_item = JSON.parse(sessionStorage.getItem('shoppingCart'));
				$.each(_this.cart_item, function (key, value) {
					var obj = value
					_this.plus(obj, 1);
				});
			},
			methods: {
				cartItem() {
					let _this = this;
					_this.cart_item = JSON.parse(sessionStorage.getItem('shoppingCart'));
					_this.calc();
				},
				remove(obj) {
					let _this = this;
					_this.cart_item.splice(_this.cart_item.indexOf(obj), 1);
					_this.calc();
					var selectData = JSON.parse(sessionStorage.getItem('shoppingCart'));
					var storeIds = selectData.filter(function (item) {
						if (item.name === obj.name) {
							return false;
						}
						return true;
					});
					sessionStorage.setItem("shoppingCart", JSON.stringify(storeIds));
					shoppingCart.removeItemFromCartAll(obj.name);
					displayCart();
					Notiflix.Notify.success("Remove from Cart");
				},
				minus(obj) {
					shoppingCart.removeItemFromCart(obj.name);
					this.cartItem();
					displayCart();
				},
				plus(obj, check = null) {
					this.check(obj)
					if (check == null) {
						shoppingCart.addItemToCart(obj.id, obj.name, obj.price, obj.count, obj.image, obj.currency, null, obj.attributes, obj.atrr);
					}
					this.cartItem();
					displayCart();
				},
				check(obj) {
					let productObj = this.productObj;
					productObj.productId = obj.id;
					productObj.attributeIds = obj.attributes;
					productObj.storage_qty = obj.count;

					axios.post("{{ route('product.check') }}", this.productObj)
						.then(function (response) {
							if (response.data.status == false) {
								$(`#${response.data.productId}`).attr('disabled', true);
							}
							return true;
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
				clearCart() {
					cart_item = [];
					this.cartItem();
				},

				removeAll() {
					shoppingCart.clearCart();
					this.cartItem();
					displayCart();
				},

				calc() {
					let _this = this;
					_this.cart_total = 0;
					_this.total = 0;

					var cart_item = _this.cart_item;
					for (let obj in cart_item) {
						var qty = cart_item[obj].count;
						var price = cart_item[obj].price;

						var total_price = qty * price;
						_this.cart_total += total_price;

						var count = parseInt(cart_item[obj].count);
						_this.total = parseInt(_this.total) + parseInt(count);
					}
					return 0;
				}
			}
		})
	</script>
@endpush
