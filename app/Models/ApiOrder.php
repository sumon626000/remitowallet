<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;

class ApiOrder extends Model
{
	use HasFactory, Prunable;

	protected $guarded = ['id'];

	protected $casts = [
		'meta' => 'object',
	];

	public function currency()
	{
		return $this->belongsTo(Currency::class, 'currency_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function depositable()
	{
		return $this->morphOne(Deposit::class, 'depositable');
	}

    public function prunable()
    {
        return static::where('status',0)->where('created_at', '<', now()->subMinutes(30));
    }

}
