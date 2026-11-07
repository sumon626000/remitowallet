

<div class="row mb-3">
    <div class="col-md-12">
        <div class="card mb-4 shadow-sm">
            <div class="card-header py-3 d-flex flex-wrap flex-row align-items-center justify-content-between">
                <h5 class="card-title">@lang('Transaction Summary')</h5>
                <input type="button" class="btn btn-white btn-sm" name="daterange" value=""/>
            </div>
            <div class="card-body">
                <div>
                    <canvas id="line-chart" height="400"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>


@push('js-lib')
    <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/moment.min.js') }}"></script>
@endpush

<x-assets :tomselect="false" :flatpickr="false" :daterange="true" />


@push('script')
    <script>

        $(document).ready(function () {
            // Initialize date range picker
            $('input[name="daterange"]').daterangepicker({
                opens: 'left',
                startDate: moment().startOf('month'),
                endDate: moment().endOf('month'),
                locale: {
                    format: 'MMMM D, YYYY'
                }
            }, function (start, end, label) {
                getTransaction(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            });

            function updateChart(data) {
                const datasets = [];

                @if($basic->deposit)
                datasets.push({
                    data: data.dataDeposit,
                    label: "Deposit",
                    borderColor: "#1abc9c",
                    fill: false
                });
                @endif
                datasets.push({
                    data: data.dataFund,
                    label: "Add Fund",
                    borderColor: "#00cec9",
                    fill: false
                });
                @if($basic->transfer)
                datasets.push({
                    data: data.dataTransfer,
                    label: "Send Money",
                    borderColor: "#3498db",
                    fill: false
                });
                @endif
                @if($basic->request)
                datasets.push({
                    data: data.dataRequestMoney,
                    label: "Request Money",
                    borderColor: "#9b59b6",
                    fill: false
                });
                @endif
                @if($basic->voucher)
                datasets.push({
                    data: data.dataVoucher,
                    label: "Voucher",
                    borderColor: "#6b7b98",
                    fill: false
                });
                @endif
                @if($basic->invoice)
                datasets.push({
                    data: data.dataInvoice,
                    label: "Invoice",
                    borderColor: "#6777ef",
                    fill: false
                });
                @endif
                @if($basic->redeem)
                datasets.push({
                    data: data.dataRedeem,
                    label: "Redeem Code",
                    borderColor: "#f39c12",
                    fill: false
                });
                @endif
                @if($basic->escrow)
                datasets.push({
                    data: data.dataEscrow,
                    label: "Escrow",
                    borderColor: "#273c75",
                    fill: false
                });
                @endif
                @if($basic->payout)
                datasets.push({
                    data: data.dataPayout,
                    label: "Payout",
                    borderColor: "#bdc3c7",
                    fill: false
                });
                @endif
                @if($basic->exchange)
                datasets.push({
                    data: data.dataExchange,
                    label: "Exchange",
                    borderColor: "#e74c3c",
                    fill: false
                });
                @endif
                @if($basic->escrow)
                datasets.push({
                    data: data.dataDispute,
                    label: "Dispute",
                    borderColor: "#8e44ad",
                    fill: false
                });
                @endif
                datasets.push({
                    data: data.dataCommissionEntry,
                    label: "Commission",
                    borderColor: "#5444ad",
                    fill: false
                });

                // Check if chart instance exists
                if (window.transactionChart) {
                    // Update the existing chart data
                    window.transactionChart.data.datasets = datasets;
                    window.transactionChart.data.labels = data.labels;
                    window.transactionChart.update();
                } else {
                    // Create a new chart instance if it doesn't exist
                    window.transactionChart = new Chart(document.getElementById("line-chart"), {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    ticks: {
                                        beginAtZero: true,
                                        stepSize: 5
                                    },
                                    grid: {
                                        color: "#e7eaf3"
                                    }
                                },
                                x: {
                                    grid: {
                                        display: false
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    mode: "index",
                                    intersect: false
                                }
                            }
                        }
                    });
                }
            }

            function getTransaction(start, end) {
                $.ajax({
                    method: "GET",
                    url: "{{ route('admin.get.transaction.chart') }}",
                    dataType: "json",
                    data: {
                        'start': start,
                        'end': end,
                    }
                })
                    .done(function (response) {
                        updateChart(response);
                    });
            }

            getTransaction(moment().startOf('month').format('YYYY-MM-DD'), moment().endOf('month').format('YYYY-MM-DD'));
        });


    </script>
@endpush
