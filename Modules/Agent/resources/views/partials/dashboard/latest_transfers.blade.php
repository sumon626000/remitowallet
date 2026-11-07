<div class="row">


    <div class="col-lg-8 mb-3 mb-lg-5">
        <div class="card h-100">
            <div class="card-header card-header-content-md-between d-grid d-sm-flex align-items-sm-center gap-2">
                <h4 class="card-header-title">@lang('Latest Transfers')</h4>
                <a href="{{ route('agent.statement.index') }}" class="btn-sm btn btn-primary">
                    <i class="bi-arrow-left-right"></i> @lang('View All')
                </a>
            </div>

            <div class="card-body card-body-height">
                <ul class="list-group list-group-flush list-group-no-gutters">
                    @forelse($transfers as $item)

                        <li class="list-group-item">
                            <div class="d-flex">
                                <div class="flex-shrink-0">
                                    {!! $item?->directionIcon() !!}
                                </div>

                                <div class="flex-grow-1 ms-3">
                                    <div class="row">
                                        <div class="col-7 col-md-5 order-md-1">
                                            <h5 class="mb-0">{{ $item->user?->name }}</h5>
                                            <span class="fs-6 text-body">
                                                {{ str_headline($item->type) }}
                                                @if($item->type == 'cash_in')
                                                    @lang('Sent')
                                                @else
                                                    @lang('Received')
                                                @endif

                                            </span>
                                        </div>

                                        <div class="col-5 col-md-4 order-md-3 text-end mt-2 mt-md-0">

                                            @if($item->type == 'cash_in')
                                                <h5 class="mb-0 ">
                                                    -{{ currencyPosition($item->amount,$item->currency_id) }}
                                                </h5>
                                            @else
                                                <h5 class="mb-0 text-success">
                                                    +{{ currencyPosition($item->amount,$item->currency_id) }}
                                                </h5>
                                            @endif

                                            <span class="fs-6 text-body">{{ dateTime($item->created_at) }}</span>
                                        </div>

                                        <div class="col-auto col-md-3 order-md-2">
                                            @if($item->status == 1)
                                                <span class="badge bg-soft-success text-success rounded-pill">@lang('Completed')</span>
                                            @else
                                                <span class="badge bg-soft-warning text-warning rounded-pill">@lang('Pending')</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </li>
                    @empty
                        <div  class="text-center p-4">
                            <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="..." data-hs-theme-appearance="default">
                            <img class="dataTables-image mb-3" src="{{ asset('assets/admin/img/oc-error-light.svg') }}" alt="..." data-hs-theme-appearance="dark">
                            <p class="mb-0">@lang("No data to show")</p>
                        </div>
                    @endforelse
                </ul>
            </div>

        </div>
    </div>


    <div class="col-lg-4 mb-3 mb-lg-5">
        <div class="card h-100 d-flex justify-content-center align-items-center">
            <div class="text-center">
                <img class="avatar avatar-xl avatar-4x3 mb-4 " src="{{ asset('assets/admin/img/oc-money-profits_light.svg') }}"
                     alt="..." data-hs-theme-appearance="default" style="min-height: 6rem;">
                <img class="avatar avatar-xl avatar-4x3 mb-4" src="{{ asset('assets/admin/img/oc-money-profits.svg') }}"
                     alt="..." data-hs-theme-appearance="dark" style="min-height: 6rem;">

                <span class="text-cap text-body">@lang('Total Commission Earned')</span>
                <span class="d-block display-4 text-success mb-2">{{ currencyPosition($agent_transaction['total_commission']) }}</span>

                <div class="row col-divider">
                    <div class="col text-end">
                    <span class="d-block fw-semibold text-dark">
                       {{ currencyPosition($agent_transaction['total_sent']) }}
                    </span>
                        <span class="d-block">@lang('Cash In')</span>
                    </div>

                    <div class="col text-start">
                        <span class="d-block fw-semibold text-dark">{{ currencyPosition($agent_transaction['total_received']) }}</span>
                        <span class="d-block">@lang('Cash Out')</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
