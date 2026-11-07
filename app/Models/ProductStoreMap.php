<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStoreMap extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function store()
	{
		return $this->belongsTo(Store::class, 'store_id');
	}

	public function product()
	{
		return $this->belongsTo(StoreProduct::class, 'product_id');
	}

}
