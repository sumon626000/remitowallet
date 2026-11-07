<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillService extends Model
{
	use HasFactory;

	protected $guarded = ['id'];
	protected $casts = [
		'info' => 'object',
		'label_name' => 'array',
		'extra_response' => 'array'
	];

	protected $appends = ['countryName'];

	public function method()
	{
		return $this->belongsTo(BillMethod::class, 'bill_method_id');
	}

	public function getCountryNameAttribute()
	{
		foreach (config('country') as $country) {
			if ($country['code'] == $this->country) {
				return $country['name'];
			}
		}
		return $this->country;
	}
}
