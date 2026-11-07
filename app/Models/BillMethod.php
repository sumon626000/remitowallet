<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillMethod extends Model
{
	use HasFactory;

	protected $guarded = ['id'];
	protected $casts = [
		'parameters' => 'object',
		'extra_parameters' => 'object',
		'inputForm' => 'object',
		'convert_rate' => 'object',
	];

	public function billServices()
	{
		return $this->hasMany(BillService::class, 'bill_method_id');
	}
}
