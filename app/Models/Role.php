<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
	use HasFactory;

	protected $guarded = ['id'];
	protected $casts = [
		'permission' => 'array'
	];

	public function roleUsers()
	{
		return $this->hasMany(Admin::class, 'role_id');
	}

    public function getStatus(): string
    {
        return match ($this->status) {
            1 => '<span class="badge bg-soft-success text-success">
                        <span class="legend-indicator bg-success"></span>' . trans('Active') . '
                    </span>',
            0 => '<span class="badge bg-soft-danger text-danger">
                        <span class="legend-indicator bg-danger"></span>' . trans('Inactive') . '
                    </span>',
            default => 'Unknown',
        };
    }


}
