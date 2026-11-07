<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>@lang('Invoice Template')</title>
	<style>
		* {
			font-family: "DejaVu Sans", sans-serif;
		}

		.background {
			background-color: #dfe4ea !important;
		}

		.black {
			color: #2f2d60 !important;
		}

	</style>
</head>
<body style="background: #f7f7f8; font-size: 15px; margin: 0; padding: 0; color: #2f2d52">
<div class="invoice" style="margin: auto; padding: 30px; max-width: 700px; background-color: #fff; height: 100%">
	<!-- header -->
	<div style="border-bottom: 1px solid #e5e5e5; width: 100%; position: relative; height: 85px">
		<div style="width: 50%; position: absolute; left: 0; top: 0">
			<h2 style="font-size: 36px; color: #1e90ff; margin: 15px 0 0 0">
                <img src="{{getFile(basicControl()->logo_driver, basicControl()->logo)}}"
                     alt="{{ basicControl()->site_title }}">
			</h2>
		</div>
		<div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
			<h4 style="font-size: 24px; margin: 0 0 15px 0">@lang('Invoice')</h4>
			<p><strong>@lang('Invoice No:')</strong> {{$invoice_number}}</p>
		</div>
	</div>

	<div style="width: 100%; position: relative; height: 100px">
		<div style="width: 50%; position: absolute; left: 0; top: 0">
			<h6 style="font-size: 18px; margin: 15px 0">@lang('Pay To:')</h6>
			<div>
				<p style="margin: 0 0 5px 0">{{@$email}}</p>
				<p style="margin: 0 0 5px 0">{{@$phone}}</p>
			</div>
		</div>
		<div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
			<h6 style="font-size: 18px; margin: 15px 0">@lang('Invoiced To:')</h6>
			<div>
				<p style="margin: 0 0 5px 0">{{@$customer_email}}</p>
			</div>
		</div>
	</div>
	<div style="width: 100%; position: relative; height: 90px">
		<div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
			<h6 style="font-size: 18px; margin: 5px 0">@lang('Date:')</h6>
			<div>
				<p style="margin: 0 0 5px 0">
					@if($payment == 1)
						{{\Carbon\Carbon::parse($due_date)->format('d/m/Y')}}
					@else
						{{\Carbon\Carbon::parse($first_pay_date)->format('d/m/Y')}}
					@endif
				</p>
			</div>
		</div>
	</div>

	<div class="card" style="border: 1px solid rgba(0, 0, 0, 0.125)">
		<div
			class="card-header background"
			style="padding: 15px; border-bottom: 1px solid #00000020">
			<h6 class="black" style="font-size: 18px; margin: 0">@lang('Invoice Summary')</h6>
		</div>
		<div class="card-body">
			<div class="table-responsive">
				<table
					class="table"
					style="
                        color: #333;
                        vertical-align: top;
                        border-color: #dee2e6;
                        caption-side: bottom;
                        border-collapse: collapse;
                        width: 100%;
                     ">
					<thead>
					<tr>
						<td style="padding: 15px; border-bottom: 1px solid #00000020">
							<strong>@lang('Description')</strong>
						</td>
						<td style="padding: 15px; border-bottom: 1px solid #00000020">
							<strong>@lang('Title')</strong>
						</td>
						<td style="padding: 15px; border-bottom: 1px solid #00000020; text-align: right">
							<strong>@lang('Quantity')</strong>
						</td>
						<td style="padding: 15px; border-bottom: 1px solid #00000020; text-align: right">
							<strong>@lang('Price')</strong>
						</td>
					</tr>
					</thead>
					<tbody>
					@forelse($items as $item)
						<tr>
							<td style="padding: 15px; border-bottom: 1px solid #00000020">
								{{$item->description}}
							</td>
							<td style="padding: 15px; border-bottom: 1px solid #00000020">{{$item->title}}</td>
							<td style="padding: 15px; border-bottom: 1px solid #00000020">{{$item->quantity}}</td>
							<td style="padding: 15px; border-bottom: 1px solid #00000020">{{$currency}}{{$item->price}}</td>
						</tr>
					@empty
					@endforelse
					</tbody>
					<tfoot class="card-footer background">
					<tr>
						<td style="padding: 15px; text-align: right" colspan="3">
							<strong class="black">@lang('Sub Total:')</strong>
						</td>
						<td class="black" style="padding: 15px; text-align: right" colspan="1">
							{{$currency}}{{$subtotal}}
						</td>
					</tr>
					<tr>
						<td style="padding: 15px; text-align: right" colspan="3">
							<strong class="black">@lang('Tax:')({{$taxRate}}%)</strong>
						</td>
						<td class="black" style="padding: 15px; text-align: right" colspan="1">
							{{$currency}}{{$tax}}
						</td>
					</tr>
					<tr>
						<td style="padding: 15px; text-align: right" colspan="3">
							<strong class="black">@lang('Vat:')({{$vatRate}}%)</strong>
						</td>
						<td class="black" style="padding: 15px; text-align: right" colspan="1">
							{{$currency}}{{$vat}}
						</td>
					</tr>
					<tr>
						<td style="padding: 15px; text-align: right" colspan="3">
							<strong class="black">@lang('Total:')</strong>
						</td>
						<td class="black" style="padding: 15px; text-align: right"
							colspan="1">{{$currency}}{{$grandTotal}}</td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
	<div style="width: 100%; position: relative; height: 155px">
		<div style="width: 50%; position: absolute; left: 0; top: 0">
			<h6 style="font-size: 18px; margin: 15px 0">@lang('Note:') {{$note}}</h6>
		</div>
	</div>
	<footer style="margin-top: 15px">
		<div class="btn-group" style="text-align: center; margin-top: 50px">
			<a href="{{$url}}"
			   style="
                     background: #1e90ff;
                     color: #fff;
                     margin: 5px;
                     width: 100px;
                     border-radius: 100px;
                     text-decoration: none;
                     padding: 12px 20px;">
				@lang('Pay') {{config('basic.currency_symbol')}}{{$grandTotal}}
			</a>
			<a href="{{$reject_url}}"
			   style="
                     background: #2f2d52;
                     color: #fff;
                     margin: 5px;
                     width: 100px;
                     border-radius: 100px;
                     text-decoration: none;
                     padding: 12px 20px;">
				@lang('Reject')
			</a>
		</div>
	</footer>
</div>
</body>
</html>
