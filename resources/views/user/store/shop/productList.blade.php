@extends('layouts.storeMaster')
@section('page_title', __('Shop'))
@section('content')
	<!-- shop section -->
	<section class="shop-section">
		<div class="container">
			<div class="row g-lg-5">
				@include('user.store.shop.search')
				<div class="col-lg-9">
					<div class="row g-4 mb-5">
						@forelse($products as $product)
							<div class="col-lg-4 col-md-6">
								<div class="product-box">
									<div class="img-box">
										<img src="{{getFile($product->driver,$product->thumbnail)}}"
											 class="img-fluid" alt=""/>
									</div>
									<div class="text-box">
										<a href="{{route('public.product.details',[$link,slug($product->name),$product->id])}}"
										   class="title">{{$product->name}}</a>
										<div class="d-flex justify-content-between align-items-center">
											<h4 class="price mb-0">{{optional($store->user->storeCurrency)->symbol}}{{$product->price}}</h4>
										</div>
									</div>
								</div>
							</div>
						@empty
						@endforelse
					</div>

					<nav aria-label="Page navigation example">
						<ul class="pagination justify-content-center">
							{{ $products->appends($_GET)->links($theme.'partials.pagination') }}
						</ul>
					</nav>
				</div>
			</div>
		</div>
	</section>
	<div class="container mb-5">
		<div class="row">
			<div class="col">
				<div class="">
					<h3>@lang('Shop Description')</h3>
					<p>
						{{$store->short_description}}
					</p>
				</div>
			</div>
		</div>
	</div>
	<div id="logbox">

	</div>
@endsection

