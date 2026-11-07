

<div class="card  mb-3 mb-lg-5">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-header-title">@lang("Transaction summary")</h4>
        <button id="js-daterangepicker-trx" class="btn btn-white btn-sm dropdown-toggle">
            <i class="bi-calendar-week"></i>
            <span class="js-daterangepicker-preview ms-1"></span>
        </button>
    </div>
    <div class="card-body ">
        <!-- Line Chart -->
        <div class="chartjs-custom" style="height: 20rem;">
            <canvas id="trxChart"></canvas>
        </div>
        <!-- End Line Chart -->
    </div>
</div>



@push('script')
    <script>
        let trxChart;

        function createTrxChart(data) {
            const ctx = document.getElementById('trxChart').getContext('2d');

            trxChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Total Transaction",
                            data: data.totalTransaction,
                            backgroundColor: "rgba(55, 125, 255, .5)",
                            borderColor: "#377dff",
                            borderWidth: 2,
                            pointRadius: 3,
                            tension: 0.4,
                            fill: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        y: {
                            ticks: {
                                beginAtZero: true,
                                callback: function(value) { return baseCurrencySymbol + value.toFixed(2); }
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

        async function updateTrxChart(startDate, endDate) {
            let url = "{{ route('user.get.transaction.chart') }}";

            try {
                const response = await axios.get(url, {
                    params: {
                        start: startDate.format('YYYY-MM-DD'),
                        end: endDate.format('YYYY-MM-DD')
                    }
                });
                let data = response.data;

                if (trxChart) {
                    trxChart.destroy();
                }
                createTrxChart(data);
            } catch (error) {
                console.error('Error fetching trx chart data:', error);
            }
        }

        // Initialize date range picker
        var start = moment().subtract(6, 'days');
        var end = moment();

        function cb(start, end) {
            $('#js-daterangepicker-trx .js-daterangepicker-preview').html(
                start.format('MMM D') + ' - ' + end.format('MMM D, YYYY')
            );
            updateTrxChart(start, end);
        }


        $('#js-daterangepicker-trx').daterangepicker({
            startDate: start,
            endDate: end,
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Last 7 Days': [moment().subtract(6, 'days'), moment()],
                'Last 30 Days': [moment().subtract(29, 'days'), moment()],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'Last Month': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        }, cb);

        cb(start, end);


    </script>
@endpush
