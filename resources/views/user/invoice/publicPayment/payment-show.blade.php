@extends(template().'layouts.app')
@section('title',__('Invoice Payment'))
@section('content')

	<div class="main-content pt-100 pb-100 publicView">
		<section class="section">
			<div class="container-fluid" id="container-wrapper">
				<div class="row justify-content-md-center">
					<div class="col-lg-4">
						<div class="card mb-4 shadow">
							<div
								class="card-header cardHeaderBgColor py-3 d-flex flex-row align-items-center justify-content-center">
								<h6 class="m-0 font-weight-bold text-white">@lang('Invoice payment')</h6>
							</div>
							<div class="card-body">
								<form action="{{ route('public.invoice.payment.confirm', $invoice->has_slug) }}"
									  method="post">
									@csrf
									<ul class="list-group">
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Requester Name')</span>
											<span>{{ __(optional($invoice->sendBy)->username) }}</span>
										</li>
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Amount')</span>
											<span>{{ (getAmount($invoice->grand_total)) }} {{ __(optional($invoice->currency)->code) }}</span>
										</li>
										@if($invoice->charge_pay == 1)
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span class="text-danger">@lang('Charge')</span>
												<span>{{ (getAmount($invoice->charge)) }} {{ __(optional($invoice->currency)->code) }}</span>
											</li>
										@endif
										@if(!empty($invoice->note))
											<li class="list-group-item list-group-item-action d-flex justify-content-between">
												<span>@lang('Note')</span>
												<span>{{ __($invoice->note) }}</span>
											</li>
										@endif
										<li class="list-group-item list-group-item-action d-flex justify-content-between">
											<span>@lang('Status')</span>
											<span>
													@if($invoice->status == null)
													<span class="badge rounded-pill bg-warning">@lang('Pending')</span>
												@elseif($invoice->status == 'paid')
													<span
														class="badge rounded-pill bg-success">@lang('Paid')</span>
												@elseif($invoice->status == 'rejected')
													<span class="badge rounded-pill bg-danger">@lang('Rejected')</span>>
												@endif
												</span>
										</li>
									</ul>
									@if($invoice->status == null)
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
