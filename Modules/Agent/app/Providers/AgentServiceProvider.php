<?php

namespace Modules\Agent\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Agent\Http\Middleware\AgentAccessMiddleware;
use Modules\Agent\Http\Middleware\AgentMiddleware;
use Modules\Agent\Http\Middleware\RedirectToAgent;
use Modules\Agent\Services\AgentService;
use Nwidart\Modules\Traits\PathNamespace;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class AgentServiceProvider extends ServiceProvider
{
    use PathNamespace;

    protected string $name = 'Agent';

    protected string $nameLower = 'agent';

    /**
     * Boot the application events.
     */
    public function boot(): void
    {
        $this->registerCommands();
        $this->registerCommandSchedules();
        $this->registerTranslations();
        $this->registerConfig();
        $this->registerViews();
        $this->loadMigrationsFrom(module_path($this->name, 'database/migrations'));

        $this->app['router']->pushMiddlewareToGroup('userCheck', AgentAccessMiddleware::class);
        $this->app['router']->pushMiddlewareToGroup('userCheck', RedirectToAgent::class);
        $this->app['router']->aliasMiddleware('agent', AgentMiddleware::class);

        $this->loadExtraMenu();
    }

    public function loadExtraMenu(): void
    {
        View::composer('user.partials.header', function () {
            $menuHtml = view('agent::partials.user_extra_menu')->render();
            View::startPush('extra_menu');
            echo $menuHtml;
            View::stopPush();
        });

        View::composer('user.partials.sidebar', function () {
            $menuHtml = view('agent::partials.user_extra_menu_sidebar')->render();
            View::startPush('extra_menu');
            echo $menuHtml;
            View::stopPush();
        });

        View::composer('admin.layouts.sidebar', function ($view) {
            $agentCounts = Cache::remember('agent_counts', now()->addMinutes(10), function () {
                return AgentService::getSidebarCounts();
            });
            $menuHtml = view('agent::partials.admin_extra_menu_sidebar', compact('agentCounts'))->render();
            View::startPush('extra_menu');
            echo $menuHtml;
            View::stopPush();
        });
    }

    public function register(): void
    {
        $this->app->register(EventServiceProvider::class);
        $this->app->register(RouteServiceProvider::class);
    }

    /**
     * Register commands in the format of Command::class
     */
    protected function registerCommands(): void
    {
        // $this->commands([]);
    }


    protected function registerCommandSchedules(): void
    {
        // $this->app->booted(function () {
        //     $schedule = $this->app->make(Schedule::class);
        //     $schedule->command('inspire')->hourly();
        // });
    }

    /**
     * Register translations.
     */
    public function registerTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->nameLower);

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->nameLower);
            $this->loadJsonTranslationsFrom($langPath);
        } else {
            $this->loadTranslationsFrom(module_path($this->name, 'lang'), $this->nameLower);
            $this->loadJsonTranslationsFrom(module_path($this->name, 'lang'));
        }
    }

    /**
     * Register config.
     */
    protected function registerConfig(): void
    {
        $trxTypePath = module_path($this->name, 'config/agent_transaction_type.php');
        $gsPath = module_path($this->name, 'config/agent_general_settings.php');
        $plPath = module_path($this->name, 'config/agent_permission_list.php');

        $this->appendToConfig('transactionType', require $trxTypePath);
        $this->appendToConfig('generalsettings.settings', require $gsPath);
        $this->appendToConfig('permissionList', require $plPath);

        $configPath = module_path($this->name, config('modules.paths.generator.config.path'));

        if (is_dir($configPath)) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($configPath));

            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $config = str_replace($configPath.DIRECTORY_SEPARATOR, '', $file->getPathname());
                    $config_key = str_replace([DIRECTORY_SEPARATOR, '.php'], ['.', ''], $config);
                    $segments = explode('.', $this->nameLower.'.'.$config_key);

                    // Remove duplicated adjacent segments
                    $normalized = [];
                    foreach ($segments as $segment) {
                        if (end($normalized) !== $segment) {
                            $normalized[] = $segment;
                        }
                    }

                    $key = ($config === 'config.php') ? $this->nameLower : implode('.', $normalized);

                    $this->publishes([$file->getPathname() => config_path($config)], 'config');
                    $this->merge_config_from($file->getPathname(), $key);
                }
            }
        }
    }

    protected function appendToConfig(string $configKey, array $custom): void
    {
        $existing = config($configKey, []);
        foreach ($custom as $key => $value) {
            if (!array_key_exists($key, $existing)) {
                $existing[$key] = $value;
            }
        }
        config([$configKey => $existing]);
    }

    /**
     * Merge config from the given path recursively.
     */
    protected function merge_config_from(string $path, string $key): void
    {
        $existing = config($key, []);
        $module_config = require $path;

        config([$key => array_replace_recursive($existing, $module_config)]);
    }

    /**
     * Register views.
     */
    public function registerViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->nameLower);
        $sourcePath = module_path($this->name, 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->nameLower.'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->nameLower);

        Blade::componentNamespace(config('modules.namespace').'\\' . $this->name . '\\View\\Components', $this->nameLower);
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [];
    }

    private function getPublishableViewPaths(): array
    {
        $paths = [];
        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->nameLower)) {
                $paths[] = $path.'/modules/'.$this->nameLower;
            }
        }

        return $paths;
    }
}
