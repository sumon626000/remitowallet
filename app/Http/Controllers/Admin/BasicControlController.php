<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BasicControl;
use App\Models\Currency;
use App\Models\FxAsset;
use App\Models\FxTrade;
use App\Models\Gateway;
use App\Services\AllStocksService;
use App\Services\ForexPriceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Facades\App\Services\BasicService;
use Facades\App\Services\CurrencyLayerService;
use Exception;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;

class BasicControlController extends Controller
{
    public function index($settings = null)
    {
        $settings = $settings ?? 'settings';
        abort_if(!in_array($settings, array_keys(config('generalsettings'))), 404);
        $settingsDetails = config("generalsettings.{$settings}");
        return view('admin.control_panel.settings', compact('settings', 'settingsDetails'));
    }

    public function basicControl()
    {
        $data['basicControl'] = basicControl();
        $data['timeZones'] = timezone_identifiers_list();
        $data['dateFormat'] = config('dateformat');
        $data['currencies'] = Currency::select('id', 'code', 'name')->where('is_active', 1)->get();
        return view('admin.control_panel.basic_control', $data);
    }

    public function basicControlUpdate(Request $request)
    {
        $request->validate([
            'site_title' => 'required|string|min:1|max:100',
            'time_zone' => 'required|string',
            'base_currency' => 'required',
            'joining_bonus' => 'required',
            'mail_recurring' => 'required',
            'fraction_number' => 'required|integer|not_in:0',
            'paginate' => 'required|integer|not_in:0',
            'date_format' => 'required|string',
            'admin_prefix' => 'required|string|min:3|max:100',
        ]);

        try {

            $basic = BasicControl();
            $base_currency = Currency::findOrFail($request->base_currency);
            $response = BasicControl::updateOrCreate([
                'id' => $basic->id ?? ''
            ], [
                'site_title' => $request->site_title,
                'time_zone' => $request->time_zone,
                'base_currency' => $base_currency->code,
                'currency_symbol' => $base_currency->symbol,
                'joining_bonus' => $request->joining_bonus,
                'mail_recurring' => $request->mail_recurring,
                'fraction_number' => $request->fraction_number,
                'date_time_format' => $request->date_format,
                'paginate' => $request->paginate,
                'admin_prefix' => $request->admin_prefix,
                'primary_color' => $request->primary_color,
                'secondary_color' => $request->secondary_color,
                'primary_color_green' => $request->primary_color_green,
                'secondary_color_green' => $request->secondary_color_green,
            ]);

            if (!$response)
                throw new Exception('Something went wrong, when updating data');

            $env = [
                'APP_TIMEZONE' => $response->time_zone,
                'APP_DEBUG' => $response->error_log == 0 ? 'true' : 'false'
            ];

            BasicService::setEnv($env);
            session()->flash('success', 'Basic control has been successfully configured');
            Artisan::call('optimize:clear');
            return back();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function basicControlActivityUpdate(Request $request)
    {
        $request->validate([
            'strong_password' => 'nullable|numeric|in:0,1',
            'registration' => 'nullable|numeric|in:0,1',
            'error_log' => 'nullable|numeric|in:0,1',
            'is_active_cron_notification' => 'nullable|numeric|in:0,1',
            'has_space_between_currency_and_amount' => 'nullable|numeric|in:0,1',
            'is_force_ssl' => 'nullable|numeric|in:0,1',
            'signup_bonus_status' => 'nullable|numeric|in:0,1',
            'deposit_commission' => 'nullable|numeric|in:0,1',
            'is_currency_position' => 'nullable|string|in:left,right',
            'cookie_status' => 'nullable|in:0,1',
            'automatic_payout_permission' => 'nullable|in:0,1'
        ]);

        try {
            $basic = BasicControl();
            $response = BasicControl::updateOrCreate([
                'id' => $basic->id ?? ''
            ], [
                'error_log' => $request->error_log,
                'strong_password' => $request->strong_password,
                'registration' => $request->registration,
                'is_active_cron_notification' => $request->is_active_cron_notification,
                'has_space_between_currency_and_amount' => $request->has_space_between_currency_and_amount,
                'is_currency_position' => $request->is_currency_position,
                'is_force_ssl' => $request->is_force_ssl,
                'signup_bonus_status' => $request->signup_bonus_status,
                'deposit_commission' => $request->deposit_commission,
                'cookie_status' => $request->cookie_status,
                'automatic_payout_permission' => $request->automatic_payout_permission,
            ]);

            if (!$response)
                throw new Exception('Something went wrong, when updating the data.');

            session()->flash('success', 'Basic control has been successfully configured.');
            Artisan::call('optimize:clear');
            return back();
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function currencyExchangeApiConfig()
    {
        $data['scheduleList'] = config('schedulelist.schedule_list');
        $data['basicControl'] = basicControl();
        return view('admin.control_panel.exchange_api_setting', $data);
    }

    public function currencyExchangeApiConfigUpdate(Request $request)
    {
        $request->validate([
            'currency_layer_access_key' => 'required|string',
            'coin_market_cap_app_key' => 'required|string',
        ]);

        try {
            $basicControl = basicControl();
            $basicControl->update([
                'currency_layer_access_key' => $request->currency_layer_access_key,
                'currency_layer_auto_update' => $request->currency_layer_auto_update,
                'currency_layer_auto_update_at' => $request->currency_layer_auto_update_at,
                'coin_market_cap_app_key' => $request->coin_market_cap_app_key,
                'coin_market_cap_auto_update' => $request->coin_market_cap_auto_update,
                'coin_market_cap_auto_update_at' => $request->coin_market_cap_auto_update_at
            ]);
            return back()->with('success', 'Configuration changes successfully');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function serviceControl(Request $request)
    {
        $serviceControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.serviceControl', compact('serviceControl'));
        }
        elseif ($request->isMethod('post')) {
            $purifiedData = $request->except('_token');
            $rules = collect($purifiedData)->mapWithKeys(function ($value, $key) {
                return [$key => 'nullable|integer|in:0,1'];
            })->toArray();

            $validator = Validator::make($purifiedData, $rules);

            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }

            $fields = array_keys($purifiedData);
            $purifiedData = (object)$purifiedData ?? null;
            foreach ($fields as $field) {
                if (property_exists($purifiedData, $field) && $purifiedData->$field !== null) {
                    $serviceControl->$field = $purifiedData->$field;
                }
            }
            $serviceControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }

    public function voucherSettings(Request $request)
    {
        $basicControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.voucherSettings', compact('basicControl'));
        } elseif ($request->isMethod('post')) {
            $purifiedData = $request->all();

            $validator = Validator::make($purifiedData, [
                'allowUser' => 'nullable|integer|min:0|in:0,1',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $basicControl->allowUser = $purifiedData->allowUser;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }

    public function invoiceSettings(Request $request)
    {
        $basicControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.invoiceSettings', compact('basicControl'));
        } elseif ($request->isMethod('post')) {

            $purifiedData = $request->all();

            $validator = Validator::make($purifiedData, [
                'invoice_charge' => 'nullable|integer|min:0|in:0,1',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $basicControl->invoice_charge = $purifiedData->invoice_charge;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }

    public function virtualCardSettings(Request $request)
    {
        $basicControl = basicControl();

        if ($request->isMethod('get')) {
            return view('admin.control_panel.virtualCardSettings', compact('basicControl'));
        } elseif ($request->isMethod('post')) {

            $purifiedData = $request->all();

            $validator = Validator::make($purifiedData, [
                'v_card_multiple' => 'nullable|integer|min:0|in:0,1',
                'v_card_charge' => 'nullable|min:0',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $basicControl->v_card_multiple = $purifiedData->v_card_multiple;
            $basicControl->v_card_charge = $purifiedData->v_card_charge;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }

    public function fxTradeSettings(Request $request)
    {
        $basicControl = basicControl();
        $settings = fxTradeSettings();

        $stockAssets = FxAsset::orderBy('sort_order')->orderBy('symbol')->get();
        $stockQuery = trim((string) $request->query('user', ''));
        $stockTrades = FxTrade::with('user')->latest('id')
            ->when($stockQuery !== '', function ($query) use ($stockQuery) {
                $query->whereHas('user', function ($user) use ($stockQuery) {
                    $user->where('username', 'like', '%'.$stockQuery.'%')
                        ->orWhere('email', 'like', '%'.$stockQuery.'%');
                });
            })
            ->limit(40)
            ->get();
        $closed = FxTrade::whereIn('status', ['settled', 'void']);
        $stockReport = [
            'trades' => (clone $closed)->count(),
            'wins' => (clone $closed)->where('result', 'win')->count(),
            'losses' => (clone $closed)->where('result', 'loss')->count(),
            'staked' => (float) (clone $closed)->sum('stake'),
            'paid' => (float) (clone $closed)->sum('payout_amount'),
        ];
        $stockReport['house'] = round($stockReport['staked'] - $stockReport['paid'], 2);
        $feedPrice = null;
        $feedSymbol = optional($stockAssets->firstWhere('active', 1))->symbol;
        if ($feedSymbol) {
            try {
                $feedPrice = app(ForexPriceService::class)->quote($stockAssets->firstWhere('active', 1))['price'] ?? null;
            } catch (\Throwable $e) {
                $feedPrice = null;
            }
        }

        if ($request->isMethod('get')) {
            return view('admin.control_panel.fxTradeSettings', compact(
                'basicControl',
                'settings',
                'stockAssets',
                'stockTrades',
                'stockReport',
                'stockQuery',
                'feedPrice',
                'feedSymbol'
            ));
        }

        $validator = Validator::make($request->all(), [
            'fx_enabled' => 'nullable|in:0,1',
            'fx_win_percent' => 'required|numeric|min:0|max:100',
            'fx_payout_mult' => 'required|numeric|min:1|max:10',
            'fx_min_bet' => 'required|numeric|min:0.01',
            'fx_max_bet' => 'required|numeric|gte:fx_min_bet',
            'royal_enabled' => 'nullable|in:0,1',
            'royal_win_percent' => 'required|numeric|min:0|max:100',
            'royal_min_bet' => 'required|numeric|min:0.01',
            'royal_max_bet' => 'required|numeric|gte:royal_min_bet',
            'bot_enabled' => 'nullable|in:0,1',
            'bot_win_percent' => 'required|numeric|min:0|max:100',
            'bot_payout_mult' => 'required|numeric|min:1|max:10',
            'bot_min_bet' => 'required|numeric|min:0.01',
            'bot_max_bet' => 'required|numeric|gte:bot_min_bet',
            'stocks_enabled' => 'nullable|in:0,1',
            'stocks_payout_pct' => 'nullable|numeric|min:1|max:500',
            'stocks_min_bet' => 'nullable|numeric|min:0.01',
            'stocks_max_bet' => 'nullable|numeric|min:0.01',
            'stocks_max_open' => 'nullable|integer|min:1|max:50',
            'stocks_bot_win_percent' => 'nullable|numeric|min:0|max:100',
            'stocks_durations' => 'nullable|array',
            'stocks_duration_remove' => 'nullable|array',
            'new_duration_value' => 'nullable|integer|min:1|max:86400',
            'new_duration_unit' => 'nullable|in:seconds,minutes,hours',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        if ((float) $request->input('stocks_max_bet', 0) > 0
            && (float) $request->input('stocks_max_bet') < (float) $request->input('stocks_min_bet')) {
            return back()->withErrors(['stocks_max_bet' => 'Maximum bet must be at least the minimum bet.'])->withInput();
        }
        $stockDurations = $this->stockDurationsFromRequest($request);
        if ($stockDurations === false) {
            return back()->withErrors(['new_duration_value' => 'Duration must be between 5 seconds and 24 hours.'])->withInput();
        }
        if ($stockDurations === []) {
            return back()->withErrors(['new_duration_value' => 'Keep at least one duration, or add a new one before removing the last.'])->withInput();
        }

        fxTradeSettings([
            'fx_enabled' => (int) $request->input('fx_enabled', 0),
            'fx_win_percent' => (float) $request->fx_win_percent,
            'fx_payout_mult' => (float) $request->fx_payout_mult,
            'fx_min_bet' => (float) $request->fx_min_bet,
            'fx_max_bet' => (float) $request->fx_max_bet,
            'royal_enabled' => (int) $request->input('royal_enabled', 0),
            'royal_win_percent' => (float) $request->royal_win_percent,
            'royal_min_bet' => (float) $request->royal_min_bet,
            'royal_max_bet' => (float) $request->royal_max_bet,
            'bot_enabled' => (int) $request->input('bot_enabled', 0),
            'bot_win_percent' => (float) $request->bot_win_percent,
            'bot_payout_mult' => (float) $request->bot_payout_mult,
            'bot_min_bet' => (float) $request->bot_min_bet,
            'bot_max_bet' => (float) $request->bot_max_bet,
            'stocks_enabled' => (int) $request->input('stocks_enabled', 0),
            'stocks_payout_pct' => (float) $request->input('stocks_payout_pct', $settings->stocks_payout_pct ?? 80),
            'stocks_bot_win_percent' => (float) $request->input('stocks_bot_win_percent', $settings->stocks_bot_win_percent ?? 55),
            'stocks_min_bet' => (float) $request->input('stocks_min_bet', $settings->stocks_min_bet ?? 10),
            'stocks_max_bet' => (float) $request->input('stocks_max_bet', $settings->stocks_max_bet ?? 1000),
            'stocks_max_open' => (int) $request->input('stocks_max_open', $settings->stocks_max_open ?? 5),
            'stocks_durations' => $stockDurations,
        ]);

        return back()->with('success', 'FX Trade settings updated successfully.');
    }

    public function stocksAssets(Request $request)
    {
        $data = $request->validate([
            'assets' => 'required|array',
            'assets.*.name' => 'required|string|max:80',
            'assets.*.active' => 'nullable|in:0,1',
            'new_symbol' => 'nullable|string|max:20',
            'new_name' => 'nullable|string|max:80',
            'new_yahoo' => 'nullable|string|max:40',
            'new_twelve' => 'nullable|string|max:40',
            'new_decimals' => 'nullable|integer|min:0|max:8',
        ]);

        foreach ($data['assets'] as $id => $row) {
            $asset = FxAsset::find($id);
            if (!$asset) {
                continue;
            }
            $asset->name = $row['name'];
            $asset->active = (int) ($row['active'] ?? 0) === 1;
            $asset->save();
        }

        $symbol = strtoupper(trim((string) ($data['new_symbol'] ?? '')));
        if ($symbol !== '') {
            if (empty($data['new_name']) || (empty($data['new_yahoo']) && empty($data['new_twelve']))) {
                return back()->withErrors(['new_symbol' => 'New market needs a name and a price symbol.'])->withInput();
            }
            if (FxAsset::where('symbol', $symbol)->exists()) {
                return back()->withErrors(['new_symbol' => 'That market already exists.'])->withInput();
            }
            FxAsset::create([
                'symbol' => $symbol,
                'name' => $data['new_name'],
                'yahoo_symbol' => $data['new_yahoo'] ?: $symbol,
                'twelve_symbol' => $data['new_twelve'] ?: $symbol,
                'decimals' => (int) ($data['new_decimals'] ?? 5),
                'active' => 1,
                'sort_order' => (int) FxAsset::max('sort_order') + 1,
            ]);
        }

        return back()->with('success', 'Forex Trade markets updated.');
    }

    private function stockDurationsFromRequest(Request $request): array|false
    {
        $sent = (array) $request->input('stocks_durations', []);
        $remove = (array) $request->input('stocks_duration_remove', []);
        $durations = [];
        foreach ($sent as $seconds => $flag) {
            $seconds = (int) $seconds;
            if ($seconds < 5 || $seconds > 86400 || (int) $flag !== 1) {
                continue;
            }
            if ((int) ($remove[$seconds] ?? $remove[(string) $seconds] ?? 0) === 1) {
                continue;
            }
            $durations[$seconds] = 1;
        }

        $value = (int) $request->input('new_duration_value', 0);
        if ($value > 0) {
            $unit = (string) $request->input('new_duration_unit', 'seconds');
            $seconds = match ($unit) {
                'minutes' => $value * 60,
                'hours' => $value * 3600,
                default => $value,
            };
            if ($seconds < 5 || $seconds > 86400) {
                return false;
            }
            $durations[$seconds] = 1;
        }

        if ($durations === []) {
            return [];
        }
        ksort($durations);

        return $durations;
    }

    public function sandBoxApi(Request $request)
    {
        $basicControl = basicControl();
        $gateways = Gateway::where('status', 1)->get();
        if ($request->isMethod('get')) {
            return view('admin.control_panel.apiSandbox', compact('basicControl', 'gateways'));
        } else {
            $purifiedData = $request->all();

            $validator = Validator::make($purifiedData, [
                'gateways' => 'required',
            ]);
            if ($validator->fails()) {
                return back()->withErrors($validator)->withInput();
            }
            $purifiedData = (object)$purifiedData;
            $basicControl->sandbox_gateways = $purifiedData->gateways;
            $basicControl->save();

            return back()->with('success', 'Successfully Updated');
        }
    }

    public function theme(Request $request)
    {
        $configThemes  = config('themes');
        $allThemes = collect($configThemes)->except('default');

        $basicControl = basicControl();
        return view('admin.frontend_management.manage-theme', compact('basicControl','allThemes'));
    }

    public function userLayoutUpdate(Request $request)
    {
        $layout = $request->input('user_layout');
        $allowedThemes = ['horizontal', 'vertical'];
        if(!in_array($layout,$allowedThemes)){
            return response()->json(['error'=>"Invalid Request"]);
        }

        $basicConfig = config('basic');
        $basicConfig['default_user_layout'] = $layout;
        $path = config_path('basic.php');
        $newContent = "<?php\n\nreturn " . var_export($basicConfig, true) . ";\n";
        file_put_contents($path, $newContent);


        $message = ucfirst($layout) .' layout selected.';
        session()->flash('success', $message);
        Artisan::call('optimize:clear');

        return response()->json(['message' => $message]);
    }

    public function themeUpdate(Request $request)
    {
        $theme = $request->input('theme');
        $allowedThemes = getThemeNames();
        if(!in_array($theme,$allowedThemes)){
            return response()->json(['error'=>"Invalid Request"]);
        }

        $basic = BasicControl::firstOrCreate();
        $basic->theme = $theme;
        $basic->save();

        $message = request()->theme_name .' theme selected.';
        return response()->json(['message' => $message]);
    }





    public function addonIndex()
    {
        $statuses = getModuleStatuses();

        $modules = collect(File::directories(base_path('Modules')))
            ->map(function ($path) use ($statuses) {
                $name = basename($path);
                $configPath = $path . '/module.json';

                $config = $this->getModuleConfig($configPath);

                $routeKey = strtolower($name);
                $routeName = "admin.$routeKey.settings";
                $routeLink = Route::has($routeName) ? route($routeName) : '';

                return [
                    'name' => $name,
                    'icon' => $config['icon'] ?? 'bi-plugin',
                    'description' => $config['description'] ?? 'No description provided.',
                    'author' => $config['author'] ?? null,
                    'version' => $config['version'] ?? null,
                    'enabled' => $statuses[$name] ?? false,
                    'route_link' => $routeLink,
                ];
            });

        return view('admin.control_panel.addon_manager', compact('modules'));
    }

    protected function getModuleConfig($configPath)
    {
        if (!file_exists($configPath)) {
            return [];
        }

        $json = json_decode(file_get_contents($configPath), true);
        return is_array($json) ? $json : [];
    }

    public function addonToggle($module)
    {
        $path = base_path('modules_statuses.json');

        if (!File::exists($path)) {
            File::put($path, json_encode([], JSON_PRETTY_PRINT));
        }

        $statuses = json_decode(File::get($path), true);
        if (!is_array($statuses)) {
            $statuses = [];
        }

        $previousStatus = $statuses[$module] ?? false;
        $newStatus = !$previousStatus;

        $statuses[$module] = $newStatus;

        File::put($path, json_encode($statuses, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        // On first-time activation (only if previously disabled)
        if ($newStatus && !$previousStatus) {

            $migrationPath = "Modules/$module/Database/Migrations";
            $migrationPath = str_replace('/', DIRECTORY_SEPARATOR, $migrationPath);

            if (File::isDirectory(base_path($migrationPath))) {
                Artisan::call('migrate', [
                    '--path' => $migrationPath,
                    '--force' => true,
                ]);

                info("Module [$module] migration run: $migrationPath");
            } else {
                info("Migration path not found for [$module]");
            }

            $seeder = "Modules\\$module\\Database\\Seeders\\{$module}DatabaseSeeder";
            info($seeder);

            if (class_exists($seeder)) {
                Artisan::call('module:seed', [
                    'module' => $module,
                    '--force' => true,
                ]);
                info("{$module} seeded");
            }
            Artisan::call('optimize:clear');
        }

        return back()->with('success', "Module '{$module}' has been " . ($newStatus ? 'enabled' : 'disabled') . ".");
    }



}
