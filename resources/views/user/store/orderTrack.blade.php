@extends('layouts.storeMaster')
@section('page_title',__('Track Order'))

@section('content')
	<div class="track-order">
		<div class="container">
			<form action="{{route('public.product.track',$link)}}" method="get">
				<div class="row g-4 justify-content-center">
					<div class="input-box input-group col-lg-6">
						<input class="form-control" type="text" name="orderNumber" value="{{@request()->orderNumber}}"
							   placeholder="@lang('Order Number')"/>
						<button class="btn-custom">@lang('Track Order')</button>
					</div>
				</div>
			</form>
			<div class="row">
				@if($order->stage != 5 )
					<div class="col">
						<div class="process">
							<div class="box">
								<div class="icon {{isStageDone('0',$order->stage)}}">
									<img src="{{asset('assets/store/img/icon/process-1.png')}}" alt="..."/>
								</div>
								<h5>@lang('Accepted')</h5>
							</div>
							<div class="box">
								<div class="icon {{isStageDone('1',$order->stage)}}">
									<img src="{{asset('assets/store/img/icon/process-2.png')}}" alt="..."/>
								</div>
								<h5>@lang('Picked')</h5>
							</div>
							<div class="box">
								<div class="icon {{isStageDone('2',$order->stage)}}">
									<img src="{{asset('assets/store/img/icon/process-3.png')}}" alt="..."/>
								</div>
								<h5>@lang('In Transit')</h5>
							</div>
							<div class="box">
								<div class="icon {{isStageDone('3',$order->stage)}}">
									<img src="{{asset('assets/store/img/icon/process-4.png')}}" alt="..."/>
								</div>
								<h5>@lang('Out for delivery')</h5>
							</div>
							<div class="box">
								<div class="icon {{isStageDone('4',$order->stage)}}">
									<img src="{{asset('assets/store/img/icon/process-5.png')}}" alt="..."/>
								</div>
								<h5>@lang('Delivered')</h5>
							</div>
						</div>
					</div>
				@else
					<div class="col">
						<div class="process">
							<div class="box">
								<div class="icon completed">
									<img src="{{asset('assets/store/img/icon/process-1.png')}}" alt="..."/>
								</div>
								<h5>@lang('Accepted')</h5>
							</div>
							<div class="box">
								<div class="icon {{cancelFrom(1,$order->cancel_from)}}">
									<img src="{{asset('assets/store/img/icon/process-2.png')}}" alt="..."/>
								</div>
								<h5>@lang('Picked')</h5>
							</div>
							<div class="box">
								<div class="icon {{cancelFrom(2,$order->cancel_from)}}">
									<img src="{{asset('assets/store/img/icon/process-3.png')}}" alt="..."/>
								</div>
								<h5>@lang('In Transit')</h5>
							</div>
							<div class="box">
								<div class="icon {{cancelFrom(3,$order->cancel_from)}}">
									<img src="{{asset('assets/store/img/icon/process-4.png')}}" alt="..."/>
								</div>
								<h5>@lang('Out for delivery')</h5>
							</div>
							<div class="box">
								<div class="icon {{cancelFrom(4,$order->cancel_from)}}">
									<img src="{{asset('assets/store/img/icon/process-5.png')}}" alt="..."/>
								</div>
								<h5>@lang('Delivered')</h5>
							</div>
							<div class="box">
								<div class="icon cancel">
									<img src="{{asset('assets/store/img/icon/cancel.png')}}" alt="..."/>
								</div>
								<h5>@lang('Cancel')</h5>
							</div>
						</div>
					</div>
				@endif
			</div>
			@if($order->stage != -1)
				<div class="row justify-content-center">
					<div class="col-lg-6">
						<div class="order-status">
							<div class="d-md-flex justify-content-between">
								<div>
									<h5>@lang('Order:') <span>#{{$order->order_number}}</span></h5>
									<p>@lang('Place Order:') {{dateTime($order->created_at)}}</p>
								</div>
								<div>
									<h5>@lang('Delivery To:')</h5>
									<p>{{$order->detailed_address}}</p>
								</div>
							</div>
							<a class="print-invoice" target="_blank"
							   href="{{route('public.product.orderDownload',$order->id)}}">
								<i class="fal fa-file-invoice"></i> @lang('Print Order')
							</a>
						</div>
					</div>
				</div>
			@endif
		</div>
	</div>
@endsection
