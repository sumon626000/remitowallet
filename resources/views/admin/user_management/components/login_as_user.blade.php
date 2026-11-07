<!-- Modal -->
<div class="modal fade" id="loginAsUserModal" tabindex="-1" role="dialog" aria-labelledby="loginAsUserModalLabel" data-bs-backdrop="static"
     aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="loginAsUserModalLabel">
                    <i class="bi bi-box-arrow-in-right me-1"></i> @lang('Confirm Account Login')
                </h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="get" action="" class="loginAccountAction" enctype="multipart/form-data" target="_blank">
                <div class="modal-body">
                    @lang('Are you sure you want to proceed with logging into this account?')
                </div>
                <div class="modal-footer pt-0 border-top-0">
                    <button type="button" class="btn btn-white btn-sm" data-bs-dismiss="modal">@lang('No')</button>
                    <button type="submit" class="btn btn-primary btn-sm">@lang('Yes')</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- End Modal -->
