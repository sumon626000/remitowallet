<div id="storeDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-danger" id="primary-header-modalLabel">@lang('Store Delete')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			@csrf
			<div class="modal-body">
				<p>@lang('Are you want to delete this store?')</p>
			</div>
			<div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
				<form action="" method="post" class="deleteStoreForm">
					@csrf
					@method('delete')
					<button type="submit" class="btn btn-primary">@lang('Submit')</button>
				</form>
			</div>
		</div>
	</div>
</div>
<div id="stageChange" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
	 aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title text-dark font-weight-bold"
					id="primary-header-modalLabel">@lang('Stage Change Confirmation')</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			@csrf
			<div class="modal-body">
				<p>@lang('Are you want to change those orders stage?')</p>
			</div>
			<input type="hidden" class="stage" value="">
			<div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
				<form action="" method="post">
					@csrf
					<button type="button" class="btn btn-primary change-yes">@lang('Yes')</button>
				</form>
			</div>
		</div>
	</div>
</div>
