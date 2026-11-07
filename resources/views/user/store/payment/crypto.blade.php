@extends('layouts.storeMaster')
@section('page_title')
	{{ __('Pay with ').__(optional($deposit->gateway)->name) }}
@endsection
@section('content')
	<div class="main-content pt-100 pb-100 publicView">
		<section class="section">
			<div class="container-fluid" id="container-wrapper">
				<div class="d-flex justify-content-center">
					<div class="card mb-4 card-primary shadow">
						<div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
							<h6 class="m-0 font-weight-bold card-title">{{ __('Pay with ').__(optional($deposit->gateway)->name) }}</h6>
						</div>
						<div class="card-body text-center">
							<h3 class="text-color"> @lang('PLEASE SEND EXACTLY') <span
									class="text-success"> {{ getAmount($data->amount) }}</span> {{ @$data->currency}}
							</h3>
							<h5>@lang('TO') <span class="text-success"> {{ $data->sendto}}</span></h5>
							<img src="{{$data->img}}">
							<h4 class="text-color bold">@lang('SCAN TO SEND')</h4>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
	</div>
@endsection

