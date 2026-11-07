<tr>
	<th scope="col" class="text-center">
		<input type="checkbox" class="form-check-input tic-check"
			   name="check-all"
			   id="check-all">
		<label for="check-all"></label>
	</th>
	<th>@lang('Code')</th>
	<th>@lang('Name')</th>
	<th>@lang('Country')</th>
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
			<td data-label="@lang('Code')">{{ __($value->biller_code) }}</td>
			<td data-label="@lang('Name')">{{ __($value->name) }}</td>
			<td data-label="@lang('Country')">{{ __($value->country) }}</td>
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
