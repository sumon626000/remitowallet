<?php

namespace App\Models;


use App\Traits\ProfitQueryTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Deposit extends Model
{
    use HasFactory, Prunable, ProfitQueryTrait;

    protected $guarded = ['id'];

    protected $casts = [
        'information' => 'object'
    ];

    public function transactional()
    {
        return $this->morphOne(Transaction::class, 'transactional');
    }

    public function depositable()
    {
        return $this->morphTo();
    }

    public static function boot(): void
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('paymentRecord');
        });

        static::creating(function (Deposit $deposit) {
            if (empty($deposit->trx_id)) {
                $deposit->trx_id = self::generateOrderNumber();
            }
        });

        static::saving(function (Deposit $deposit) {
            if (empty($deposit->trx_id)) {
                $deposit->trx_id = self::generateOrderNumber();
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

            // Check again to ensure the new trx_id doesn't already exist (extra safety)
            while (self::where('trx_id', 'D' . $newOrderNumber)->exists()) {
                $newOrderNumber = (int)$newOrderNumber + 1;
            }
            return 'D' . $newOrderNumber;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function gateway()
    {
        return $this->belongsTo(Gateway::class, 'payment_method_id', 'id');
    }

    public function picture()
    {
        $image = optional($this->gateway)->image;
        if (!$image) {
            $firstLetter = substr(optional($this->gateway)->name, 0, 1);
            return '<div class="avatar avatar-sm avatar-soft-primary avatar-circle">
                        <span class="avatar-initials">' . $firstLetter . '</span>
                     </div>';

        } else {
            $url = getFile(optional($this->gateway)->driver, optional($this->gateway)->image);
            return '<div class="avatar avatar-sm avatar-circle">
                        <img class="avatar-img" src="' . $url . '" alt="Image Description">
                     </div>';

        }
    }

    public function getStatusClass()
    {
        return [
            '0' => 'text-dark',
            '1' => 'text-success',
            '2' => 'text-dark',
            '3' => 'text-danger',
        ][$this->status] ?? 'text-danger';
    }


    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id', 'id');
    }

    public function sender()
    {
        return $this->belongsTo(Admin::class, 'admin_id', 'id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function getStatus(): string
    {
        if ($this->status == 0) {
            return '<span class="badge bg-soft-warning text-warning">
                        <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                    </span>';

        } elseif ($this->status == 1) {
            return '<span class="badge bg-soft-success text-success">
                        <span class="legend-indicator bg-success"></span>' . trans('Success') . '
                    </span>';

        } elseif ($this->status == 2) {
            return '<span class="badge bg-soft-info text-info">
                    <span class="legend-indicator bg-info"></span>' . trans('Generated') . '
                 </span>';
        } elseif ($this->status == 3) {
            return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Rejected') . '
                 </span>';
        } else {
            return 'Unknown';
        }
    }

    public function scopeSearch($query, $filters)
    {
        return $query->with(['sender', 'receiver', 'currency'])
            ->when(isset($filters['email']), function ($q) use ($filters) {
                return $q->where('email', 'LIKE', "%{$filters['email']}%");
            })
            ->when(isset($filters['trx_id']), function ($q) use ($filters) {
                return $q->where('trx_id', 'LIKE', "%{$filters['trx_id']}%");
            })
            ->when(isset($filters['min']), function ($q) use ($filters) {
                return $q->where('amount', '>=', $filters['min']);
            })
            ->when(isset($filters['max']), function ($q) use ($filters) {
                return $q->where('amount', '<=', $filters['max']);
            })
            ->when(isset($filters['currency_id']) && $filters['currency_id'] !== 'all', function ($q) use ($filters) {
                return $q->where('currency_id', $filters['currency_id']);
            })
            ->when(isset($filters['created_at']) && preg_match("/^[0-9]{2,4}-[0-9]{1,2}-[0-9]{1,2}$/", $filters['created_at']), function ($q) use ($filters) {
                return $q->whereDate("created_at", $filters['created_at']);
            });
    }

    public function scopeGetProfit($query, $days = null): Builder
    {
        $baseCurrencyRate = "(SELECT exchange_rate FROM currencies WHERE currencies.id = deposits.currency_id LIMIT 1)";
        $status = 1;
        return $this->addProfitQuery($query, $baseCurrencyRate, $status, $days);
    }


    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subDays(2))->where('status', 0);
    }

    public function receive_wallet()
    {
        return $this->belongsTo(Currency::class, 'receive_currency_code', 'code');
    }
}
