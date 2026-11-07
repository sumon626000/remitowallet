









<div class="content active" id="tab1">
	<div class="invoice">
		<div class="container invoice-container">
			<header class="top">
				<div class="row">
					<div class="col-sm-6">
						<img src="{{getFile($basicControl->site_logo_driver,$basicControl->site_logo)}}"
							 alt="{{$basicControl->site_title}}">
					</div>
					<div class="col-sm-6 text-sm-right">
						<h4>@lang('Invoice')</h4>
						<p>@lang('Invoice Number') - @{{item.invoiceNumber}}</p>
					</div>
				</div>
			</header>

			<main>
				<div class="row mt-3">
					<div class="col-sm-6">
						<h6>@lang('Pay To:')</h6>
						<div>
							<span>{{auth()->user()->email}}</span> <br/>
							<span>{{auth()->user()->mobile}}</span> <br/>
						</div>
					</div>
					<div class="col-sm-6 text-sm-right">
						<h6>@lang('Invoiced To:')</h6>
						<div>
							<span>@{{ invoice.customer_email }}</span> <br/>
						</div>
					</div>
				</div>
				<div class="row my-3">
					<div class="col-sm-12 text-sm-right">
						<h6>@lang('Date:')</h6>
						<span v-if="invoice.payment == 1">@{{invoice.due_date}}</span>
						<span v-else>@{{invoice.first_pay_date}}</span>
					</div>
				</div>
				<div class="card mt-3">
					<div class="card-header background">
						<h6 class="mb-0" class="item.frontColor">@lang('Invoice Summary')</h6>
					</div>
					<div class="card-body p-0">
						<div class="table-responsive invoice">
							<table class="table mb-0">
								<thead>
								<tr>
									<td class="col-8">
										<strong>@lang('Description')</strong>
									</td>
									<td class="col-2 text-center">
										<strong>@lang('Title')</strong>
									</td>
									<td class="col-2 text-right">
										<strong>@lang('Quantity')</strong>
									</td>
									<td class="col-2 text-right">
										<strong>@lang('Price')</strong>
									</td>
								</tr>
								</thead>
								<tbody>
								<tr v-for="(item, index) in invoice.items">
									<td>
										<span class="text-3">@{{ item.description }}</span>
									</td>
									<td class="text-center">@{{ item.title }}</td>
									<td class="text-right">@{{ item.quantity }}</td>
									<td class="text-right">@{{ symbol }}@{{ item.price }}</td>
								</tr>
								</tbody>
								<tfoot class="card-footer background">
								<tr>
									<td colspan="3" class="text-right">
										<strong>@lang('Sub Total:')</strong>
									</td>
									<td class="text-right">@{{ symbol }}
										@{{
										subtotal }}
									</td>
								</tr>
								<tr>
									<td colspan="3" class="text-right">
										<strong>@lang('Tax') (@{{ taxRate }}%)</strong>
									</td>
									<td class="text-right">@{{ symbol }}
										@{{
										tax }}
									</td>
								</tr>
								<tr>
									<td colspan="3" class="text-right">
										<strong>@lang('Vat') (@{{ vatRate }}%)</strong>
									</td>
									<td class="text-right">@{{ symbol }}
										@{{
										vat }}
									</td>
								</tr>
								<tr>
									<td
										colspan="3"
										class="text-right border-bottom-0">
										<strong>@lang('Total:')</strong>
									</td>
									<td class="text-right border-bottom-0">
										@{{ symbol }}@{{grandTotal | decimalFiltered}}
									</td>
								</tr>
								</tfoot>
							</table>
						</div>
					</div>
				</div>
				<br/>
			</main>

			<!-- Footer -->
			@if(!request()->routeIs('invoice.view'))
				<footer class="text-center">
					<div class="btn-group btn-group-sm d-print-none">
						<a :href="'{{route('user.generatePdf')}}?invoice=' + encodeURIComponent(JSON.stringify({...invoice, ...item, tax, vat, subtotal, taxRate, vatRate, grandTotal,clickBtn:0}))"
						   target="_blank" class="btn btn-light">
							<i class="fas fa-print"></i>
							@lang('Print')
						</a>
						<a :href="'{{route('user.generatePdf')}}?invoice=' + encodeURIComponent(JSON.stringify({...invoice, ...item,tax, vat, subtotal, taxRate, vatRate, grandTotal,clickBtn:1}))"
						   class="btn btn-light">
							<i class="fas fa-download"></i>
							@lang('Download')
						</a>
					</div>
				</footer>
			@endif
		</div>
	</div>
</div>


