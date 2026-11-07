
@props(['for' => 'transactions'])

@php
    $type = strtolower(trim(request('type') ?? ''));
    $map = [
        'user' => 'success',
        'agent' => 'primary',
        'merchant' => 'info',
    ];
    $class = $map[$type] ?? 'secondary';
@endphp

<div class="alert alert-info alert-dismissible fade show" role="alert">
    <span class="fw-semibold">Hello {{ '@' . auth()->user()->username }}</span>,
    @lang('You are viewing') <span class="fw-bold text-{{ $class }}">{{ ucfirst($type ?: 'all') }}</span> {{__($for)}}.

    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
