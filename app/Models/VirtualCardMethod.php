<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualCardMethod extends Model
{
	use HasFactory;

	protected $guarded = ['id'];
	protected $casts = [
		'parameters' => 'object',
		'currencies' => 'object',
		'extra_parameters' => 'object',
		'add_fund_parameter' => 'object',
		'form_field' => 'object',
		'currency' => 'object',
		'symbol' => 'object',
	];
}
