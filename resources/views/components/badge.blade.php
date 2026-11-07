

<span class="badge bg-soft-{{ $type ?? 'info' }} text-{{ $type ?? 'info' }}">
    @if($ind ?? false)
        <span class="legend-indicator bg-{{ $type ?? 'info' }}"></span>
    @endif
    {{ trans($text ?? 'N\A') }}
</span>

