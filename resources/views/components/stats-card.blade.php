@props([
    'title',
    'value' => 0,
    'total' => 0,
    'percentage' => 0,
    'icon' => 'bi-bar-chart',
    'color' => 'success',
])

<div class="col-sm-6 col-lg-3 mb-3 mb-lg-4">
    <div class="card card-hover-shadow h-100 stats-card">
        <div class="card-body">
            <div class="d-flex">
                <div class="flex-grow-1">
                    <h6 class="card-subtitle mb-3">{{ __($title) }}</h6>
                    <h3 class="card-title js-counter">{{ $value }}</h3>
                    <div class="d-flex align-items-center">
                        <span class="d-block fs-6">@lang('from') {{ $total }}</span>
                        <span class="badge bg-soft-{{ $color }} text-{{ $color }} ms-2">
                            <i class="bi-graph-up"></i> {{ fractionNumber($percentage) ?? 0 }}%
                        </span>
                    </div>
                </div>
                <span class="icon icon-soft-{{ $color }} icon-lg icon-circle ms-3">
                    <i class="bi {{ $icon }} fs-1"></i>
                </span>
            </div>
        </div>
    </div>
</div>
