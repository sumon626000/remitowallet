@extends(template().'layouts.app')
@section('title',__('Voucher Payment'))
@section('content')

	<div class="main-content pt-100 pb-100 publicView">
		<section class="section">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-4">
						<div class="card mb-4 shadow">
							<div
								class="card-header cardHeaderBgColor py-3 d-flex flex-row align-items-center justify-content-center">
								<h6 class="m-0 font-weight-bold text-white">@lang('Voucher payment')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('voucher.paymentPublicView', $voucher->utr) }}" method="post">
									@csrf
									<ul class="list-group">
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Requester Name')</span>
											<span>{{ __(optional($voucher->sender)->name) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Amount')</span>
											<span>{{ (getAmount($voucher->transfer_amount)) }} {{ __(optional($voucher->currency)->code) }}</span>
										</li>
										@if(!empty($voucher->note))
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Note')</span>
												<span>{{ __($voucher->note) }}</span>
											</li>
										@endif
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Status')</span>
											<span>
													@if($voucher->status == 1)
													<span class="badge rounded-pill bg-info">@lang('Generated')</span>
												@elseif($voucher->status == 2)
													<span
														class="badge rounded-pill bg-success">@lang('Completed')</span>
												@elseif($voucher->status == 5)
													<span class="badge rounded-pill bg-danger">@lang('Canceled')</span>
												@elseif($voucher->status == 0)
													<span class="badge rounded-pill bg-warning">@lang('Pending')</span>
												@else
													<span class="badge rounded-pill bg-warning">@lang('N/A')</span>
												@endif
												</span>
										</li>
									</ul>
									@if($voucher->status == 1)
										<div class="row mt-3">
											<div class="d-grid gap-2 col-md-6">
												<button type="submit" class="btn btn-success btn-sm btn-block"
														name="status" value="2">
													<i class="fa fa-check-circle"></i> @lang('Confirm Payment')
												</button>
											</div>
											<div class="d-grid gap-2 col-md-6">
												<button type="submit" class="btn btn-danger btn-sm btn-block"
														name="status" value="5">
													<i class="fa fa-times-circle"></i> @lang('Cancel Payment')
												</button>
											</div>
										</div>
									@endif
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
	</div>
@endsection
