

<div class="col-lg-6">
    <div class="card h-100">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h4 class="card-title h4 mt-3">@lang("Latest Transaction")</h4>
            <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                <a href="{{ route('admin.transaction') }}" class="btn btn-sm btn-primary">
                    @lang('View All Transaction')
                </a>
            </div>
        </div>
        <div class="table-responsive position-relative">
            <table class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                <thead class="thead-light">
                <tr>
                    <th>@lang('User')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Charge')</th>
                    <th>@lang('Transaction Id')</th>
                    <th>@lang('Date-time')</th>
                </tr>
                </thead>

                <tbody>
                @forelse($transactions->take(5) as $key => $item)
                    <tr>
                        <td>
                            <a class="d-flex align-items-center me-2"
                               href="{{ route("admin.user.view.profile", optional($item->user)->id) }}">
                                <div class="flex-shrink-0">{!! $item->user?->profilePicture() !!}</div>
                                <div class="flex-grow-1 ms-3">
                                    <h5 class="text-hover-primary mb-0">@lang($item->user?->name)</h5>
                                    <span class="fs-6 text-body">{{ '@'. $item->user?->username }}</span>
                                </div>
                            </a>
                        </td>
                        <td>
                            <h6 class='mb-0 {{ $item->trx_type == '+' ? 'text-success' : 'text-danger' }}'>
                                {{ $item->trx_type. currencyPosition($item->amount,$item->currency_id) }}
                            </h6>
                        </td>
                        <td>
                            <span class='text-danger'>{{ currencyPosition($item->charge,$item->currency_id) }}</span>
                        </td>
                        <td>@lang($item->trx_id)</td>
                        <td>
                            {{ dateTime($item->created_at) }}
                        </td>
                    </tr>
                @empty
                    @include('empty')
                @endforelse
                </tbody>

            </table>
        </div>
    </div>
</div>
