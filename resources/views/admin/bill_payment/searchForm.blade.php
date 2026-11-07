<x-offcanvas.filter-btn/>


<div class="offcanvas offcanvas-end" tabindex="-1" id="filterOffcanvas">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">@lang('Filter Payment List')</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-3">
        <form id="filter_form" action="">
            <div class="mb-4">
                <span class="text-cap text-body">@lang('Type')</span>
                <div class="row">
                    <div class="col-12">
                        <input type="text" name="type" class="form-control" id="type"
                               value="{{ old('type', request()->type) }}" autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="mb-4">
                <span class="text-cap text-body">@lang('Code')</span>
                <div class="row">
                    <div class="col-12">
                        <input type="text" name="code" class="form-control" id="code"
                               value="{{ old('code', request()->code) }}" autocomplete="off">
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm mb-4">
                    <small class="text-cap text-body">@lang('Methods')</small>
                    <div class="tom-select-custom">
                        <select
                            class="js-select js-datatable-filter form-select form-select-sm"
                            id="filter_status" name="service"
                            data-target-column-index="4" data-hs-tom-select-options='{
                                                                  "placeholder": "All Methods",
                                                                  "searchInDropdown": true,
                                                                  "hideSearch": true,
                                                                  "dropdownWidth": "100"
                                                                }'>
                            <option value="all"
                                    data-option-template='<span class="d-flex align-items-center">
                                            <span class="legend-indicator bg-secondary"></span>All Service</span>'>
                            </option>
                            @foreach($categories as $key => $category)
                                <option value="{{ $category->service }}"
                                        {{ @request()->service == $category->service ? 'selected' : '' }}
                                        data-option-template='<span class="d-flex align-items-center">
                                                    <span class="legend-indicator bg-success"></span>{{str_replace('_',' ',ucfirst($category->service)) }}</span>'>
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-sm mb-4">
                    <small class="text-cap text-body">@lang('Status')</small>
                    <div class="tom-select-custom">
                        <select
                            class="js-select js-datatable-filter form-select form-select-sm"
                            id="filter_status" name="status"
                            data-target-column-index="4" data-hs-tom-select-options='{
                                                                  "placeholder": "All status",
                                                                  "searchInDropdown": true,
                                                                  "hideSearch": true,
                                                                  "dropdownWidth": "100"
                                                                }'>
                            <option value="all"
                                    data-option-template='<span class="d-flex align-items-center">
                                                <span class="legend-indicator bg-secondary"></span>All Status</span>'>
                                @lang('All Status')
                            </option>
                            <option value="active"
                                    {{ @request()->status == 'active' ? 'selected' : '' }}
                                    data-option-template='<span class="d-flex align-items-center">
                                                <span class="legend-indicator bg-success"></span>Active</span>'>
                            </option>
                            <option value="inActive"
                                    {{ @request()->status == 'inActive' ? 'selected' : '' }}
                                    data-option-template='<span class="d-flex align-items-center">
                                                <span class="legend-indicator bg-danger"></span>In-Active</span>'>
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row gx-2 mt-4">
                <div class="col">
                    <button type="submit" class="btn btn-primary w-100" id="filter_button">
                        <i class="bi-search"></i> @lang('Apply')
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
