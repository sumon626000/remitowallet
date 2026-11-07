<?php

namespace Modules\Merchant\Models;

use App\Models\Currency;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MerchantSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'withdraw_information' => 'object',
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'withdraw_currency','code');
    }

    public function merchant()
    {
        return $this->belongsTo(User::class, 'merchant_id');
    }

    public function getLastWithdrawAtAttribute(): ?string
    {
        return $this->last_auto_withdraw_at ? dateTime($this->last_auto_withdraw_at) : 'Not withdrawn yet';
    }


}
