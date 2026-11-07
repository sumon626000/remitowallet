<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Payout extends Model
{
    use HasFactory,Prunable;

    protected $fillable = ['user_id', 'currency_id', 'payout_method_id', 'payout_currency_code', 'amount', 'charge', 'net_amount',
        'amount_in_base_currency', 'charge_in_base_currency', 'net_amount_in_base_currency', 'response_id', 'last_error',
        'information', 'meta_field', 'feedback', 'trx_id', 'status', 'admin_id'];

    protected $table = 'payouts';

    protected $casts = [
        'information' => 'object',
        'meta_field' => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function method()
    {
        return $this->belongsTo(PayoutMethod::class, 'payout_method_id','id');
    }

    public function getStatusClass()
    {
        return [
            '1' => 'text-pending',
            '2' => 'text-success',
            '3' => 'text-danger',
        ][$this->status] ?? 'text-success';
    }

    public function payoutMethod()
    {
        return $this->belongsTo(PayoutMethod::class, 'payout_method_id', 'id');
    }

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function picture()
    {
        $image = $this->method?->logo;
        if ($image) {
            $url = getFile($this->method->driver, $this->method->logo);
            return '<div class="avatar avatar-sm avatar-circle">
                        <img class="avatar-img" src="' . $url . '" alt="Image Description">
                     </div>';

        } else {
            $firstLetter = substr($this->method?->name, 0, 1) ?? 'N/A';
            return '<div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                        <span class="avatar-initials">' . $firstLetter . '</span>
                     </div>';
        }
    }


    public static function boot(): void
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('payoutRecord');
        });

        static::creating(function (Payout $payout) {
            if (empty($payout->trx_id)) {
                $payout->trx_id = generateOrderedId('payouts','trx_id','P');
            }
        });
        static::saving(function (Payout $payout) {
            if (empty($payout->trx_id)) {
                $payout->trx_id = generateOrderedId('payouts','trx_id','P');
            }
        });

    }


    public static function generateOrderNumber()
    {
        return DB::transaction(function () {
            $lastOrder = self::lockForUpdate()->orderBy('id', 'desc')->first();
            if ($lastOrder && isset($lastOrder->trx_id)) {
                $lastOrderNumber = (int)filter_var($lastOrder->trx_id, FILTER_SANITIZE_NUMBER_INT);
                $newOrderNumber = $lastOrderNumber + 1;
            } else {
                $newOrderNumber = strRandomNum(12);
            }

            while (self::where('trx_id', 'P'.$newOrderNumber)->exists()) {
                $newOrderNumber = (int)$newOrderNumber + 1;
            }
            return 'P' . $newOrderNumber;
        });
    }


    public function getStatus(): string
    {
        if ($this->status == 0) {
            return '<span class="badge bg-soft-warning text-warning">
                        <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                    </span>';

        } elseif ($this->status == 1) {
            return '<span class="badge bg-soft-info text-info">
                        <span class="legend-indicator bg-info"></span>' . trans('Generated') . '
                    </span>';

        } elseif ($this->status == 2) {
            return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Payout Done') . '
                 </span>';
        } elseif ($this->status == 3) {
            return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Canceled') . '
                 </span>';
        } elseif ($this->status == 6) {
            return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Failed') . '
                 </span>';
        }else {
            return 'Unknown';
        }
    }

    public function scopeGetProfit($query, $days = null)
    {
        $baseCurrencyRate = "(SELECT exchange_rate FROM currencies WHERE currencies.id = payouts.currency_id LIMIT 1)";

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

    public function scopeSearchPayouts($query, $request)
    {
        if ($transaction = $request->input('transaction')) {
            $query->where('trx_id', 'LIKE', '%' . $transaction . '%');
        }
        if ($request->status != null) {
            $query->where('status', $request->status);
        }
        if ($request->created_at != null) {
            $date = Carbon::parse($request->created_at);
            $query->whereDate('created_at', $date);
        }
        return $query;
    }


    //for api
    public function scopeSearch(Builder $query, array $filters, $userId)
    {
        $validDate = isset($filters['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $filters['created_at']);

        return $query->with(['user', 'admin', 'currency'])
            ->when(isset($filters['email']), function ($query) use ($filters) {
                $query->whereHas('user', function ($q) use ($filters) {
                    $q->where('email', $filters['email']);
                });
            })
            ->when(isset($filters['trx_id']), function ($query) use ($filters) {
                $query->where('trx_id', 'LIKE', "%{$filters['trx_id']}%");
            })
            ->when(isset($filters['min']), function ($query) use ($filters) {
                $query->where('amount', '>=', $filters['min']);
            })
            ->when(isset($filters['max']), function ($query) use ($filters) {
                $query->where('amount', '<=', $filters['max']);
            })
            ->when(isset($filters['currency_id']), function ($query) use ($filters) {
                $query->where('currency_id', $filters['currency_id']);
            })
            ->when($validDate, function ($query) use ($filters) {
                $query->whereDate('created_at', $filters['created_at']);
            });
    }


    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }

}
