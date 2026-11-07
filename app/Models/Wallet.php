<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
	use HasFactory;

	protected $fillable = ['user_id', 'currency_id','balance'];

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id', 'id');
	}

    public function scopeCustomOrder($query)
    {
        return $query->orderByRaw("
            FIELD(currency_id,
                (SELECT id FROM currencies WHERE code = 'GBP'),
                (SELECT id FROM currencies WHERE code = 'EUR'),
                (SELECT id FROM currencies WHERE code = 'USD')
            ) DESC
        ")->orderBy('balance', 'desc');
    }

}
