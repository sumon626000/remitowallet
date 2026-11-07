<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreProduct extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function scopeOwn($query)
	{
		return $query->where('user_id', Auth::id());
	}

	public function category()
	{
		return $this->belongsTo(StoreCategory::class, 'category_id');
	}

	public function productImages()
	{
		return $this->hasMany(StoreProductImage::class, 'product_id');
	}

	public function productStores()
	{
		return $this->hasMany(ProductStoreMap::class, 'product_id');
	}

	public function productAttrs()
	{
		return $this->hasMany(ProductAttrMap::class, 'product_id');
	}
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function storeProductStocks()
	{
		return $this->hasMany(StoreProductStock::class, 'product_id');
	}

	public function orderDetails()
	{
		return $this->hasMany(ProductOrderDetail::class,'product_id');
	}
}
