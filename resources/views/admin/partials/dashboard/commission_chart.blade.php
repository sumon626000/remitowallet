

<div class="card  mb-3 mb-lg-5">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h4 class="card-header-title">@lang("Commission Summary")</h4>
        <button id="js-daterangepicker-commission" class="btn btn-white btn-sm dropdown-toggle">
            <i class="bi-calendar-week"></i>
            <span class="js-daterangepicker-preview ms-1"></span>
        </button>
    </div>
    <div class="card-body ">
        <!-- Line Chart -->
        <div class="chartjs-custom" style="height: 20rem;">
            <canvas id="commissionChart"></canvas>
        </div>
        <!-- End Line Chart -->
    </div>
</div>



@push('script')
    <script>
        let commissionChart;

        function createCommissionChart(data) {
            const ctx = document.getElementById('commissionChart');

            commissionChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.dates,
                    datasets: [
                        {
                            label: "Total Commission",
                            data: data.totalCommission,
                            backgroundColor: "rgb(225,243,237)",
                            borderColor: "#65efca",
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
                                beginAtZero: true
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

        async function updateCommissionChart(startDate, endDate) {
            let url = "{{ route('admin.get.commission.chart') }}";

            try {
                const response = await axios.get(url, {
                    params: {
                        start: startDate.format('YYYY-MM-DD'),
                        end: endDate.format('YYYY-MM-DD')
                    }
                });
                let data = response.data;

                if (commissionChart) {
                    commissionChart.destroy();
                }
                createCommissionChart(data);
            } catch (error) {
                console.error('Error fetching commission chart data:', error);
            }
        }

        var start = moment().subtract(6, 'days');
        var end = moment();

        function cb(start, end) {
            $('#js-daterangepicker-commission .js-daterangepicker-preview').html(
                start.format('MMM D') + ' - ' + end.format('MMM D, YYYY')
            );
            updateCommissionChart(start, end);
        }

        $('#js-daterangepicker-commission').daterangepicker({
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
