<div id="addCategory" class="modal fade" data-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="primary-header-modalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('New Category')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('user.category.save')}}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>@lang('Name')</label>
                            <div class="form-group">
                                <input type="text" name="name" class="form-control" required>
                                @error('name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <label class="row form-check form-switch my-4"
                           for="instant_delivery">
                                            <span class="col-8 col-sm-9 ms-0">
                                              <span class="d-block text-dark">@lang("Status")</span>
                                              <span
                                                  class="d-block fs-5">@lang("Enable Status to operation the category")</span>
                                            </span>
                        <span class="col-4 col-sm-3 text-end">
                                                    <input type="hidden" value="0" name="status"/>
                                                    <input
                                                        class="form-check-input editStatus @error('status') is-invalid @enderror"
                                                        type="checkbox" name="status"
                                                        id="status" value="1"
                                                    >
                                                </span>
                        @error('status')
                        <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div id="editCategory" class="modal fade" data-backdrop="static" tabindex="-1" role="dialog"
     aria-labelledby="primary-header-modalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">@lang('Update Category')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="" method="POST" class="editFormAction">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <label>@lang('Name')</label>
                            <div class="form-group">
                                <input type="text" name="name" class="form-control editName" required>
                                @error('name')
                                <span class="text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <label class="row form-check form-switch my-4"
                           for="instant_delivery">
                                            <span class="col-8 col-sm-9 ms-0">
                                              <span class="d-block text-dark">@lang("Status")</span>
                                              <span
                                                  class="d-block fs-5">@lang("Enable Status to operation the category")</span>
                                            </span>
                        <span class="col-4 col-sm-3 text-end">
                                                    <input type="hidden" value="0" name="status"/>
                                                    <input
                                                        class="form-check-input editStatus @error('status') is-invalid @enderror"
                                                        type="checkbox" name="status"
                                                        id="status" value="1"
                                                    >
                                                </span>
                        @error('status')
                        <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </label>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <button type="submit" class="btn btn-primary">@lang('Update')</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="categoryDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
     aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-danger">@lang('Category Delete Confirmation')</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>@lang('Are you want to delete this category?')</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                <form action="" method="post" class="deleteCategoryForm">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                </form>
            </div>
        </div>
    </div>
</div>
