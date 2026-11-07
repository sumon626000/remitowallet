<!-- General JS Scripts -->
<script src="{{ asset('assets/store/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('assets/store/js/jquery-3.6.0.min.js') }}"></script>
<script src="{{ asset('assets/store/js/owl.carousel.min.js') }}"></script>
<script src="{{ asset('assets/store/js/range-slider.min.js') }}"></script>
<script src="{{ asset('assets/store/js/socialSharing.js') }}"></script>

<!-- JS Libraies -->
<script src="{{ asset('assets/global/js/notiflix-aio-3.2.6.min.js') }}"></script>
<script src="{{ asset('assets/global/js/pusher.min.js') }}"></script>
<script src="{{ asset('assets/global/js/vue.min.js') }}"></script>
<script src="{{ asset('assets/global/js/axios.min.js') }}"></script>

@stack('js-lib')

<!-- Template JS File -->
<script src="{{ asset('assets/store/js/script.js') }}"></script>
<script>
	$(document).ready(function () {
		$(document).ajaxStart(function () {
			$('#wait').removeClass('d-none').show();
		});
		$(document).ajaxComplete(function () {
			$('#wait').hide();
		});
	});
</script>


<script>
	//Cart Js
	var shoppingCart = (function () {

		cart = [];

		// Constructor
		function Item(id, name, price, count, image, currency, quantity = null, attributes, attributesName) {
			this.id = id;
			this.name = name;
			this.price = price;
			this.count = count;
			this.image = image;
			this.currency = currency;
			this.quantity = quantity;
			this.attributes = attributes;
			this.attributesName = attributesName;
		}

		// Save cart
		function saveCart() {
			sessionStorage.setItem('shoppingCart', JSON.stringify(cart));
		}

		// Load cart
		function loadCart() {
			cart = JSON.parse(sessionStorage.getItem('shoppingCart'));
		}

		if (sessionStorage.getItem("shoppingCart") != null) {
			loadCart();
		}


		var obj = {};

		// Add to cart
		obj.addItemToCart = function (id, name, price, count, image = null, currency, quantity = null, attributes, attributesName) {

			var attempt = 0;
			for (var item in cart) {
				if (cart[item].name === name && JSON.stringify(cart[item].attributes) == JSON.stringify(attributes)) {
					if (quantity == null) {
						cart[item].count++;
						saveCart();
						return;
					} else {
						cart[item].count += parseInt(quantity);
						saveCart();
						return;
					}

				} else {
					var attempt = 0;
				}
			}


			if (attempt == 0 && quantity != null) {
				var item = new Item(id, name, price, count, image, currency, quantity, attributes, attributesName);
				var first = 1;
				for (var i = quantity; i > 0; i--) {
					if (first == 1) {
						first = 0;
						cart.push(item);
						saveCart();
					} else {
						shoppingCart.addItemToCart(id, name, price, 1, image, currency, null, attributes, attributesName);
					}
				}
			}

			if (quantity == null) {
				var item = new Item(id, name, price, count, image, currency, null, attributes, attributesName);
				cart.push(item);
				saveCart();
			}
		}
// Set count from item
		obj.setCountForItem = function (name, count) {
			for (var i in cart) {
				if (cart[i].name === name) {
					cart[i].count = count;
					break;
				}
			}
		};
// Remove item from cart
		obj.removeItemFromCart = function (name) {
			for (var item in cart) {
				if (cart[item].name === name) {
					cart[item].count--;
					if (cart[item].count === 0) {
						cart.splice(item, 1);
					}
					break;
				}
			}
			saveCart();
		}

// Remove all items from cart
		obj.removeItemFromCartAll = function (name) {
			for (var item in cart) {
				if (cart[item].name === name) {
					cart.splice(item, 1);
					break;
				}
			}
			saveCart();
		}

// Clear cart
		obj.clearCart = function () {
			cart = [];
			saveCart();
		}

// Count cart
		obj.totalCount = function () {
			var totalCount = 0;
			for (var item in cart) {
				totalCount += cart[item].count;
			}
			return totalCount;
		}

// Total cart
		obj.totalCart = function () {
			var totalCart = 0;
			for (var item in cart) {
				totalCart += cart[item].price * cart[item].count;
				if (cart[item].count === 0) {
					totalCart = 0;
					break;
				}
			}

			var total = `${Number(totalCart.toFixed(2))}`;
			return total;

		}

// List cart
		obj.listCart = function () {
			var cartCopy = [];
			for (i in cart) {
				item = cart[i];
				itemCopy = {};
				for (p in item) {
					itemCopy[p] = item[p];

				}
				itemCopy.total = Number(item.price * item.count).toFixed(2);
				cartCopy.push(itemCopy)
			}
			return cartCopy;
		}
		return obj;
	})();


	// Add item
	$('.addToCart').click(function (event) {
		event.preventDefault();

		var id = $(this).data('id');
		var name = $(this).data('name');
		var price = Number($(this).data('price'));
		var image = $(this).data('image');
		var currency = $(this).data('currency');
		var quantity = $(this).data('quantity');
		var attributes = $(this).data('attributes');
		var route = $(this).data('route');

		var attributesName = '';

		$.ajax({
			url: route,
			method: "get",
			data: {
				productId: id,
				attributeIds: attributes,
			},
			success: function (response) {
				attributesName = JSON.stringify(response.attributes);
				shoppingCart.addItemToCart(id, name, price, 1, image, currency, quantity, attributes, attributesName);
				displayCart();
				Notiflix.Notify.success("Added to Cart");
			}
		});


	});

	// Clear items
	$('.clear-cart').on('click', function () {
		shoppingCart.clearCart();
		displayCart();
	});


	function displayCart() {
		var cartArray = shoppingCart.listCart();
		var output = "";
		for (var i in cartArray) {

			var myString = cartArray[i].attributesName;
			let attributes = myString.replace(/[{}\"\[\]']+/g, '');
			let attributeName = attributes.split(',').join(' ');

			output += `<li>
                       <a class="dropdown-item" href="javascript:void(0)">
                          <img src="${cartArray[i].image}" alt="..." />
                          <div class="text">
                             <p>${cartArray[i].name}</p>
                             <span class="price">Price: ${cartArray[i].currency}${cartArray[i].price}</span> <br />
                             <span class="quantity">Qty: ${cartArray[i].count}</span><br>
                             <span class="attributesName">${attributeName}</span>
                             <button class="close delete-item" data-name="${cartArray[i].name}">
                                <i class="fal fa-times-circle"></i>
                             </button>
                          </div>
                       </a>
                    </li>`;
		}

		if (output.count === 0) {
			$('.count').html(0);
		} else {
			$('.count').html(shoppingCart.totalCount());
		}
		$('.show-cart').html(output);
		$('.total-cart').html(shoppingCart.totalCart());


	}

	// Delete item button
	$('.show-cart').on("click", ".delete-item", function (event) {
		var name = $(this).data('name')
		shoppingCart.removeItemFromCartAll(name);
		displayCart();
		Notiflix.Notify.success("Remove from Cart");
	})


	// -1
	$('.show-cart').on("click", ".minus-item", function (event) {
		var name = $(this).data('name')
		shoppingCart.removeItemFromCart(name);
		displayCart();
	})

	// +1
	$('.show-cart').on("click", ".plus-item", function (event) {
		var name = $(this).data('name')
		shoppingCart.addItemToCart(name);
		displayCart();
	})

	// Item count input
	$('.show-cart').on("change", ".item-count", function (event) {
		var name = $(this).data('name');
		var count = Number($(this).val());
		shoppingCart.setCountForItem(name, count);
		displayCart();
	});

	displayCart();
</script>
@stack('extra_scripts')
