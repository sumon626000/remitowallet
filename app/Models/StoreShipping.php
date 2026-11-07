<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreShipping extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function scopeOwn($query)
	{
		return $query->where('user_id', Auth::id());
	}

	public function store()
	{
		return $this->belongsTo(Store::class, 'store_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function orders()
	{
		return $this->hasMany(ProductOrder::class, 'shipping_id');
	}
}
