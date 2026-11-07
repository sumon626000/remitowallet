<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FxTrade extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'stake' => 'decimal:2',
        'payout_pct' => 'decimal:2',
        'open_price' => 'decimal:8',
        'close_price' => 'decimal:8',
        'payout_amount' => 'decimal:2',
        'open_time' => 'datetime',
        'expire_time' => 'datetime',
        'settled_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
