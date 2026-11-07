@php
    $modules = getModuleStatuses();
    $hasAgent = !empty($modules['Agent']) && $modules['Agent'] === true;
    $hasMerchant = !empty($modules['Merchant']) && $modules['Merchant'] === true;

    $showUser = $hasAgent || $hasMerchant;
@endphp

@if ($hasAgent || $hasMerchant)
    <div class="tom-select-custom tom-select-sm me-2">
        <select
            class="js-select js-datatable-filter form-select"
            id="filter_type"
            data-hs-tom-select-options='{ "placeholder": "Any Type", "searchInDropdown": false, "hideSearch": true ,"dropdownWidth": "8rem"}'
        >
            <option value="all"
                    data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-secondary"></span>{{ __("All Type") }}</span>'>
            </option>

            @if ($showUser)
                <option value="user" @selected(request('type')=='user')
                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-success"></span>User</span>'>
                </option>
            @endif

            @if ($hasAgent)
                <option value="agent" @selected(request('type')=='agent')
                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-primary"></span>Agent</span>'>
                </option>
            @endif

            @if ($hasMerchant)
                <option value="merchant" @selected(request('type')=='merchant')
                        data-option-template='<span class="d-flex align-items-center"><span class="legend-indicator bg-info"></span>Merchant</span>'>
                </option>
            @endif
        </select>
    </div>

    @push('script')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const typeSelect = document.getElementById('filter_type');

                if (typeSelect) {
                    typeSelect.addEventListener('change', function () {
                        const selectedType = this.value;
                        const currentUrl = new URL(window.location.href);

                        if (selectedType === 'all') {
                            currentUrl.searchParams.delete('type');
                        } else {
                            currentUrl.searchParams.set('type', selectedType);
                        }

                        window.location.href = currentUrl.toString();
                    });
                }
            });
        </script>
    @endpush

@endif


