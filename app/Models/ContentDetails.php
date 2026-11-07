<?php

namespace App\Models;

use App\Traits\Translatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ContentDetails extends Model
{
    use HasFactory, Translatable;

    public $fillable = ['content_id', 'language_id', 'description'];

    public $casts = ['description' => "object"];

    public function content()
    {
        return $this->belongsTo(Content::class, 'content_id', 'id');
    }

    protected static function boot()
    {
        $activeTheme = getTheme();
        parent::boot();
        static::saved(function () use ($activeTheme) {
            Cache::forget("footer_content_{$activeTheme}");
            Cache::forget('extra_info_content');
        });
    }
}
