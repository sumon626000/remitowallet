@extends('layouts.storeMaster')
@section('page_title', __('Product Details'))
@section('content')
	<!-- product details section -->
	<section class="product-details-section">
		<div class="container">
			<div class="row g-4">
				<div class="col-lg-6">
					<div id="mainCarousel" class="carousel mx-auto">
						@forelse($product->productImages as $productImage)
							<div class="carousel__slide"
								data-src="{{getFile($productImage->driver,$productImage->image)}}"
								data-fancybox="gallery" data-caption="">
								<img class="img-fluid" src="{{getFile($product->driver,$product->thumbnail)}}"/>
							</div>
						@empty
						@endforelse
					</div>

					<div id="thumbCarousel" class="carousel max-w-xl mx-auto mb-5">
						@forelse($product->productImages as $productImage)
							<div class="carousel__slide">
								<img class="panzoom__content img-fluid"
									 src="{{getFile($productImage->driver,$productImage->image)}}"/>
							</div>
						@empty
						@endforelse
					</div>
				</div>
				<div class="col-lg-6">
					<div class="product-info">
						<h3 class="title">{{$product->name}}
                            <sup><span class="tag stock " id="stock">
                                <i class="fa fa-spinner fa-spin loader" aria-hidden="false"></i>
						    </span></sup>
                        </h3>
						<span class="tag">@lang('Available')</span>
						@if($product->tag)
							<span class="tag">{{$product->tag}}</span>
						@endif
						<h4 class="price mt-4">@lang('Price:')
                            {{ $product?->user?->StoreCurrency?->symbol}}{{getAmount($product->price)}}
                        </h4>
						<p class="description my-4">
                            {!! __($product->description) !!}
						</p>
						<p>@lang('SKU:') {{$product->sku}}</p>
						<p>@lang('Category:') {{optional($product->category)->name}}</p>
						<div class="d-flex align-items-center">
							<h5>@lang('Quantity:')</h5>
							<div class="quantity ms-2">
								<button class="btn-inc-dec qty decrement">-</button>
								<input type="text" class="form-control qty counter" value="1" readonly/>
								<button class="btn-inc-dec qty increment">+</button>
							</div>
						</div>
						@forelse($attributes as $attr)
							<div class="d-flex align-items- attribute-length">
								<h5 class="pt-2 me-2">{{optional($attr->attribute)->name}}:</h5>
								<div class="radio-box mb-3">
                                    @forelse($attr?->attribute?->attrLists ?? [] as $key => $attrList)
										<span class="tag-item">
                                          <input type="radio" class="btn-check attribute-select"
												 name="attr{{$attr->id}}"
												 id="{{$attr->id}}{{$key}}"
												 autocomplete="off"
												 value="{{$attrList->id}}"
												 @if($key == 0) checked @endif />
                                          <label class="btn btn-primary"
												 for="{{$attr->id}}{{$key}}"> {{$attrList->name}}</label>
                                        </span>
									@empty
									@endforelse
								</div>
							</div>
						@empty
						@endforelse
						<div id="shareBlock"><h5>@lang('Share now:')</h5></div>
						<button data-id="{{$product->id}}"
								data-name="{{$product->name}}" data-price="{{$product->price}}"
								data-image="{{getFile($product->driver,$product->thumbnail)}}"
								data-currency="{{ $product->user?->StoreCurrency?->symbol }}"
								data-attributes=""
								data-route="{{route('product.attributes.list')}}"
								data-quantity="1"
								class="btn-custom mt-4 addToCart cartCount">@lang('Add to cart')</button>
					</div>
				</div>
			</div>
		</div>
		<div class="row justify-content-center">
			<div class="col-lg-8">
				<div class="product-description">
					<div class="navigator">
						<button tab-id="tab1" class="tab active">@lang('Description')</button>
						<button tab-id="tab2" class="tab">@lang('Instruction')</button>
					</div>
					<!-- description -->
					<div id="tab1" class="content active">
						<h4>@lang('Description Area')</h4>
						{!! __($product->description) !!}
					</div>
					<!-- review -->
					<div id="tab2" class="content">{!! __($product->instruction) !!} </div>
				</div>
			</div>
		</div>
	</section>
	@if(count($popularProducts)>0)
		<!-- popular products section -->
		<section class="popular-products-section">
			<div class="container">
				<div class="row">
					<div class="col-12">
						<div class="header-text text-center">
							<h2>@lang('Popular Items')</h2>
							<p class="mx-auto">@lang('Our special offered items price.')</p>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col">
						<div class="popular-products-carousel owl-carousel">
							@forelse($popularProducts as $popularProduct)
								<div class="product-box">
									<div class="img-box">
										<img src="{{getFile($product->driver, $popularProduct->thumbnail)}}"
											class="img-fluid" alt="{{$popularProduct->name}}"/>
									</div>
									<div class="text-box">
										<a href="{{route('public.product.details',[$link,@slug($popularProduct->name),$popularProduct->id])}}"
										   class="title">{{$popularProduct->name}}</a>
										<div class="d-flex justify-content-between align-items-center">
											<h4 class="price mb-0">{{optional($product->user->StoreCurrency)->symbol}}{{$popularProduct->price}}</h4>
											<button class="add-to-cart">
												<i class="fal fa-shopping-cart"></i>
											</button>
										</div>
									</div>
								</div>
							@empty
							@endforelse
						</div>
					</div>
				</div>
			</div>
		</section>
	@endif
@endsection

@push('js-lib')
    <script src="{{ asset('assets/store/js/fancybox.umd.js') }}"></script>
@endpush

@push('extra_scripts')
	<script>
		"use strict";

        // Initialise Carousel
        const mainCarousel = new Carousel(document.querySelector("#mainCarousel"), {
            Dots: false,
        });

        // Thumbnails
        const thumbCarousel = new Carousel(document.querySelector("#thumbCarousel"), {
            Sync: {
                target: mainCarousel,
                friction: 0,
            },
            Dots: false,
            Navigation: false,
            center: true,
            slidesPerPage: 1,
            infinite: true,
        });

        // Customize Fancybox
        Fancybox.bind('[data-fancybox="gallery"]', {
            Carousel: {
                on: {
                    change: (that) => {
                        mainCarousel.slideTo(mainCarousel.findPageForSlide(that.page), {
                            friction: 0,
                        });
                    },
                },
            },
        });



        var stockQty = 0;
		var value = 1;
		$(".counter").val(value);

		$(document).on("click", ".increment", function () {
			value = parseInt(value + 1);
			if (value > stockQty) {
				Notiflix.Notify.warning("@lang('Out Of Stock')");
				value = parseInt(value - 1);
			} else {
				$(".counter").val(value);
				$('.cartCount').data('quantity', value);
			}
		});
		$(document).on("click", ".decrement", function () {
			if (value > 1) {
				value = parseInt(value - 1);
				$(".counter").val(value);
				$('.cartCount').data('quantity', value);
			} else {
				value = 1;
				$(".counter").val(value);
				$('.cartCount').data('quantity', value);
			}
		});

		// Get Product Info
		getStock();
		$(".attribute-select").on('change', function () {
			$('#stock').html(`<i class="fa fa-spinner fa-spin loader" aria-hidden="false"></i>`)
			getStock();
		});

		function getStock() {
			let selectedIds = [];
			let attributeLength = $('.attribute-length').length;
			let productId = '{{ $product->id }}';

			$($(".attribute-select:checked")).each(function (key, value) {
				selectedIds.push($(value).val());
				$('.cartCount').data('attributes', selectedIds);
			});

			if (selectedIds.length != attributeLength) {
				return false;
			}

			$.ajax({
				url: "{{ route('public.stock.check') }}",
				method: "get",
				data: {
					productId: productId,
					attributeIds: selectedIds,
				},
				success: function (res) {
					stockQty = res.stock;
					if (res.message) {
						$('#stock').html(`${res.message}<i class=" loader" style="font-size:24px" aria-hidden="false"></i>`)
						$('.loader').removeClass('fa fa-spinner fa-spin');
					}

					if (res.status == false) {
						$('.qty').attr('disabled', 'true');
						$(".addToCart").attr('disabled', true);
						$('#stock').addClass('OutStock')
						Notiflix.Notify.failure("Out of stock");
					} else {
						$('.qty').prop('disabled', false)
						$(".addToCart").attr('disabled', false);
						$('#stock').removeClass('OutStock')
					}
					let qty = $(".counter").val();
				}
			});
		}

		$(".addToCart").on('click', function () {
			productCheck();
		});

		function productCheck() {
			var cart = JSON.parse(sessionStorage.getItem('shoppingCart'));
			var storage_qty = 0;
			for (let obj in cart) {
				var storage_qty = cart[obj].count;
			}

			let selectedIds = [];
			let productId = '{{ $product->id }}';

			let quantity = $(this).data('quantity');
			let attributeLength = $('.attribute-length').length;

			$($(".attribute-select:checked")).each(function (key, value) {
				selectedIds.push($(value).val());
			});
			if (selectedIds.length != attributeLength) {
				return false;
			}

			$.ajax({
				url: "{{ route('product.attr.check') }}",
				method: "get",
				data: {
					productId: productId,
					attributeIds: selectedIds,
					quantity: quantity,
					storage_qty: storage_qty,
				},
				success: function (res) {
					if (!res.status) {
						$(".addToCart").attr('disabled', true);
					}
				}
			});
		}
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
