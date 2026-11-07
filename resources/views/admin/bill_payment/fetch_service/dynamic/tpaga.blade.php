<tr>
	<th scope="col" class="text-center">
		<input type="checkbox" class="form-check-input tic-check"
			   name="check-all"
			   id="check-all">
		<label for="check-all"></label>
	</th>
	<th>@lang('Name')</th>
	<th>@lang('Id')</th>
	<th>@lang('Icon')</th>
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
			<td data-label="@lang('Id')">{{ @$value->id }}</td>
			<td data-label="@lang('Icon')">
				<a href="javascript:void(0)"
				   class="text-decoration-none">
					<div class="d-lg-flex d-block align-items-center ">
						<div class="mr-3"><img
								src="{{@$value->style->icon_url }}"
								alt="user" class="rounded-circle"
								width="40" data-toggle="tooltip">
						</div>
					</div>
				</a>
			</td>
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
