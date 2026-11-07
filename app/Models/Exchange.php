<?php

namespace App\Models;

use App\Traits\RandomCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class Exchange extends Model
{
    use HasFactory, RandomCode, Prunable;

    public function fromWallet()
    {
        return $this->belongsTo(Wallet::class, 'from_wallet', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function toWallet()
    {
        return $this->belongsTo(Wallet::class, 'to_wallet', 'id');
    }

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function scopeGetProfit($query, $days = null)
    {
        $baseCurrencyRate = 'exchange_rate';

        if ($days) {
            $date = now()->subDays($days)->toDateString();

            return $query->selectRaw("
                SUM(
                    CASE
                        WHEN status = 1
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ) AS total_profit
            ")->selectRaw("
                SUM(
                    CASE
                        WHEN status = 1 AND updated_at >= ?
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ) AS profit_{$days}_days
            ", [$date])->selectRaw("
                (SUM(
                    CASE
                        WHEN status = 1 AND updated_at >= ?
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ) / NULLIF(SUM(
                    CASE
                        WHEN status = 1
                        THEN charge / {$baseCurrencyRate}
                        ELSE 0
                    END
                ), 0)) * 100 AS profit_percentage_{$days}_days
            ", [$date]);
        }

        return $query->selectRaw("
            SUM(
                CASE
                    WHEN status = 1
                    THEN charge / {$baseCurrencyRate}
                    ELSE 0
                END
            ) AS total_profit
        ");
    }


    public function scopeSearch($query, $search)
    {
        $created_date = isset($search['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']);

        return $query->when(isset($search['utr']), fn($q) => $q->where('utr', 'LIKE', "%{$search['utr']}%"))
            ->when(isset($search['min']), fn($q) => $q->where('amount', '>=', $search['min']))
            ->when(isset($search['max']), fn($q) => $q->where('amount', '<=', $search['max']))
            ->when($created_date, fn($q) => $q->whereDate("created_at", $search['created_at']))
            ->when(isset($search['from_wallet']), fn($q) => $q->where('from_wallet', $search['from_wallet']))
            ->when(isset($search['to_wallet']), fn($q) => $q->where('to_wallet', $search['to_wallet']))
            ->when(isset($search['status']), fn($q) => $q->where('status', $search['status']));
    }

    public function formatted(): array
    {
        return [
            'exchange' => optional($this->fromWallet->currency)->code ?? 'N/A',
            'fromCurrency' => optional($this->fromWallet)->currency->code,
            'toCurrency' => optional($this->toWallet)->currency->code,
            'amount' => getAmount($this->amount),
            'charge' => getAmount($this->charge),
            'exchangeRate' => getAmount($this->exchange_rate),
            'exchangeAmount' => getAmount($this->received_amount),
            'transactionId' => $this->utr,
            'status' => $this->status ? 'Completed' : 'Pending',
            'createdTime' => $this->created_at,
        ];
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }

}
