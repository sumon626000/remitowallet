<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VirtualCardOrder extends Model
{
	use HasFactory;

	protected $guarded = ['id'];
	protected $casts = [
		'form_input' => 'object',
		'card_info' => 'object',
		'test' => 'object',
	];
	protected $appends = ['is_active'];

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

	public function getStatusMessageAttribute()
	{
		if ($this->status == 0) {
			return '<span class="badge bg-soft-warning text-warning">
                    <span class="legend-indicator bg-warning"></span>' . trans('Pending') . '
                  </span>';
		} elseif ($this->status == 2) {
			return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Rejected') . '
                  </span>';
		} elseif ($this->status == 1) {
			return '<span class="badge bg-soft-success text-success">
                    <span class="legend-indicator bg-success"></span>' . trans('Approve') . '
                  </span>';
		} elseif ($this->status == 3) {
			return '<span class="badge bg-soft-dark text-dark">
                    <span class="legend-indicator bg-dark"></span>' . trans('Resubmitted') . '
                  </span>';
		} elseif ($this->status == 5) {
			return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Block Requested') . '
                  </span>';
		} elseif ($this->status == 6) {
			return '<span class="badge bg-soft-dark text-dark">
                    <span class="legend-indicator bg-dark"></span>' . trans('Fund Rejected') . '
                  </span>';
		} elseif ($this->status == 7) {
			return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Block') . '
                  </span>';
		} elseif ($this->status == 8) {
			return '<span class="badge bg-soft-info text-info">
                    <span class="legend-indicator bg-info"></span>' . trans('Add Fund Requested') . '
                  </span>';
		} elseif ($this->status == 9) {
			return '<span class="badge bg-soft-danger text-danger">
                    <span class="legend-indicator bg-danger"></span>' . trans('Inactive') . '
                  </span>';
		}
	}

	public function scopeCards($query)
	{
		return $query->whereIn('status', [1, 5, 6, 7, 8, 9]);
	}

	public function getIsActiveAttribute()
	{
		if ($this->expiry_date) {
			if ($this->expiry_date > Carbon::now()) {
				if ($this->status == 1 || $this->status == 5 || $this->status == 6 || $this->status == 8) {
					return 'Active';
				}
			}
		}
		return 'Inactive';
	}

	public function cardMethod()
	{
		return $this->belongsTo(VirtualCardMethod::class, 'virtual_card_method_id');
	}

	public function transactional()
	{
		return $this->morphOne(Transaction::class, 'transactional');
	}

	public function chargeCurrency()
	{
		return $this->belongsTo(Currency::class, 'charge_currency', 'id');
	}
}
