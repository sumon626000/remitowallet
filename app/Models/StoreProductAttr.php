<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class StoreProductAttr extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function scopeOwn($query)
	{
		return $query->where('user_id', Auth::id());
	}

	public function attrLists()
	{
		return $this->hasMany(ProductAttrList::class, 'store_product_attrs_id');
	}
}
