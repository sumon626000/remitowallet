<tr>
	<th scope="col" class="text-center">
		<input type="checkbox" class="form-check-input tic-check"
			   name="check-all"
			   id="check-all">
		<label for="check-all"></label>
	</th>
	<th>@lang('Name')</th>
	<th>@lang('Service Type')</th>
	<th>@lang('Country')</th>
	<th>@lang('Min-Max (In Local)')</th>
	<th>@lang('Min-Max (In International)')</th>
	<th>@lang('Transaction Fee (In Local)')</th>
	<th>@lang('Transaction Fee (In International)')</th>
	<th>@lang('Action')</th>
</tr>
</thead>
<tbody>
@if($services)
	@foreach($services as $key => $value)
		<tr>
			<td class="text-center">
				<input type="checkbox" id="chk-{{ $value->id }}"
					   class="form-check-input row-tic tic-check"
					   name="check"
					   value="{{json_encode($value)}}"
					   data-id="{{json_encode($value)}}">
				<label for="chk-{{ $value->id }}"></label>
			</td>
			<td data-label="@lang('Name')">{{ @$value->name }}</td>
			<td data-label="@lang('Service Type')">{{ @$value->serviceType??'Airtime' }}</td>
			<td data-label="@lang('Country')">{{ @$value->countryName ?? $value->country->name}}</td>
			<td data-label="@lang('Min-Max (In Local)')">
				@if($value->denominationType =='FIXED')
					-
				@else
					{{ @$value->minLocalTransactionAmount ?? $value->localMinAmount}}
					- {{ @$value->maxLocalTransactionAmount ?? $value->localMaxAmount }} {{@$value->localTransactionCurrencyCode?? $value->destinationCurrencyCode}}
				@endif
			</td>
			<td data-label="@lang('Min-Max (In International)')">
				@if($value->denominationType =='FIXED')
					-
				@else
					{{ @$value->minInternationalTransactionAmount ?? $value->minAmount }}
					- {{ @$value->maxInternationalTransactionAmount ?? $value->maxAmount}} {{@$value->internationalTransactionFeeCurrencyCode ?? $value->senderCurrencyCode}}
				@endif
			</td>
			<td data-label="@lang('Transaction Fee (In Local)')">
				{{@$value->localTransactionFee??'-'}} {{@$value->localTransactionCurrencyCode??''}}
			</td>
			<td data-label="@lang('Transaction Fee (In International)')">{{@$value->internationalTransactionFee??'-'}} {{@$value->internationalTransactionFeeCurrencyCode??''}}</td>
			<td data-label="@lang('Action')">
				<button
					class="btn btn-sm btn-outline-primary"
					id="singleAdd"
					data-resource="{{json_encode($value)}}"><i
						class="fas fa-plus-circle"></i> @lang('Add')
				</button>
			</td>
		</tr>
	@endforeach
@endif
</tbody>
