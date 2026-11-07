<div class="card mb-3 mb-lg-5">
    <div class="card-header card-header-content-between">
        <h4 class="card-header-title">@lang("Latest Users")</h4>

        <a class="btn btn-white btn-sm" href="{{ route("admin.users") }}">@lang("View All")</a>
    </div>
    <div class="table-responsive">
        <table class="table table-borderless table-thead-bordered table-align-middle card-table">
            <thead class="thead-light">
            <tr>
                <th>@lang('Full Name')</th>
                <th>@lang('Country')</th>
                <th>@lang('Status')</th>
                <th>@lang('Action')</th>
            </tr>
            </thead>
            <tbody>
            @forelse($latestUser as $user)
                <tr>
                    <td>
                        <a class="d-flex align-items-center me-2"
                           href="{{ route("admin.user.view.profile", $user->id) }}">
                            <div class="flex-shrink-0">
                                {!! $user->profilePicture() !!}
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="text-hover-primary mb-0">{{ $user->firstname . ' ' . $user->lastname }}</h5>
                                <span class="fs-6 text-body">{{'@'. $user->username }} {!! renderUserTypeBadge($user->type) !!}</span>
                            </div>
                        </a>
                    </td>
                    <td>
                        {{ $user->country ?? 'N/A' }}
                    </td>
                    <td>
                        @if($user->status == 1)
                            <span class="badge bg-soft-success text-success">
                                <span class="legend-indicator bg-success"></span>@lang("Active")
                            </span>
                        @else
                            <span class="badge bg-soft-danger text-danger">
                                <span class="legend-indicator bg-danger"></span>@lang("Inactive")
                            </span>
                        @endif
                    </td>
                    <td>
                        <div class="btn-group" role="group">
                            <a class="btn btn-white btn-sm" href="{{ route('admin.user.edit', $user->id) }}">
                                <i class="bi-pencil-square me-1"></i> @lang("Edit")
                            </a>
                            <div class="btn-group">
                                <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown" >
                                    <a class="dropdown-item" href="{{ route('admin.user.view.profile', $user->id) }}">
                                        <i class="bi-eye dropdown-item-icon"></i> @lang("View Profile")
                                    </a>
                                    <a class="dropdown-item" href="{{ route('admin.send.email', $user->id) }}"> <i
                                            class="bi-envelope dropdown-item-icon"></i> @lang("Send Mail") </a>
                                    <a class="dropdown-item loginAccount" href="javascript:void(0)"
                                       data-route="{{ route('admin.login.as.user', $user->id) }}"
                                       data-bs-toggle="modal" data-bs-target="#loginAsUserModal">
                                        <i class="bi bi-box-arrow-in-right dropdown-item-icon"></i>
                                        @lang("Login As") {{ ucfirst($user->type) }}
                                    </a>
                                    <a class="dropdown-item addBalance" href="javascript:void(0)"
                                       data-route="{{ route('admin.user.update.balance', $user->id) }}"
                                       data-balance="{{ currencyPosition($user->balance) }}"
                                       data-bs-toggle="modal" data-bs-target="#addBalanceModal">
                                        <i class="bi bi-cash-coin dropdown-item-icon"></i>
                                        @lang("Manage Balance")
                                    </a>
                                </div>
                            </div>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <div class="text-center p-4">
                        <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description" data-hs-theme-appearance="default">
                        <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="Image Description" data-hs-theme-appearance="dark">
                        <p class="mb-0">@lang("No data to show")</p>
                    </div>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
