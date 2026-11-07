<?php

namespace App\Providers;

use App\Models\ContentDetails;
use App\Models\Language;
use App\Models\ManageMenu;
use App\Services\SidebarDataService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Bridge\Mailchimp\Transport\MandrillTransportFactory;
use Symfony\Component\Mailer\Bridge\Sendgrid\Transport\SendgridTransportFactory;
use Symfony\Component\Mailer\Bridge\Sendinblue\Transport\SendinblueTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

        try {
            DB::connection()->getPdo();

            $data['basicControl'] = basicControl();
            $data['basic'] = basicControl();
            $data['theme'] = template();
            $data['themeTrue'] = template(true);
            View::share($data);


            view()->composer([
                'admin.layouts.sidebar',
            ], function ($view) {
                $sidebarCounts = Cache::remember('sidebar_counts', now()->addMinutes(10), function () {
                    return SidebarDataService::getSidebarCounts();
                });
                $mergedContents = $this->getMergedContents();

                $view->with([
                    'sidebarCounts' => $sidebarCounts,
                    'manageContents' => $mergedContents,
                ]);
            });


            if (basicControl()->is_force_ssl == 1) {
                if ($this->app->environment('production') || $this->app->environment('local')) {
                    \URL::forceScheme('https');
                }
            }

            Mail::extend('sendinblue', function () {
                return (new SendinblueTransportFactory)->create(
                    new Dsn(
                        'sendinblue+api',
                        'default',
                        config('services.sendinblue.key')
                    )
                );
            });

            Mail::extend('sendgrid', function () {
                return (new SendgridTransportFactory)->create(
                    new Dsn(
                        'sendgrid+api',
                        'default',
                        config('services.sendgrid.key')
                    )
                );
            });

            Mail::extend('mandrill', function () {
                return (new MandrillTransportFactory)->create(
                    new Dsn(
                        'mandrill+api',
                        'default',
                        config('services.mandrill.key')
                    )
                );
            });

        } catch (\Exception $e) {
        }

    }

    public function getMergedContents(): array
    {
        $theme = basicControl()->theme;
        $allContents = config('contents.all', []);
        $themeContents = config("contents.{$theme}", []);

        $merged = array_replace_recursive($themeContents,$allContents);
        unset($merged['message'], $merged['content_media']);

        return $merged;
    }




}
