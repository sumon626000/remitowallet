<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductAttrList extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function attrName()
	{
		return $this->belongsTo(StoreProductAttr::class, 'store_product_attrs_id');
	}
}
