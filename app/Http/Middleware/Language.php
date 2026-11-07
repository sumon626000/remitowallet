<?php

namespace App\Http\Middleware;

use Closure;
use App\Models\Language as LanguageModel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Language
{
    public function handle($request, Closure $next)
    {
        try {
            DB::connection()->getPdo();
            $languages = $this->getActiveLanguages();
            $defaultLanguage = $this->getDefaultLanguage();
            $langCode = $this->getCode($defaultLanguage);
            $rtl = $this->getDirection($defaultLanguage);

            session()->put('lang', $langCode);
            session()->put('rtl', $rtl);
            app()->setLocale($langCode);
            return $next($request);
        } catch (\Exception $exception) {
            return $next($request);
        }
    }

    public function getCode($defaultLanguage = null)
    {
        return session('lang', $defaultLanguage ? $defaultLanguage->short_name : 'en');
    }

    public function getDirection($defaultLanguage = null)
    {
        return session('rtl', $defaultLanguage ? $defaultLanguage->rtl : 0);
    }

    public function getDefaultLanguage()
    {
        return Cache::remember('default_language', now()->addHour(), function () {
            return LanguageModel::where('status', 1)
                ->where(function ($query) {
                    $query->where('default_status', 1)
                        ->orWhere('default_status', 0);
                })
                ->orderBy('default_status', 'desc')
                ->first();
        });
    }

    public function getActiveLanguages()
    {
        return Cache::remember('active_languages', now()->addMinutes(60), function () {
            return LanguageModel::where('status', 1)
                ->orderBy('default_status', 'desc')
                ->get();
        });
    }
}
