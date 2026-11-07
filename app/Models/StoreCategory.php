<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreCategory extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function scopeOwn($query)
	{
		return $query->where('user_id', Auth::id());
	}

	public function products()
	{
		return $this->hasMany(StoreProduct::class, 'category_id');
	}

	public function activeProducts()
	{
		return $this->hasMany(StoreProduct::class, 'category_id')->where('status', 1);
	}
}
