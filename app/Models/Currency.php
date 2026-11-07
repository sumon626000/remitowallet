<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = ['is_default'];

    public function chargesLimit()
    {
        return $this->hasMany(ChargesLimit::class, 'currency_id', 'id');
    }

    public function getImage(): string
    {
        return getFile($this->driver, $this->logo);
    }

    public function balance()
    {
        return Wallet::where('user_id', auth()->id())->where('currency_id', $this->id)->sum('balance');
    }

}
