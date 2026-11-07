<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChargesLimit extends Model
{
	use HasFactory;

	protected $fillable = ['currency_id', 'transaction_type_id', 'payment_method_id'];

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id');
	}

	public function gateway()
	{
		return $this->belongsTo(Gateway::class, 'payment_method_id');
	}
}
