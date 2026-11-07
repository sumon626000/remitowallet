
<div class="col mb-3 mb-lg-4 {{ $class ?? ' col-lg-3 col-md-4' }}">
    <div class="card card-hover-shadow text-center h-100 statRecord border-0 shadow-sm rounded-3">

        @if($pgBar ?? true)
            <div class="card-progress-wrap position-relative">
                <div class="progress card-progress rounded-pill" style="height: 3px;">
                    <div class="progress-bar {{ $pgColor ?? 'bg-secondary' }}" role="progressbar"
                         style="width: {{ $pgValue ?? 100 }}%; transition: width 0.6s ease;"
                         aria-valuenow="{{ $pgValue ?? 100 }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        @endif

        <div class="card-body d-flex align-items-center gap-3 p-4">
            <div class="icon icon-lg icon-soft-primary icon-circle shadow-sm">
                <i class="{{ $icon ?? 'bi bi-send' }}"></i>
            </div>
            <div>
                <span class="h5 text-dark mb-1 d-flex align-items-center">
                    @if($currency ?? true)
                        <span class="fw-semibold text-muted">{{ $basicControl->currency_symbol ?? '$' }}</span>
                    @endif
                    <span class="ms-1 fw-bold">{{ number_format($amount ?? 0, 2) }}</span>
                </span>
                <span class="fw-semibold text-muted small">{{ __($text) ?? __('Last 30 Days Send Money') }}</span>
            </div>
        </div>

    </div>
</div>
