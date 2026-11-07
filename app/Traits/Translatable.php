<?php


namespace App\Traits;

use App\Models\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

trait Translatable
{
    public static function booted()
    {
        if (Auth::getDefaultDriver() != 'admin') {
            $lang = app()->getLocale();

            $languageId = Cache::remember("language_id_{$lang}", now()->addMinutes(60), function () use ($lang) {
                $langQuery = Language::query();
                $language = $langQuery->where('short_name', $lang)->first();
                if (!$language) {
                    $language = $langQuery->where('default_status', true)->first();
                }
                return $language?->id;
            });

            if ($languageId) {
                static::addGlobalScope('language', function (Builder $builder) use ($languageId) {
                    $builder->where('language_id', $languageId);
                });
            }
        }
    }
}
