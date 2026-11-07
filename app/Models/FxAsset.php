<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FxAsset extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'active' => 'boolean',
        'decimals' => 'integer',
        'sort_order' => 'integer',
    ];
}
