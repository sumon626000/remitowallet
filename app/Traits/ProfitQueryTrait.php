<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait ProfitQueryTrait
{
    public function addProfitQuery(Builder $query, $exchangeRate, $status, int $days = null): Builder
    {
        $rate = $exchangeRate;

        if ($days) {
            $date = now()->subDays($days)->toDateString();

            $query->selectRaw("
                SUM(CASE
                    WHEN status = ? THEN charge / {$rate}
                    ELSE 0
                END) AS total_profit
            ", [$status]);

            $query->selectRaw("
                SUM(CASE
                    WHEN status = ? AND updated_at >= ?
                    THEN charge / {$rate}
                    ELSE 0
                END) AS profit_{$days}_days
            ", [$status, $date]);

            $query->selectRaw("
                (SUM(CASE
                    WHEN status = ? AND updated_at >= ?
                    THEN charge / {$rate}
                    ELSE 0
                END) / NULLIF(SUM(CASE
                    WHEN status = ?
                    THEN charge / {$rate}
                    ELSE 0
                END), 0)) * 100 AS profit_percentage_{$days}_days
            ", [$status, $date, $status]);

            return $query;
        }

        return $query->selectRaw("
            SUM(CASE
                WHEN status = ?
                THEN charge / {$rate}
                ELSE 0
            END) AS total_profit
        ", [$status]);
    }

}
