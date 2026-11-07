<div class="row">
    <div class="col-xl-12">
        <div class="card transaction-card mb-3 mb-lg-5" id="RecentTransactionGraph">
            <!-- Header -->
            <div class="card-header card-header-content-sm-between">
                <h4 class="card-header-title mb-2 mb-sm-0">
                    @lang("Transaction Graph") @if(isset($code)) @lang('for') {{ $code }}@endif
                </h4>

                <ul class="nav nav-segment nav-fill" id="projectsTab" role="tablist">
                    <li class="nav-item this-month" data-bs-toggle="chart" data-datasets="0"
                        data-trigger="click" data-action="toggle">
                        <a class="nav-link active" href="javascript:void(0);" data-bs-toggle="tab">@lang("This Month")</a>
                    </li>
                    <li class="nav-item" data-bs-toggle="chart" data-datasets="1" data-trigger="click" data-action="toggle">
                        <a class="nav-link" href="javascript:void(0);" data-bs-toggle="tab">@lang("Last Month")</a>
                    </li>
                </ul>
            </div>

            <div class="card-body">
                <div class="row align-items-sm-center mb-4">
                    <div class="col-sm mb-3 mb-sm-0">
                        <div class="d-flex align-items-center">
                            <span class="h5 mb-0"> @lang("Transaction:") <span class="transaction_amount"></span> </span>
                        </div>
                    </div>
                    <!-- End Col -->
                    <div class="col-sm-auto">
                        <div class="row fs-6">
                            <div class="col-auto">
                                <span class="legend-indicator bg-primary"></span> @lang("Transaction")
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bar Chart -->
                <div class="chartjs-custom chartjs-height">
                    <canvas id="updatingLineChart" data-hs-chartjs-options='{
                      "type": "line",
                      "data": {
                         "labels": @json($statistics['schedule']->keys()),
                         "datasets": [{
                          "backgroundColor": ["rgba(55,125,255, .5)", "rgba(255, 255, 255, .2)"],
                          "borderColor": "#377dff",
                          "borderWidth": 2,
                          "pointRadius": 0,
                          "hoverBorderColor": "#377dff",
                          "pointBackgroundColor": "#377dff",
                          "pointBorderColor": "#fff",
                          "pointHoverRadius": 0,
                          "tension": 0.4
                        },
                        {
                          "backgroundColor": ["rgba(0, 201, 219, .5)", "rgba(255, 255, 255, .2)"],
                          "borderColor": "#00c9db",
                          "borderWidth": 2,
                          "pointRadius": 0,
                          "hoverBorderColor": "#00c9db",
                          "pointBackgroundColor": "#00c9db",
                          "pointBorderColor": "#fff",
                          "pointHoverRadius": 0,
                          "tension": 0.4
                        }]
                      },
                      "options": {
                        "gradientPosition": {"y1": 200},
                         "scales": {
                            "y": {
                              "grid": {
                                "color": "#e7eaf3",
                                "drawBorder": false,
                                "zeroLineColor": "#e7eaf3"
                              },
                              "ticks": {
                                "min": 0,
                                "max": 100,
                                "stepSize": 100,
                                "fontColor": "#97a4af",
                                "fontFamily": "Open Sans, sans-serif",
                                "padding": 10,
                                "postfix": "k"
                              }
                            },
                            "x": {
                              "grid": {
                                "display": false,
                                "drawBorder": false
                              },
                              "ticks": {
                                "fontSize": 12,
                                "fontColor": "#97a4af",
                                "fontFamily": "Open Sans, sans-serif",
                                "padding": 5
                              }
                            }
                        },
                        "plugins": {
                          "tooltip": {
                            "prefix": "{{ $currency->symbol }}",
                            "hasIndicator": true,
                            "mode": "index",
                            "intersect": false,
                            "lineMode": true,
                            "lineWithLineColor": "rgba(19, 33, 68, 0.075)"
                          }
                        },
                        "hover": {
                          "mode": "nearest",
                          "intersect": true
                        }
                      }
                    }'>
                    </canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-12">
        <div class="row g-5  h-100">
            <div class="col-xl-3 col-sm-6 col-lg-6 mb-5">
                <a class="card user-card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="card-title-top">
                            <i class="bi-cash-stack"></i>
                            <h6 class="card-subtitle">@lang('Total Transaction')</h6>
                        </div>

                        <div class="row align-items-center gx-2 mb-1">
                            <h2 class="card-title text-inherit" id="totalTxn">0</h2>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 col-lg-6 mb-5">
                <a class="card user-card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="card-title-top">
                            <i class="bi-cash-stack"></i>
                            <h6 class="card-subtitle">@lang("Daily Transaction")</h6>
                        </div>

                        <div class="row align-items-center gx-2 mb-1">
                            <h2 class="card-title text-inherit" id="dailyTxn"></h2>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 col-lg-6 mb-5">
                <a class="card user-card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="card-title-top">
                            <i class="bi-cash-coin"></i>
                            <h6 class="card-subtitle">@lang("Total Profit")</h6>
                        </div>
                        <div class="row align-items-center gx-2 mb-1">
                            <h2 class="card-title text-inherit" id="totalProfit"></h2>
                        </div>
                    </div>
                </a>
            </div>

            <div class="col-xl-3 col-sm-6 col-lg-6 mb-5">
                <a class="card user-card card-hover-shadow h-100">
                    <div class="card-body">
                        <div class="card-title-top">
                            <i class="bi-cash-coin"></i>
                            <h6 class="card-subtitle">@lang("Daily Profit")</h6>
                        </div>
                        <div class="row align-items-center gx-2 mb-1">
                            <h2 class="card-title text-inherit" id="dailyProfit"></h2>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>

</div>


@push('js-lib')
    <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/apexcharts.min.js') }}"></script>
@endpush

@push('script')
    <script>
        let $monthlyTransaction = 0;
        let $totalTransaction = {};

        Notiflix.Block.standard('#RecentTransactionGraph');

        function monthlyTransaction(keyDataset = 0) {
            $.ajax({
                url: "{{ route('admin.wallet.monthly.transaction',$code) }}",
                data: {
                    keyDataset
                },
                dataType: 'json',
                type: "GET",
                success: function (data) {
                    const currency = {
                        symbol: data.currency_symbol,
                        code: data.currency_code
                    };

                    $totalTransaction[keyDataset] = data.dailyTransaction;
                    $monthlyTransaction = (data.totalTransaction)
                    $monthlyProfit = (data.totalProfit)
                    $(".transaction_amount").text($monthlyTransaction);

                    const todayDate = "Day " + new Date().getDate().toString().padStart(2, '0');

                    const todayTransaction = currencyPosition(data.dailyTransaction[todayDate],currency);
                    const todayProfit = currencyPosition(data.dailyProfit[todayDate],currency);
                    $("#dailyTxn").text(todayTransaction);
                    $("#dailyProfit").text(todayProfit);

                    $("#totalTxn").text($monthlyTransaction);
                    $("#totalProfit").text($monthlyProfit);
                    updateChart(keyDataset);
                    Notiflix.Block.remove('#RecentTransactionGraph');
                },
                async: false
            });
        }

        const updatingChartDatasets = [
            [$totalTransaction[0]],
            [$totalTransaction[1]]
        ];
        HSCore.components.HSChartJS.init(document.querySelector('#updatingLineChart'), {
            data: {
                datasets: [
                    {
                        data: updatingChartDatasets[0][0]
                    },
                    {
                        data: updatingChartDatasets[0][1]
                    }
                ]
            }
        });

        const updatingLineChart = HSCore.components.HSChartJS.getItem('updatingLineChart');

        function updateChart(keyDataset) {
            updatingChartDatasets[keyDataset] = [$totalTransaction[keyDataset]];

            updatingLineChart.data.datasets.forEach(function (dataset, key) {
                dataset.data = updatingChartDatasets[keyDataset][key];
            });
            updatingLineChart.update();
        }

        document.querySelectorAll('[data-bs-toggle="chart"]').forEach(item => {
            item.addEventListener('click', e => {
                let keyDataset = e.currentTarget.getAttribute('data-datasets');
                monthlyTransaction(keyDataset);
            });
        });

        monthlyTransaction(0);


    </script>
@endpush

@push('css')
    <style>
        .chartjs-height {
            height: 16rem !important;
        }
    </style>
@endpush
