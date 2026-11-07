<div class="row">
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('Product Name')" name="name" value="{{@request()->name}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<input placeholder="@lang('SKU')" name="sku" value="{{@request()->sku}}" type="text"
				   class="form-control form-control-sm">
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="category_id" class="form-control form-control-sm">
				<option value="">@lang('All Category')</option>
				@foreach($categories as $key => $category)
					<option
						value="{{ $category->id }}" {{ @request()->category_id == $category->id ? 'selected' : '' }}> {{$category->name}} </option>
				@endforeach
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group search-currency-dropdown">
			<select name="status" class="form-control form-control-sm">
				<option value="">@lang('All Status')</option>
				<option
					value="1" {{ @request()->status == '0' ? 'selected' : '' }}>@lang('Active')</option>
				<option
					value="0" {{ @request()->status == '1' ? 'selected' : '' }}>@lang('Inactive')</option>
			</select>
		</div>
	</div>
	<div class="col-md-2">
		<div class="form-group">
			<button type="submit" class="btn btn-primary btn-sm btn-block">@lang('Search')</button>
		</div>
	</div>
</div>
