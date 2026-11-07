<?php

namespace App\Http\Middleware;

use App\Models\ContentDetails;
use App\Models\Language;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ManageTheme
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (request()->has('theme')) {
            $allowedThemes = getThemeNames();
            $requestedTheme = request()->query('theme');
            if (in_array($requestedTheme, $allowedThemes)) {
                session()->put('active_theme', $requestedTheme);
            }
        }

        $theme = template();

        view()->composer([
            $theme . 'sections.footer',
            $theme . 'partials.header',
            $theme . 'page',
        ], function ($view) {
            $footer = $this->getExtraData('footer');
            $view->with('footer', $footer);
            $languages = \Cache::remember('active_languages', now()->addMinutes(60), function () {
                return Language::query()->orderBy('default_status', 'desc')->where('status', 1)->get();
            });
            $view->with('languages', $languages);
        });

        view()->composer([
            $theme . 'auth.login',
            $theme . 'auth.register',
            $theme . 'auth.passwords.email',
        ], function ($view) {
            $authPages = $this->getExtraData('auth_pages')['single']  ?? [];
            $view->with('template', $authPages);
        });


        return $next($request);
    }


    public function getExtraData(string $section): array
    {
        $activeTheme = getTheme();
        $lang = session('lang');
        $cacheKey = "extra_data_{$section}_{$activeTheme}_{$lang}";
        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($section) {
            $sectionData = ContentDetails::with(['content' => function ($query) use ($section) {
                $query->where('name', $section);
            }])->get();

            $singleContent = $sectionData->firstWhere('content.type', 'single') ?? [];
            $multipleContents = $sectionData->where('content.type', 'multiple')
                ->values()
                ->map(function ($multipleContentData) {
                    return collect($multipleContentData->description)
                        ->merge($multipleContentData->content->only('media'));
                });

            return [
                'single' => $singleContent
                    ? collect($singleContent->description ?? [])->merge($singleContent->content->only('media'))
                    : [],
                'multiple' => $multipleContents,
            ];
        });
    }
}
