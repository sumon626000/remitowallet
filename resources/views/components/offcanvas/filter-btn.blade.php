@props([
    'toggle' => 'offcanvas',
    'target' => 'filterOffcanvas',
    'label' => 'Filter',
    'icon' => 'bi-filter',
    'class' => 'btn btn-white btn-sm',
    'title'=>'Ctrl+F'
])

<button class="{{ $class }}" title="{{ $title }}"
        @if($toggle) data-bs-toggle="{{ $toggle }}" @endif
        @if($target) data-bs-target="#{{ $target }}" @endif
>
    <i class="{{ $icon }}"></i> @lang($label)
</button>



@push('script')
    <script>
        document.addEventListener("keydown", function (event) {
            if (event.ctrlKey && event.key.toLowerCase() === "f") {
                event.preventDefault();

                let offcanvasEl = document.getElementById("filterOffcanvas");
                let offcanvasInstance = bootstrap.Offcanvas.getInstance(offcanvasEl) || new bootstrap.Offcanvas(offcanvasEl);
                offcanvasInstance.toggle();
            }
        });

    </script>
@endpush
