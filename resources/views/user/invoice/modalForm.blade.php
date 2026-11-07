<!-- add service modal -->
<div v-cloak class="modal fade" id="addService" tabindex="-1" aria-labelledby="addServiceLabel"
	 aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="addServiceLabel">@lang('Add service')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" @click.prevent="toggleModal"></button>
			</div>
			<div class="modal-body">
				<form action="">
					<div class="row g-3">
						<div class="input-box col-12">
							<input class="form-control" v-model="item.title" type="text" placeholder="@lang('Title')"/>
							<p class="text-danger">@{{ title_error }}</p>
						</div>

						<div class="input-box col-md-6">
							<input class="form-control" v-model="item.price" type="text" placeholder="@lang('Price')"/>
							<p class="text-danger">@{{ price_error }}</p>
						</div>

						<div class="input-box col-md-6">
							<input class="form-control" v-model="item.quantity" type="text"
								   placeholder="@lang('Quantity')"/>
							<p class="text-danger">@{{ quantity_error }}</p>
						</div>
						<div class="input-box col-12">
                           <textarea class="form-control" v-model="item.description"
							   placeholder="@lang('Description')"
							   cols="30" rows="3"></textarea>
							<p class="text-danger">@{{ description_error }}</p>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Cancel')</button>
				<button type="button" @click.prevent="addServices" class="btn btn-primary">@lang('Add')</button>
			</div>
		</div>
	</div>
</div>

<!-- edit modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-md">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="editModalLabel">@lang('Edit Service')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
			</div>
			<div class="modal-body">
				<form action="">
					<div class="row g-3">
						<div class="input-box col-12">
							<input class="form-control" v-model="item.title" type="text" placeholder="@lang('Title')"/>
							<p class="text-danger">@{{ title_error }}</p>
						</div>

						<div class="input-box col-md-6">
							<input class="form-control" v-model="item.price" type="text" placeholder="@lang('Price')"/>
							<p class="text-danger">@{{ price_error }}</p>
						</div>

						<div class="input-box col-md-6">
							<input class="form-control" v-model="item.quantity" type="number"
								   placeholder="@lang('Quantity')"/>
							<p class="text-danger">@{{ quantity_error }}</p>
						</div>
						<div class="input-box col-12">
                           <textarea class="form-control"
							   v-model="item.description"
							   placeholder="@lang('Description (optional)')"
							   cols="30" rows="3"></textarea>
							<p class="text-danger">@{{ description_error }}</p>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
				<button type="button" @click.prevent="editService()"
						class="btn btn-primary">@lang('Save changes')</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="chargeLimit" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered modal-md modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title" id="editModalLabel">@lang('Charge and Limits')</h4>
				<button
					type="button"
					class="close"
					data-dismiss="modal"
					aria-label="Close">
					<i class="fas fa-times"></i>
				</button>
			</div>
			<div class="modal-body">
				<form action="">
					@if($basicControl->invoice_charge == 0)
						<p class="text-danger">@lang('Charges:') <span>@{{ charges }}</span></p>
					@endif
					<p>@lang('Minimum Limits:') <span>@{{ minLimit }} @{{code}}</span></p>
					<p>@lang('Maximum Limits:') <span>@{{ maxLimit }} @{{code}}</span></p>
				</form>
			</div>
			<div class="modal-footer">
				<button
					type="button"
					class="btn btn-outline-primary"
					data-dismiss="modal">
					@lang('Close')
				</button>
			</div>
		</div>
	</div>
</div>
