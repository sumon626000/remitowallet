

<div class="mb-3">
    <small class="text-cap text-body">@lang($label)</small>

    @if($type === 'select')
        <div class="tom-select-custom">

            <select name="{{ $name ?? '' }}" id="{{ $id ?? 'filter_status' }}"
                    class="js-select form-select"
                    data-hs-tom-select-options='{
                        "hideSearch": {{ isset($hideSearch) ? 'true' : 'false' }}, "searchInDropdown": true,
                        "dropdownWidth": "100%"
                    }'
            >
                <option value="all" selected data-option-template='<span class="d-flex align-items-center">
                        <span class="legend-indicator bg-secondary"></span>@lang('All ' . $label)</span>'>
                </option>

                @forelse($options as $key => $value)
                    @php
                        $indicatorClasses = $indicators[$key] ?? null;
                    @endphp
                    <option value="{{ $key }}" {{ request($name ?? '') === $key ? 'selected' : '' }}
                    @if($indicatorClasses) data-option-template='<span class="d-flex align-items-center">
                        <span class="legend-indicator {{ $indicatorClasses }}"></span>{{ trans($value) }}</span>'
                    @endif
                    >
                        {{ trans($value) }}
                    </option>
                @empty
                @endforelse

            </select>
        </div>
    @elseif($type === 'date')
        <div class="input-group custom">
            <input type="text" class="js-flatpickr form-control"
                   name="{{ $name ?? '' }}"
                   id="filter_date_range"
                   placeholder="Select date"
                   @isset($mode)
                       data-hs-flatpickr-options='{
                             "dateFormat": "d/m/Y",
                             "mode": "range"
                           }'
                   @else
                        data-hs-flatpickr-options='{ "dateFormat": "Y-m-d"}'
                   @endif

                   value="{{ request($name ?? '') }}">
            <span class="input-group-text" id="flatpickr_filter_date_range">
                <i class="bi bi-arrow-counterclockwise"></i>
            </span>
        </div>
    @else
        <input type="text" class="form-control form-control-sm"
               name="{{ $name ?? '' }}"
               id="{{ $id ?? '' }}"
               value="{{ request($name ?? '') }}"
               placeholder="{{ $placeholder ?? '' }}"
               @isset($atcoff)
                   autocomplete="off"
               @endisset
        >
    @endif
</div>

