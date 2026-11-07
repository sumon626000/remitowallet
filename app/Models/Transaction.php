<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function transactional()
    {
        return $this->morphTo();
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }


    public static function boot(): void
    {
        parent::boot();
        static::saved(function () {
            Cache::forget('paymentRecord');
        });

        static::creating(function (Transaction $transaction) {
            if (empty($transaction->trx_id)) {
                $transaction->trx_id = self::generateOrderNumber();
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
            while (self::where('trx_id', 'T'.$newOrderNumber)->exists()) {
                $newOrderNumber = (int)$newOrderNumber + 1;
            }
            return 'T' . $newOrderNumber;
        });
    }

    public function getIconAttribute(): string
    {
        return match ($this->transactional_type) {
            'App\Models\Transfer' => 'bi-send',
            'App\Models\Deposit' => 'bi-box-arrow-in-down',
            'App\Models\Payout' => 'bi-box-arrow-up',
            'App\Models\Exchange' => 'bi-arrow-left-right',
            'App\Models\Invoice' => 'bi-receipt',
            default => 'bi-arrow-left-right',
        };
    }



    public function getTypeAttribute(): string
    {
        $className = class_basename($this->transactional_type);

        return trim(preg_replace('/(?<!^)([A-Z])/', ' $1', $className));
    }



}
