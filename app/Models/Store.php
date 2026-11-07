<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Store extends Model
{
	use HasFactory, SoftDeletes;

	protected $guarded = ['id'];

	public function scopeOwn($query)
	{
		return $query->where('user_id', Auth::id());
	}

	public function productsMap()
	{
		return $this->hasMany(ProductStoreMap::class, 'store_id');
	}

	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

    public function type(): string
    {
        $isDisabled = $this->delivery_note == 'disabled';

        $iconClass = $isDisabled ? 'bi-slash-circle' : 'bi-truck';
        $colorClass = $isDisabled ? 'icon-soft-danger text-danger' : 'icon-soft-success text-success';
        $label = $isDisabled ? trans('Disabled') : trans('Enabled');

        return <<<HTML
        <div class="d-flex align-items-center">
            <span class="icon {$colorClass} icon-sm icon-circle " title="{$label}">
                <i class="bi {$iconClass}"></i>
            </span>
        </div>
    HTML;
    }

}
