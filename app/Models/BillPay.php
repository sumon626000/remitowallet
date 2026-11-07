<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillPay extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	protected $casts = [
		'customer' => 'object'
	];

	public function service()
	{
		return $this->belongsTo(BillService::class, 'service_id');
	}

	public function method()
	{
		return $this->belongsTo(BillMethod::class, 'method_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function depositable()
	{
		return $this->morphOne(Deposit::class, 'depositable');
	}

	public function baseCurrency()
	{
		return $this->belongsTo(Currency::class, 'base_currency_id', 'id');
	}

	public function walletCurrency()
	{
		return $this->belongsTo(Currency::class, 'from_wallet', 'id');
	}


    public function getStatus($type = null)
    {
        if ($this->status == 0) {
            return !$type ?
                '<span class="badge bg-soft-primary text-primary">
                        <span class="legend-indicator bg-primary"></span>' . trans('Generated') . '
                    </span>'
                : 'Generated' ;

        } elseif ($this->status == 1) {
            return !$type ?
                '<span class="badge bg-soft-warning text-warning">
                        <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                    </span>'
                : 'Pending';

        } elseif ($this->status == 2) {
            return !$type ?
                '<span class="badge bg-soft-info text-info">
                    <span class="legend-indicator bg-info"></span>' . trans('Payment Done') . '
                 </span>'
                : 'Payment Done';
        }
        elseif ($this->status == 3) {
            return !$type ?
                '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Completed') . '
                 </span>'
                : 'Completed';
        }
        elseif ($this->status == 4) {
            return !$type ?
                '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Return') . '
                 </span>'
                : 'Return';
        }
        else{
            return 'Unknown';
        }
    }

    public function scopeGetProfit($query, $days = null): Builder
    {
        $baseCurrencyRate = "exchange_rate";
        $status = [2, 3];

        $statusList = implode(',', $status);
        $date = $days ? now()->subDays($days)->toDateString() : null;

        $query->selectRaw("
            SUM(
                CASE
                    WHEN status IN ({$statusList})
                    THEN charge / {$baseCurrencyRate}
                    ELSE 0
                END
            ) AS total_profit
        ");

        if ($days) {
            $query->selectRaw("
                SUM(
                    CASE
                        WHEN status IN ({$statusList}) AND updated_at >= ?
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ) AS profit_{$days}_days
            ", [$date]);

            $query->selectRaw("
                (
                    SUM(
                        CASE
                            WHEN status IN ({$statusList}) AND updated_at >= ?
                            THEN charge / {$baseCurrencyRate}
                            ELSE 0
                        END
                    )
                    / NULLIF(
                        SUM(
                            CASE
                                WHEN status IN ({$statusList})
                                THEN charge / {$baseCurrencyRate}
                                ELSE 0
                            END
                        ), 0
                    )
                ) * 100 AS profit_percentage_{$days}_days
            ", [$date]);
        }

        return $query;
    }

}
