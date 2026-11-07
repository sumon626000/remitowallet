

<div class="shadow p-3 mb-5 alert alert-soft-dark mb-4 mb-lg-7" role="alert">
    <div class="alert-box d-flex flex-wrap align-items-center gap-2">
        <div class="flex-shrink-0">
            <img class="avatar avatar-xl" src="{{ asset('assets/admin/img/oc-megaphone.svg') }}" alt="..."
                 data-hs-theme-appearance="default">
            <img class="avatar avatar-xl" src="{{ asset('assets/admin/img/oc-megaphone-light.svg') }}" alt="..."
                 data-hs-theme-appearance="dark">
        </div>

        <div class="flex-grow-1 ms-3">
            <h3 class="mb-1">{{ __($title ?? "Attention!") }}</h3>
            <div class="d-flex align-items-center flex-column flex-sm-row gap-2">
                <p class="mb-0 text-body">
                    {{ __($message ?? 'Allow it from notification setting.') }}
                </p>
            </div>
        </div>
        {{ $slot }}
        @if($closeBtn ?? false)
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        @endif
    </div>
</div>
