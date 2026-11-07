
@if ($isTooltip ?? false)
    <span data-bs-toggle="tooltip" data-bs-html="true" data-bs-placement="{{ $position ?? 'top' }}"
          class="{{ $class ?? '' }}" title="{{ $title ?? '' }}">
        <i class="bi bi-info-circle"></i>
    </span>
@else
    <span class="{{ $class ?? '' }} small ms-2">{{ $content ?? '' }}</span>
@endif


