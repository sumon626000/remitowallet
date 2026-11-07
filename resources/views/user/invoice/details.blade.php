@extends(userLayout())
@section('title',__('Invoice View'))

@section('content')

    <div class="content {{ containerClass() }}" id="invoice-app" v-cloak>
        <div class="page-header border-0 mb-0">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Invoice")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("Invoice View")</h1>
                </div>
            </div>
        </div>
        <div class="row justify-content-md-center">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="card-header-title">@lang('Invoice Info')</h4>
                    </div>
                    <div class="card-body">

                        <div class="list-group list-group-flush list-group-no-gutters">

                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 ms-2">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <span class="d-block">@lang('Status')</span>
                                            </div>
                                            <div class="col-auto">
                                                {!! $invoice->getStatus() !!}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="list-group-item">
                                <div class="d-flex align-items-center">
                                    <div class="flex-grow-1 ms-2">
                                        <div class="row align-items-center">
                                            <div class="col">
                                                <span class="d-block">@lang('Request Link')</span>
                                            </div>
                                            <div class="col-auto">
                                                <a href="{{route('public.invoice.show',$invoice->has_slug)}}"
                                                   class="link" target="_blank"
                                                   title="click">@lang('Invoice Link')</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if($invoice->paid_at)
                                <x-list-item label="Paid At" value="{{dateTime($invoice->paid_at)}}"/>
                            @endif

                            @if($invoice->rejected_at)
                                <x-list-item label="Rejected At" value="{{dateTime($invoice->rejected_at)}}"/>
                            @endif
                            @if($invoice->status == null && $invoice->reminder_at)
                                <x-list-item label="Last Remind At" value="{{dateTime($invoice->reminder_at)}}"/>
                            @endif
                            <x-list-item label="Invoice Number" value="{{ $invoice->invoice_number }}"/>
                            <x-list-item label="Invoice Issue Date" value="{{dateTime($invoice->created_at)}}"/>

                            @if($invoice->charge_pay == 0)
                                <x-list-item label="Percent Charge ({{$invoice->percentage+0}}%)"
                                     value="{{ $invoice->currency?->symbol }}{{ getAmount($invoice->charge_percentage) }}"
                                     class="text-danger"
                                />
                                <x-list-item label="Fixed Charge ({{$invoice->percentage+0}}%)"
                                     value="{{ $invoice->currency?->symbol }}{{ getAmount($invoice->charge_fixed) }}"
                                     class="text-danger"
                                />
                                <x-list-item label="Overall Charge ({{$invoice->percentage+0}}%)"
                                     value="{{ $invoice->currency?->symbol }}{{ getAmount($invoice->charge) }}"
                                     class="text-danger"
                                />
                            @endif

                        </div>
                    </div>
                </div>
            </div>

            <!--col-6-->
            @include('user.invoice.report')

        </div>
    </div>

@endsection


@push('script')
	<script>
		"use strict";
		var newApp = new Vue({
			el: "#invoice-app",
			data: {
				item: {
					invoiceNumber: "",
				},
				invoice: {
					customer_email: "",
					payment: "",
					first_pay_date: "", items: [], due_date: '', note: ''
				},
				symbol: "", subtotal: 0, taxRate: 0, tax: 0, vatRate: 0, vat: 0, grandTotal: 0
			},
			mounted() {
				this.item.invoiceNumber = "{{$invoice->invoice_number}}"
				this.invoice.customer_email = "{{$invoice->customer_email}}"
				this.invoice.payment = "{{$invoice->frequency}}"
				this.invoice.due_date = "{{$invoice->due_date}}"
				this.invoice.note = "{{$invoice->note}}"
				this.invoice.first_pay_date = "{{$invoice->due_date}}"
				this.invoice.items = @json($invoice->items);
				this.symbol = "{{optional($invoice->currency)->symbol}}"
				this.subtotal = "{{$invoice->subtotal}}"
				this.taxRate = "{{$invoice->tax_rate}}"
				this.tax = "{{$invoice->tax}}"
				this.vatRate = "{{$invoice->vat_rate}}"
				this.vat = "{{$invoice->vat}}"
				this.grandTotal = "{{$invoice->grand_total}}"
			},
			methods: {}
		})
	</script>
@endpush
