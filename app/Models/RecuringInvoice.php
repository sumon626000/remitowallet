<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecuringInvoice extends Model
{
	use HasFactory;

	protected $guarded = ['id'];

	public function invoice()
	{
		return $this->hasOne(Invoice::class, 'recuring_invoice_id ', 'id');
	}
}
