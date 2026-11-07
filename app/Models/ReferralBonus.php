<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralBonus extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function getStatus(): string
    {
        return match ($this->status) {
            0 => '<span class="badge bg-soft-danger text-danger">
                        <span class="legend-indicator bg-danger"></span>' . trans('Deactivated') . '
                    </span>',
            1 => '<span class="badge bg-soft-success text-success">
                        <span class="legend-indicator bg-success"></span>' . trans('Active') . '
                    </span>',
            default => 'Unknown',
        };
    }

    public function getType(): string
    {
        return match ($this->calcType) {
            0 => '<span class="badge bg-soft-secondary text-secondary">
                        <span class="legend-indicator bg-secondary"></span>' . trans('Percent') . '
                    </span>',
            1 => '<span class="badge bg-soft-info text-info">
                        <span class="legend-indicator bg-info"></span>' . trans('Fixed') . '
                    </span>',
            default => 'Unknown',
        };
    }


}
