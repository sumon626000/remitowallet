<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<title>Order Slip</title>
</head>
<body style="font-size: 15px; margin: 0; padding: 0; color: #2f2d52">
<div class="invoice" style="margin: auto; padding: 30px; max-width: 700px; background-color: #fff">
	<!-- header -->
	<div style="border-bottom: 1px solid #e5e5e5; width: 100%; position: relative; height: 85px">
		<div style="width: 50%; position: absolute; left: 0; top: 0">
			<p><strong>Order No:</strong> #{{$orderNumber}}</p>
			<p><strong>Date:</strong> {{$date}}</p>
		</div>
		<div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
			<img style="font-size: 36px; color: #2bb673; margin: 15px 0 0 0" src={{$shopImage}}>
		</div>
	</div>

	<div style="width: 100%; position: relative; height: 155px">
		<div style="width: 50%; position: absolute; left: 0; top: 0">
			<h6 style="font-size: 18px; margin: 15px 0">Order From:</h6>
			<div>
				<p style="margin: 0 0 5px 0">{{$buyerName}}</p>
				<p style="margin: 0 0 5px 0">{{$buyerEmail}}</p>
				<p style="margin: 0 0 5px 0">{{$buyerPhone}}</p>
				<p style="margin: 0 0 5px 0">{{$buyerAddress}}</p>
			</div>
		</div>
		<div style="width: 50%; position: absolute; right: 0; top: 0; text-align: right">
			<h6 style="font-size: 18px; margin: 15px 0">Shop To:</h6>
			<div>
				<p style="margin: 0 0 5px 0">{{$shopName}}</p>
			</div>
		</div>
	</div>

	<div class="card" style="border: 1px solid rgba(0, 0, 0, 0.125)">
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
                     "
				>
					<thead class="card-header" style="background-color: #f1f2f6; border-bottom: 1px solid #00000020">
					<tr>
						<td style="padding: 15px">
							<strong>Item</strong>
						</td>
						<td style="padding: 15px">
							<strong>Qty</strong>
						</td>
						<td style="padding: 15px">
							<strong>Price</strong>
						</td>
						<td style="padding: 15px; text-align: right">
							<strong>Total</strong>
						</td>
					</tr>
					</thead>
					<tbody>
					@forelse($items as $item)
						<tr>
							<td style="padding: 15px; border-bottom: 1px solid #00000020">{{optional($item->product)->name}}</td>
							<td style="padding: 15px; border-bottom: 1px solid #00000020">{{$item->quantity}}</td>
							<td style="padding: 15px; border-bottom: 1px solid #00000020">{{$currency}}{{number_format($item->price,2)}}</td>
							<td style="padding: 15px; border-bottom: 1px solid #00000020; text-align: right">{{$currency}}{{number_format($item->total_price,2)}}</td>
						</tr>
					@empty
					@endforelse
					</tbody>
					<tfoot class="card-footer">
					<tr>
						<td style="padding: 15px; border-bottom: 1px solid #00000020; text-align: right" colspan="3">
							<strong>Sub Total:</strong>
						</td>
						<td style="padding: 15px; border-bottom: 1px solid #00000020; text-align: right" colspan="2">
							{{$currency}}{{number_format($subtotal,2)}}
						</td>
					</tr>
					<tr>
						<td style="padding: 15px; border-bottom: 1px solid #00000020; text-align: right" colspan="3">
							<strong>Shipping:</strong>
						</td>
						<td style="padding: 15px; border-bottom: 1px solid #00000020; text-align: right" colspan="2">
							{{$currency}}{{number_format($shipping,2)}}
						</td>
					</tr>
					<tr>
						<td style="padding: 15px; text-align: right" colspan="3">
							<strong>Total amount:</strong>
						</td>
						<td style="padding: 15px; text-align: right"
							colspan="2">{{$currency}}{{number_format($totalAmount,2)}}</td>
					</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</div>
</div>
</body>
</html>
