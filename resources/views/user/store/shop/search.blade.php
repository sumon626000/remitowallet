<div class="col-lg-3 order-1 order-lg-0">
	<div class="filter-area">
		<form action="{{route('public.view',$link)}}" method="get">
			<div class="filter-box">
				<h4>@lang('Search here')</h4>
				<div class="input-group">
					<input type="text" class="form-control" name="search" value="{{@request()->search}}"
						   placeholder="search..."/>
					<button type="button"><i class="fal fa-search" aria-hidden="true"></i></button>
				</div>
			</div>
			<div class="filter-box">
				<h4>@lang('categories')</h4>
				<div class="check-box">
					@forelse($categories as $category)
						<div class="form-check">
							<input class="form-check-input" type="checkbox" name="category[]" value="{{$category->id}}"
								   id="category1" @if(@request()->category)
								@foreach(@request()->category as $categorySingle)
									{{$categorySingle ==$category->id?'checked':'' }}
									@endforeach
								@endif/>
							<label class="form-check-label" for="category1">{{$category->name}} </label>
						</div>
					@empty
					@endforelse
				</div>
			</div>
			<!-- PRICE RANGE -->
			<div class="filter-box">
				<h4>@lang('Filter by price')</h4>
				<div class="input-box">
					<input type="text" class="js-range-slider" name="my_range" value=""/>
					<label for="customRange1"
						   class="form-label mt-3"> {{optional($store->user?->storeCurrency)->symbol}}{{$min}}
						— {{ $store->user?->storeCurrency?->symbol }}{{$max}}</label>
				</div>
			</div>
			<div class="filter-box">
				@forelse($attributes as $attr)
					<h4>{{optional($attr->attribute)->name}}</h4>
					<div class="check-box">
                        @forelse($attr?->attribute?->attrLists ?? [] as $key => $attrList)
							<div class="form-check">
								<input class="form-check-input" name="attrList[]" type="checkbox"
									   value="{{$attrList->id}}" id="brand1" @if(@request()->attrList)
									@foreach(@request()->attrList as $attrListSingle)
										{{$attrListSingle ==$attrList->id?'checked':'' }}
										@endforeach
									@endif/>
								<label class="form-check-label" for="brand1">{{$attrList->name}} </label>
							</div>
						@empty
						@endforelse
					</div>
				@empty
				@endforelse
			</div>
			<div>
				<button class="btn-custom w-100">@lang('Filter')</button>
			</div>
		</form>
	</div>
</div>
@push('extra_scripts')
	<script>
		'use script'
		var min = '{{$min}}'
		var max = '{{$max}}'
		var minRange = '{{$minRange}}'
		var maxRange = '{{$maxRange}}'
        console.log(max)

        $(".js-range-slider").ionRangeSlider({
            type: "double",
            min: min,
            max: max,
            from: minRange,
            to: maxRange,
            grid: true,
        });

	</script>
@endpush
