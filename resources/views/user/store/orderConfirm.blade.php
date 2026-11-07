@extends('layouts.storeMaster')
@section('page_title',__('Order Confirmation'))

@section('content')
	<div class="complete-order">
		<div class="container">
			<div class="row justify-content-center">
				<div class="col-lg-6">
					<div class="text-center">
						<img src="{{asset('assets/store/img/icon/package.png')}}" alt="..."
							 class="img-fluid icon mb-3"/>
						<h3>@lang('Order Confirmation')</h3>
						<p>
							@lang('Hey') {{$order->fullname}}
							, @lang("We've got your order! We know you’re going to love it. You can track your order here or shop again here..")
						</p>
						<h5>@lang('Your Order ID:') #{{$order->order_number}}</h5>
					</div>
					<div class="order-summary">
						<h4>@lang('Order Summary')</h4>
						@forelse($order->orderDetails as $orderDetail)
							<div class="item">
								<img
									src="{{getFile(config('filelocation.product.path').optional($orderDetail->product)->thumbnail)}}"
									alt=""/>
								<div class="text">
									<p class="name">{{optional($orderDetail->product)->name}}</p>
									<p class="color">
										@foreach($orderDetail->attr ?? [] as $attr)
											{{optional($attr->attrName)->name}}:{{$attr->name}}
										@endforeach
									</p>
									<p class="quantity">@lang('QTY:') {{$orderDetail->quantity}}</p>
									<p class="price">{{ $store->user?->storeCurrency?->symbol }}{{getAmount($orderDetail->price,2)}}</p>
								</div>
							</div>
						@empty
						@endforelse
						<div class="checkout-summary">
							<ul>
								<li>@lang('Subtotal')
									<span>{{optional($store->user->storeCurrency)->symbol}}{{getAmount($order->total_amount,2)}}</span>
								</li>
								<li>@lang('Shipping')
									<span>{{optional($store->user->storeCurrency)->symbol}}{{getAmount($order->shipping_charge,2)}}</span>
								</li>
								<li>@lang('Grand Total')
									<span>{{optional($store->user->storeCurrency)->symbol}}{{getAmount($order->total_amount+$order->shipping_charge,2)}}</span>
								</li>
							</ul>
						</div>
						<div class="text-center mt-3">
							<a href="{{route('public.product.track',$link)}}"
							   class="btn-custom">@lang('track your order')</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
@endsection
@push('extra_scripts')
	<script>
		'use script'
		shoppingCart.clearCart();
	</script>
@endpush
