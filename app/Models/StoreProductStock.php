<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreProductStock extends Model
{
	use HasFactory;

	protected $guarded = ['id'];
	protected $casts = [
		'product_attr_lists_id' => 'object',
	];

	public function scopeOwn($query)
	{
		return $query->where('user_id', Auth::id());
	}

	public function product()
	{
		return $this->belongsTo(StoreProduct::class, 'product_id');
	}
}
