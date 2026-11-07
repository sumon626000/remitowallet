<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualCardTransaction extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	protected $casts = [
		'data' => 'object'
	];

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function cardOrder()
	{
		return $this->belongsTo(VirtualCardOrder::class, 'card_order_id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_code', 'code');
	}
}
