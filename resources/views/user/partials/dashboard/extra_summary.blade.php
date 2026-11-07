<div class="row">
    <div class="col-12 col-xxl-8 order-2 order-md-3 order-xxl-2 mb-6">
        <div class="card" data-block="chartOne">
            <div class="row row-bordered g-0">
                <div class="col-lg-8">
                    <div class="card-header border-bottom-0 d-flex align-items-center justify-content-between">
                        <div class="card-title mb-0">
                            <h4 class="m-0 me-2">@lang('Transfer & Request Summary')</h4>
                        </div>
                    </div>
                    <div id="totalRevenueChart" class="px-3"></div>
                </div>
                <div class="col-lg-4">
                    <div class="card-body px-xl-9 py-12 d-flex align-items-center flex-column">

                        <div id="growthChart" class="my-6"></div>

                        <div class="d-flex gap-5 justify-content-between">
                            <div class="d-flex gap-2">
                                <span class="icon icon-md icon-soft-primary icon-square">
                                    <i class="bi-send-check fs-2"></i>
                                </span>
                                <div class="d-flex flex-column">
                                    <small>@lang('Transfer')</small>
                                    <h6 class="mb-0" id="transferAmount"></h6>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <span class="icon icon-md icon-soft-info icon-square">
                                    <i class="bi-box-arrow-in-down fs-2"></i>
                                </span>
                                <div class="d-flex flex-column">
                                    <small>@lang('Request')</small>
                                    <h6 class="mb-0" id="requestAmount"></h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-8 col-lg-12 col-xxl-4 order-3 order-md-2">
        <div class="row ">
            <div class="col-md-6 col-sm-12 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="card-title d-flex align-items-start justify-content-between mb-5">
                            <div class="avatar avatar-circle flex-shrink-0">
                                <img src="{{ asset('assets/user/img/payment.png') }}" alt="..." class="avatar-img avatar-circle"/>
                            </div>
                        </div>
                        <h3 class="mb-1  mt-3">@lang('Payments')</h3>
                        <h4 class="card-title mb-1">{{ $payments ?? 0 }}</h4>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-sm-12 mb-4">
                <div class="card h-100" data-block="chartWeek">
                    <div class="card-body pb-0">
                        <h6 class="d-block fw-medium mb-3">@lang('Daily Transaction')</h6>
                        <div id="revenueChart"></div>
                    </div>
                </div>
            </div>
            <div class="col-12 mb-4">
                <div class="card" data-block="transferChart">
                    <div class="card-body">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                            <div>
                                <h6 class="mb-1">@lang('Transfer Report')</h6>
                                <span class="badge bg-warning mb-3">@lang('YEAR') {{ date('Y') }}</span>
                                <div class="mt-3 mb-2">
                                    <span class="text-success fw-semibold d-block mb-1">@lang('Amount')</span>
                                    <h4 class="mb-0">{{ currencyPosition($transfer_this_year) }}</h4>
                                </div>
                            </div>
                            <div id="transferReportChart" class=" w-100 w-md-auto float-end mb-0 mt-5"></div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>


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
                        <button type="button" class="nav-link btn-sm btn" id="deposit-tab" role="tab"
                                data-type="Deposit">@lang('Deposit')
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
                        <a class="dropdown-item" href="{{ route('user.transaction') }}">@lang('View all transactions')</a>
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
        document.addEventListener("DOMContentLoaded", function () {

            initTotalRevenueChart("#totalRevenueChart","{{ route('user.get.activity.chart') }}");
            initTransferReportChart('#transferReportChart',"{{ route('user.get.transfer.chart') }}");

            axios.get("{{ route('user.get.transfer.request.summary') }}",{meta: { blockId: 'chartOne' }})
                .then(function(response) {
                    const data = response.data.data;
                    const basic = window.basicControl;

                    document.getElementById('transferAmount').textContent = basic.currency_symbol + data.transfer.formatted;
                    document.getElementById('requestAmount').textContent = basic.currency_symbol + data.request.formatted;

                    initGrowthChart("#growthChart",data.percentage);
                })
                .catch(function(error) {
                    console.error(error);
                });

            axios.get("{{ route('user.get.transaction.count') }}",{meta: { blockId: 'chartWeek' }})
                .then(function(response) {
                    const dailyData = response.data.data;
                    initRevenueChart('#revenueChart', dailyData);
                })
                .catch(function(error) {
                    console.error(error);
                });

        });

        document.addEventListener('DOMContentLoaded', function () {
            const tabs = document.querySelectorAll('.nav-link');
            tabs.forEach(tab => {
                tab.addEventListener('click', function () {
                    $('.nav-link').removeClass('active');
                    $(this).addClass('active');
                    const type = this.getAttribute('data-type');
                    const route = `{{ route('user.get.load.chart', ['type' => ':type']) }}`.replace(':type', type);
                    loadChartData(route, type);
                });
            });
            const defaultRoute = `{{ route('user.get.load.chart', ['type' => 'Transaction']) }}`;
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
