<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait RandomCode
{
    public function setUtrAttribute($value)
    {
        $this->attributes['utr'] = generateOrderedId($this->getTable(), 'utr', $value);

//        $randomString = $this->generateRandomAlphaNumeric(10);
//        $this->attributes['utr'] = $value . $randomString;
    }

    protected function generateRandomAlphaNumeric($length)
    {
        $randomAlpha = Str::upper(Str::random($length));
        $numericChars = '0123456789';
        $randomAlphaNumeric = Str::replaceArray('X', str_split(Str::random(2, $numericChars)), $randomAlpha);
        return $randomAlphaNumeric;
    }
}
