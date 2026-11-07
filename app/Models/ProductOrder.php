<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function store()
	{
		return $this->belongsTo(Store::class, 'store_id');
	}

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function depositable()
	{
		return $this->morphOne(Deposit::class, 'depositable');
	}

	public function orderDetails()
	{
		return $this->hasMany(ProductOrderDetail::class, 'order_id');
	}

	public function gateway()
	{
		return $this->belongsTo(Gateway::class, 'gateway_id');
	}

	public function shipping()
	{
		return $this->belongsTo(StoreShipping::class, 'shipping_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}
}
