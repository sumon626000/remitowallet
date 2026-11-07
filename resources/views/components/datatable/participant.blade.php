
<a class="d-flex align-items-center me-2" href="#">
    <div class="flex-shrink-0">
        {!! optional($user)->profilePicture() !!}
    </div>

    <div class="flex-grow-1 ms-2">
        <h5 class="text-hover-primary mb-0">{{ $user?->name }}
            <i class="bi bi-info-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="{{ $role }}"></i>
        </h5>
        <span class="fs-6 text-body">{{'@'.$user?->username }}</span>
    </div>
</a>
