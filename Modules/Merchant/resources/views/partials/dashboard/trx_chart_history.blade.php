
<div class="row mb-5">

    <div class="col-md-6 col-lg-8 order-1 ">
        <div class="card h-100">
            <div class="card-header nav-align-top">
                <ul class="nav nav-segment">
                    <li class="nav-item">
                        <button type="button" class="nav-link active btn-sm btn" id="income-tab" role="tab"
                                data-bs-toggle="tab" data-bs-target="#transaction-tab"
                                data-type="Transaction" aria-selected="true">@lang('Transaction')
                        </button>
                    </li>
                    <li class="nav-item">
                        <button type="button" class="nav-link btn-sm btn" id="payout-tab" role="tab"
                                data-type="Payout">@lang('Payout')</button>
                    </li>
                </ul>
            </div>
            <div class="card-body" data-block="incomeChartBlock">
                <div class="tab-content p-0">
                    <div class="tab-pane fade show active" id="transaction-tab" role="tabpanel">
                        <div class="d-block mb-6">
                            <p class="mb-1">@lang('Total Amount')</p>
                            <h2 class="mb-0 me-1" id="totalAmount"></h2>
                        </div>
                        <div id="incomeChart"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions -->
    <div class="col-md-6 col-lg-4 order-2 ">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title m-0 me-2">@lang('Transactions')</h5>
                <div class="dropdown">
                    <span class="p-0" type="button" id="transactionID" data-bs-toggle="dropdown">
                        <i class="bi-three-dots-vertical"></i>
                    </span>
                    <div class="dropdown-menu dropdown-menu-end" >
                        <a class="dropdown-item" href="{{ route('merchant.transaction') }}">@lang('View all transactions')</a>
                    </div>
                </div>
            </div>
            <div class="card-body pt-4">
                @if(!$transactions->isEmpty())
                    <ul class="p-0 m-0">
                        @forelse($transactions as $item)
                            <li class="d-flex align-items-center mb-6">
                                <div class="icon icon-md  flex-shrink-0 me-3
                            {{ ($item->trx_type == '+') ? 'icon-soft-success' : 'icon-soft-danger' }} ">
                                    <i class="{{ $item->icon }}"></i>
                                </div>
                                <div class="d-flex w-100 flex-wrap align-items-center justify-content-between gap-2">
                                    <div class="me-2">
                                        <div class="d-block">{{ $item->type }}</div>
                                        <h6 class="fw-normal mb-0">{{ $item->remarks }}</h6>
                                    </div>
                                    <div class="user-progress d-flex align-items-center gap-2">
                                        <h4 class="fw-normal mb-0">{{ $item->trx_type }}{{ $item->amount }}</h4>
                                        <span class="text-body-secondary">{{ $item->currency?->code ?? 'USD' }}</span>
                                    </div>
                                </div>
                            </li>
                        @empty
                        @endforelse
                    </ul>
                @else
                    <div class="d-flex justify-content-center align-items-center" style="min-height: 300px;">
                        <table class="w-100">
                            @include('empty')
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


@push('style')
    <style>
        .apexcharts-legend-marker{
            margin-right: 8px !important;
        }
        .apexcharts-xaxis-label,
        .apexcharts-yaxis-label,
        .apexcharts-xaxis-label tspan {
            fill: var(--bs-dark) !important;
        }

    </style>
@endpush


@push('script')
    <script>


        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.nav-link');
            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    $('.nav-link').removeClass('active');
                    $(this).addClass('active');
                    const type = this.getAttribute('data-type');
                    const route = `{{ route('merchant.get.load.chart', ['type' => ':type']) }}`.replace(':type', type);
                    loadChartData(route, type);
                });
            });
            const defaultRoute = `{{ route('merchant.get.load.chart', ['type' => 'Transaction']) }}`;
            loadChartData(defaultRoute, 'Transaction');
        });

        function loadChartData(route) {
            const chartContainer = '#incomeChart';

            fetchWithBlock(route, { meta: { blockId: 'incomeChartBlock' } })
                .then(data => {
                    initIncomeChart(chartContainer, data);
                    const totalAmount = data.amounts.reduce((a, b) => a + b, 0);
                    $('#totalAmount').text(currencyPosition(totalAmount));
                })
                .catch(error => {
                    console.error('Chart load failed:', error);
                });
        }

        const Lang = {!! json_encode([
            'transfer' => __('Transfer'),
            'amount' => __('Amount'),
            'count' => __('Count'),
        ]) !!};

    </script>
@endpush
