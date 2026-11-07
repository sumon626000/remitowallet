<?php

namespace Modules\Agent\Models;

use App\Models\Currency;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AgentSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }

}
