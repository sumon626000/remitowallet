

<div class="{{ $mobile ? 'd-lg-none mt-3' : 'col-lg-4 d-none d-lg-block' }}">
    <div class="card">
        <div class="card-header card-header-content-between">
            <h4 class="card-header-title">@lang('Transaction Details') </h4>
        </div>
        <div class="card-body">
            <div class="list-group list-group-flush list-group-no-gutters showCharge">
                <div class="text-center">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}"
                         alt="..." data-hs-theme-appearance="default">
                    <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                         alt="..." data-hs-theme-appearance="dark">
                    <p class="mb-0">@lang("No Details Found")</p>
                </div>
            </div>
        </div>
    </div>
</div>
