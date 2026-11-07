<?php

namespace App\Models;

use App\Traits\ProfitQueryTrait;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QRCode extends Model
{
	use HasFactory,ProfitQueryTrait;

	protected $guarded = ['id'];

	public function depositable()
	{
		return $this->morphOne(Deposit::class, 'depositable');
	}

	public function successDepositable()
	{
		return $this->morphOne(Deposit::class, 'depositable')->where('status', 1);
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function gateway()
	{
		return $this->belongsTo(Gateway::class, 'gateway_id');
	}

    public function scopeGetProfit($query, $days = null): Builder
    {
        $baseCurrencyRate = "(SELECT exchange_rate FROM currencies WHERE currencies.id = q_r_codes.currency_id LIMIT 1)";
        $status = 1;
        return $this->addProfitQuery($query, $baseCurrencyRate, $status, $days);
    }

}
