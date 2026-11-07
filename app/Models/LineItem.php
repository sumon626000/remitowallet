<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LineItem extends Model
{
	use HasFactory;

	protected $fillable = ['line_item_type', 'line_item_id', 'title', 'price', 'description', 'quantity', 'subtotal'];
}
