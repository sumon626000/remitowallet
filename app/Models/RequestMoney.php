<?php

namespace App\Models;

use App\Traits\RandomCode;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Auth;

class RequestMoney extends Model
{
    use HasFactory, RandomCode, Prunable;

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id', 'id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function scopeVisibleToUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('sender_id', $userId)
                ->orWhere(function ($subQuery) use ($userId) {
                    $subQuery->where('receiver_id', $userId)
                        ->where('status', 1);
                });
        });
    }

    public function scopeGetProfit($query, $days = null)
    {
        $baseCurrencyRate = "(SELECT exchange_rate FROM currencies WHERE currencies.id = request_money.currency_id LIMIT 1)";

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
        return $query
            ->when(isset($search['email']), function ($query) use ($search) {
                return $query->where('email', 'LIKE', "%{$search['email']}%");
            })
            ->when(isset($search['utr']), function ($query) use ($search) {
                return $query->where('utr', 'LIKE', "%{$search['utr']}%");
            })
            ->when(isset($search['min']), function ($query) use ($search) {
                return $query->where('amount', '>=', $search['min']);
            })
            ->when(isset($search['max']), function ($query) use ($search) {
                return $query->where('amount', '<=', $search['max']);
            })
            ->when(isset($search['currency_id']), function ($query) use ($search) {
                return $query->where('currency_id', $search['currency_id']);
            })
            ->when(isset($search['sender']), function ($q) use ($search) {
                $q->whereHas('sender', function ($qry) use ($search) {
                    $qry->whereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search['sender']}%"])
                        ->orWhere('username', 'LIKE', "%{$search['sender']}%");
                });
            })
            ->when(isset($search['receiver']), function ($q) use ($search) {
                $q->whereHas('receiver', function ($qry) use ($search) {
                    $qry->whereRaw("CONCAT(firstname, ' ', lastname) LIKE ?", ["%{$search['receiver']}%"])
                        ->orWhere('username', 'LIKE', "%{$search['receiver']}%");
                });
            })
            ->when(isset($search['type']) && preg_match("/sent/", $search['type']), function ($query) {
                return $query->where('sender_id', Auth::id());
            })
            ->when(isset($search['type']) && preg_match("/received/", $search['type']), function ($query) {
                return $query->where('receiver_id', Auth::id());
            })
            ->when(isset($search['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $search['created_at']), function ($query) use ($search) {
                return $query->whereDate('created_at', $search['created_at']);
            });
    }

    public static function transformRequestMoney($query): array
    {
        return [
            'requestTo' => optional($query->receiver)->name ?? 'N/A',
            'senderEmail' => $query->email ?? 'N/A',
            'amount' => getAmount($query->amount),
            'currency' => optional($query->currency)->code,
            'status' => $query->status == 1 ? 'Success' : ($query->status == 2 ? 'Canceled' : 'Pending'),
            'transactionId' => $query->utr ?? 'N/A',
            'createdTime' => $query->created_at,
        ];
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }
}
