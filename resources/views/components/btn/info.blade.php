

@props(['route' => null, 'class' => 'btn btn-primary btn-sm', 'icon'=>'clock-history'])

<a href="{{ $route ? route($route) : 'javascript:void(0)' }}" class="{{ $class }}">
    @if($icon)
        <i class="bi bi-{{ $icon }}"></i>
    @endif
    {{ $slot }}
</a>
