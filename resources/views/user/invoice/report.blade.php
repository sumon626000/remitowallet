<div class="col-md-6 mb-5 mb-lg-0">
    <!-- Card -->
    <div class="card card-lg mb-5">
        <div class="card-body">
            <div class="row justify-content-lg-between">
                <div class="col-sm order-2 order-sm-1 mb-3">
                    <div class="mb-2">
                        <img class="avatar avatar-xs" src="{{getFile($basicControl->favicon_driver,$basicControl->favicon)}}"
                             alt="{{$basicControl->site_title}}">
                    </div>
                    <h1 class="h2 text-primary">{{$basicControl->site_title}}</h1>
                </div>
                <!-- End Col -->

                <div class="col-sm-auto order-1 order-sm-2 text-sm-end mb-3">
                    <div class="mb-3">
                        <h2>@lang('Invoice')</h2>
                        <span class="d-block">@{{ item.invoiceNumber }}</span>
                    </div>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class="row justify-content-md-between mb-3">
                <div class="col-md">
                    <h4>@lang('Pay To:')</h4>
                    <address>
                        <span>{{auth()->user()->email}}</span><br>
                        <span>{{auth()->user()->mobile}}</span>
                    </address>
                </div>
                <!-- End Col -->

                <div class="col-md text-md-end">
                    <h4>@lang('Invoiced To:')</h4>
                    <address>
                        <span>@{{ invoice.customer_email }}</span>
                    </address>
                </div>
                <!-- End Col -->
            </div>
            <!-- End Row -->

            <div class="row justify-content-md-between mb-3">
                <div class="col-md text-md-end">
                    <dl class="row">
                        <dt class="col-sm-8">@lang('Date:')</dt>
                        <dd class="col-sm-4">
                            <span v-if="invoice.payment == 1">@{{ invoice.due_date }}</span>
                            <span v-else>@{{ invoice.first_pay_date }}</span>
                        </dd>
                    </dl>
                </div>
            </div>
            <!-- End Row -->

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-borderless table-nowrap table-align-middle">
                    <thead class="thead-light">
                    <tr>
                        <th>@lang('Description')</th>
                        <th>@lang('Title')</th>
                        <th class="text-center">@lang('Quantity')</th>
                        <th class="text-end">@lang('Price')</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="(item, index) in invoice.items">
                        <td>@{{ item.description }}</td>
                        <td>@{{ item.title }}</td>
                        <td class="text-center">@{{ item.quantity }}</td>
                        <td class="text-end">@{{ symbol }}@{{ item.price }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <hr class="my-5">

            <div class="row justify-content-md-end mb-3">
                <div class="col-md-8 col-lg-7">
                    <dl class="row text-sm-end">
                        <dt class="col-sm-6">@lang('Subtotal:')</dt>
                        <dd class="col-sm-6">@{{ symbol }}@{{ subtotal }}</dd>

                        <dt class="col-sm-6">@lang('Tax') (@{{ taxRate }}%)</dt>
                        <dd class="col-sm-6">@{{ symbol }}@{{ tax }}</dd>

                        <dt class="col-sm-6">@lang('Vat') (@{{ vatRate }}%)</dt>
                        <dd class="col-sm-6">@{{ symbol }}@{{ vat }}</dd>

                        <dt class="col-sm-6">@lang('Total:')</dt>
                        <dd class="col-sm-6">
                            <strong>@{{ symbol }}@{{ grandTotal | decimalFiltered }}</strong>
                        </dd>
                    </dl>
                </div>
            </div>
            <!-- End Summary -->

        </div>
    </div>

    @if(!request()->routeIs('user.invoice.view'))
        <div class="d-flex justify-content-end d-print-none gap-3">
            <a class="btn btn-white"
               :href="'{{route('user.generatePdf')}}?invoice=' + encodeURIComponent(JSON.stringify({...invoice, ...item, tax, vat, subtotal, taxRate, vatRate, grandTotal, clickBtn:1}))"
               >
                <i class="bi-file-earmark-arrow-down me-1"></i> @lang('PDF')
            </a>

            <a class="btn btn-primary" target="_blank"
               :href="'{{route('user.generatePdf')}}?invoice=' + encodeURIComponent(JSON.stringify({...invoice, ...item, tax, vat, subtotal, taxRate, vatRate, grandTotal, clickBtn:0}))"
            >
                <i class="bi-printer me-1"></i> @lang('Print details')
            </a>
        </div>
    @endif
</div>



