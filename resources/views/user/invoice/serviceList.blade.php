<div class="table-responsive mt-5">
	<table class="table table-align-middle">
		<thead class="thead-light">
		<tr>
			<th class="col-5">@lang('Title')</th>
			<th class="col-3">@lang('Quantity')</th>
			<th class="col-3">@lang('Price')</th>
			<th class="col-1" class="text-end">
				@lang('Action')
			</th>
		</tr>
		</thead>
		<tbody>
            <tr v-for="(item, index) in invoice.items">
                <td> @{{ item.title }}</td>
                <td> @{{ item.quantity }}</td>
                <td>{{$basicControl->currency_symbol}}@{{ item.price }}</td>
                <td class="action">
                    <div class="d-flex justify-content-end btn-group">
                        <button type="button" class="btn btn-white btn-icon btn-sm"
                                data-bs-toggle="modal" data-bs-target="#editModal" @click.prevent="editItem(index)">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        </button>
                        <button type="button" class="btn btn-soft-danger btn-icon btn-sm"
                                data-bs-toggle="modal" data-bs-target="#describeModal" @click.prevent="removeItem(index)">
                            <i class="bi bi-trash" aria-hidden="true"></i>
                        </button>
                    </div>
                </td>
            </tr>
        </tbody>

        <tfoot class="thead-light">
            <tr class="estimation top">
                <td></td>
                <td><span>@lang('Subtotal')</span></td>
                <td>
                    <div class="input-group">
                        <input type="text" v-model="subtotal" class="form-control" placeholder="" readonly/>
                        <div class="input-group-append">
                            <label class="form-control">@{{code}}</label>
                        </div>
                    </div>
                </td>
                <td></td>
            </tr>
            <tr class="estimation">
                <td></td>
                <td><span>@lang('Tax')</span></td>
                <td>
                    <div class="input-group">
                        <input type="number" v-model="item.tax" step="0.001" v-on:keyup="calculateTax" class="form-control"
                               placeholder=""/>
                        <div class="input-group-append">
                            <label class="form-control">%</label>
                        </div>
                    </div>
                </td>
                <td></td>
            </tr>
            <tr class="estimation">
                <td></td>
                <td><span>@lang('Vat')</span></td>
                <td>
                    <div class="input-group">
                        <input type="number" v-model="item.vat" step="0.001" v-on:keyup="calculateVat" class="form-control"
                               placeholder=""/>
                        <div class="input-group-append">
                            <label class="form-control">%</label>
                        </div>
                    </div>
                </td>
                <td></td>
            </tr>
            <tr class="estimation">
                <td></td>
                <td><span>@lang('Grandtotal')</span></td>
                <td>
                    <span>@{{ symbol }}@{{grandTotal | decimalFiltered}}</span>
                </td>
                <td></td>
            </tr>
        </tfoot>
	</table>
</div>
<div class="row">
	<div class="col-md-12">
		<button type="button" class="btn btn-primary btn-sm btn-block"
				@click="saveInvoice('send')">
			@lang('Save and Send')
		</button>
	</div>
</div>
