<div id="productImageDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
     aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-danger">@lang('Image Delete Confirmation')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			@csrf
			<div class="modal-body">
				<p>@lang('Are you want to delete this image?')</p>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
				<form action="" method="post" class="deleteProductImageForm">
					@csrf
					@method('delete')
					<button type="submit" class="btn btn-primary">@lang('Yes')</button>
				</form>
			</div>
		</div>
	</div>
</div>
<div id="productDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title text-danger">@lang('Product Delete Confirmation')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>@lang('Are you want to delete this Product?')</p>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
				<form action="" method="post" class="deleteProductForm">
					@csrf
					@method('delete')
					<button type="submit" class="btn btn-primary">@lang('Yes')</button>
				</form>
			</div>
		</div>
	</div>
</div>
