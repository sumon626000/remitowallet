
@props([
    'id' => 'filterOffcanvas',
    'title' => null,
    'datatable' => null
])

<div class="offcanvas offcanvas-end" tabindex="-1" id="{{ $id }}">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title">
            {!! $title ? __($title) : '<i class="bi-search me-1"></i> ' . __('Filter') !!}
        </h5>

        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body p-3">
        <form id="filter_form" action="">
            {{ $slot }}

            <div class="row gx-2 mt-4">
                @isset($datatable)
                    <div class="col">
                        <button type="button" id="clear_filter" class="btn btn-white w-100">@lang('Clear Filters')</button>
                    </div>
                @endisset
                <div class="col">
                    <button type="{{ isset($datatable) ? 'button' : 'submit' }}" class="btn btn-primary w-100" id="filter_button">
                        <i class="bi-search"></i> @lang('Apply')
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>



