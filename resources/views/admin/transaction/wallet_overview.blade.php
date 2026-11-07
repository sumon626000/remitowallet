<div class="col-lg-6">
    <div class="card h-100">
        <div class="card-header card-header-content-between ">
            <h4 class="card-header-title mt-3 mb-2">@lang('Reports overview')</h4>
        </div>
        <div class="card-body">
            <span class="h1 d-block mb-4">
                {{ currencyPosition($overview->total_transaction_volume,$currency->id) }}
            </span>
            <!-- Progress -->
            @php
                $gross_percentage = 100;
                $net_percentage = $overview->total_transaction_volume > 0
                    ? ($overview->net_transaction_volume / $overview->total_transaction_volume) * 100
                    : 0;
                $profit_percentage = $overview->total_transaction_volume > 0
                    ? ($overview->total_profit / $overview->total_transaction_volume) * 100
                    : 0;
            @endphp

            <div class="progress rounded-pill mb-2">
                <div class="progress-bar " role="progressbar" style="width: {{ $gross_percentage }}%"
                     aria-valuenow="{{ $gross_percentage }}" aria-valuemin="0" aria-valuemax="100"
                     data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="@lang('Total volume transaction')">
                </div>
                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $net_percentage }}%"
                     aria-valuenow="{{ $net_percentage }}" aria-valuemin="0" aria-valuemax="100"
                     data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="@lang('Net volume transaction')">
                </div>
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $profit_percentage }}%"
                     aria-valuenow="{{ $profit_percentage }}" aria-valuemin="0" aria-valuemax="100"
                     data-bs-toggle="tooltip" data-bs-placement="bottom" data-bs-original-title="@lang('Total profit')">
                </div>
            </div>
            <div class="d-flex justify-content-between mb-4">
                <span>0%</span>
                <span>100%</span>
            </div>
            <!-- End Progress -->
            <div class="table-responsive">
                <table class="table table-lg table-nowrap card-table mb-0">
                    <tbody>
                    <tr>
                        <th scope="row">
                            <span class="legend-indicator bg-primary"></span>@lang('Total transaction volume')
                        </th>
                        <td>{{ currencyPosition($overview->total_transaction_volume, $currency->id) }}</td>
                        <td>
                            <span class="badge bg-soft-primary text-primary">{{ round($gross_percentage, 1) }}%</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <span class="legend-indicator bg-info opacity-50"></span>@lang('Net transaction volume')
                        </th>
                        <td>{{ currencyPosition($overview->net_transaction_volume, $currency->id) }}</td>
                        <td>
                            <span class="badge bg-soft-info text-info">{{ round($net_percentage, 1) }}%</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <span class="legend-indicator bg-success"></span>@lang('Total profit')
                        </th>
                        <td>{{ currencyPosition($overview->total_profit, $currency->id) }}</td>
                        <td>
                            <span class="badge bg-soft-success text-success">{{ round($profit_percentage, 1) }}%</span>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <span class="legend-indicator bg-warning"></span>@lang('Total user involved')
                        </th>
                        <td>{{ number_format($overview->total_users) }}</td>
                        <td>
                            <span class="badge bg-soft-warning text-warning">
                                {{ round($overview->user_percentage, 1) }}%
                            </span>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
