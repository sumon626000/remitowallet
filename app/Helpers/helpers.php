<?php

use App\Models\BasicControl;
use App\Models\ContentDetails;
use App\Models\ManageMenu;
use App\Models\Page;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use App\Models\Language;
use App\Models\PageDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;


if (!function_exists('getTheme')) {
    function getTheme()
    {
        $defaultTheme = config('themes.default', 'rivo');

        $keywords = ['public', 'qr-payment', 'invoice', 'make/payment'];
        $urlContainsPublic = Str::contains(request()->getRequestUri(), $keywords);
        if ($urlContainsPublic) {
            $activeTheme = $defaultTheme;
        } else {
            $activeTheme = session('active_theme') ?? basicControl()->theme ?? $defaultTheme;
        }
        return $activeTheme;
    }
}

if (!function_exists('getThemeNames')) {
    function getThemeNames(): array
    {
        $directory = resource_path('views/themes');
        return File::isDirectory($directory) ? array_map('basename', File::directories($directory)) : [];
    }
}

if (!function_exists('template')) {
    function template($asset = false): string
    {
        $activeTheme = getTheme();

        if ($asset) return 'assets/themes/' . $activeTheme . '/';
        return 'themes.' . $activeTheme . '.';
    }
}

if (!function_exists('footerData')) {
    function footerData()
    {
        $activeTheme = getTheme();
        $footerContent = Cache::remember("footer_content_{$activeTheme}", now()->addMinutes(60), function () {
            return ContentDetails::with('content')->whereRelation('content', 'name', 'footer')->get();
        });

        $singleContent = $footerContent->firstWhere('content.type', 'single');
        $footerData = [
            'single' => $singleContent
                ? collect($singleContent->description)->merge($singleContent->content->only('media'))
                : [],
            'multiple' => $footerContent->where('content.type', 'multiple')
                ->map(fn($item) => collect($item->description)->merge($item->content->only('media'))),
        ];
        $extraInfo = Cache::remember('extra_info_content', now()->addMinutes(60), function () {
            return ContentDetails::with('content')
                ->whereHas('content', function ($query) {
                    $query->whereIn('name', ['contact', 'social']);
                })
                ->get()
                ->groupBy('content.name');
        });

        $languages = Cache::remember('active_languages', now()->addMinutes(60), function () {
            return Language::query()->orderBy('default_status', 'desc')->where('status', 1)->get();
        });
        return [
            'footer' => $footerData,
            'extraInfo' => $extraInfo,
            'languages' => $languages,
        ];
    }
}

if (!function_exists('getPageSections')) {
    function getPageSections()
    {
        $theme = basicControl()->theme;
        $sectionsPath = resource_path("views/themes/{$theme}/sections");
        $pattern = $sectionsPath . '/*';
        $files = glob($pattern);

        $fileBaseNames = [];
        foreach ($files as $file) {
            if (is_file($file)) {
                $basename = basename($file);
                $sectionKey = str_replace('.blade.php', '', $basename);

                $baseConfig = config("contents.all.{$sectionKey}", []);
                $themeConfig = config("contents.{$theme}.{$sectionKey}", []);
                $mergedConfig = array_replace_recursive($baseConfig, $themeConfig);
                $contentImage = $mergedConfig['preview'] ?? null;
                $previewPath = str_replace(':theme', $theme, $contentImage);
                $fileBaseNames[$sectionKey] = [
                    'basename' => $sectionKey,
                    'preview' => $previewPath,
                    'optionText' => ucwords(stringToTitle(str_replace(basicControl()->theme, '', $sectionKey))),
                ];
            }
        }
        return $fileBaseNames;
    }
}

if (!function_exists('stringToTitle')) {
    function stringToTitle($string)
    {
        return implode(' ', array_map('ucwords', explode(' ', preg_replace('/[^a-zA-Z0-9]+/', ' ', $string))));
    }
}

if (!function_exists('getTitle')) {
    function getTitle($title)
    {
        if ($title == "sms") {
            return strtoupper(preg_replace('/[^A-Za-z0-9]/', ' ', $title));
        }
        return ucwords(preg_replace('/[^A-Za-z0-9]/', ' ', $title));
    }
}

if (!function_exists('getRoute')) {
    function getRoute($route, $params = null)
    {
        return isset($params) ? route($route, $params) : route($route);
    }
}

if (!function_exists('hex2rgba')) {
    function hex2rgba($color, $opacity = false)
    {
        $default = 'rgb(0,0,0)';

        if (empty($color))
            return $default;

        if ($color[0] == '#') {
            $color = substr($color, 1);
        }

        if (strlen($color) == 6) {
            $hex = array($color[0] . $color[1], $color[2] . $color[3], $color[4] . $color[5]);
        } elseif (strlen($color) == 3) {
            $hex = array($color[0] . $color[0], $color[1] . $color[1], $color[2] . $color[2]);
        } else {
            return $default;
        }

        $rgb = array_map('hexdec', $hex);

        if ($opacity) {
            if (abs($opacity) > 1)
                $opacity = 1.0;
            $output = 'rgba(' . implode(",", $rgb) . ',' . $opacity . ')';
        } else {
            $output = 'rgb(' . implode(",", $rgb) . ')';
        }
        return $output;
    }
}

if (!function_exists('basicControl')) {
    function basicControl()
    {
        if (session()->get('themeMode') == null) {
            session()->put('themeMode', 'auto');
        }

        try {
            DB::connection()->getPdo();
            $configure = \Cache::get('ConfigureSetting');
            if (!$configure) {
                $configure = BasicControl::firstOrCreate();
                \Cache::put('ConfigureSetting', $configure);
            }

            return $configure;
        } catch (\Exception $e) {

        }
    }
}

if (!function_exists('fxTradeSettings')) {
    function fxTradeSettings(?array $updates = null): object
    {
        $path = storage_path('app/fx_trade_settings.json');
        $defaults = [
            'fx_enabled' => 1,
            'fx_win_percent' => 45,
            'fx_payout_mult' => 1.9,
            'fx_min_bet' => 10,
            'fx_max_bet' => 1000,
            'royal_enabled' => 1,
            'royal_win_percent' => 88,
            'royal_min_bet' => 1,
            'royal_max_bet' => 1000,
            'bot_enabled' => 1,
            'bot_win_percent' => 55,
            'bot_payout_mult' => 1.9,
            'bot_min_bet' => 10,
            'bot_max_bet' => 1000,
            'stocks_enabled' => 1,
            'stocks_min_bet' => 10,
            'stocks_max_bet' => 1000,
            'stocks_payout_pct' => 80,
            'stocks_bot_win_percent' => 55,
            'stocks_max_open' => 5,
            'stocks_durations' => [
                5 => 1, 15 => 1, 30 => 1, 45 => 1, 60 => 1, 120 => 1,
                300 => 1, 600 => 1, 900 => 1, 1800 => 1, 3600 => 1,
            ],
        ];

        $current = $defaults;
        if (is_file($path)) {
            $decoded = json_decode((string) file_get_contents($path), true);
            if (is_array($decoded)) {
                $current = array_merge($defaults, $decoded);
            }
        }

        if ($updates !== null) {
            $current = array_merge($current, $updates);
            foreach (['fx_enabled', 'royal_enabled', 'bot_enabled', 'stocks_enabled'] as $flag) {
                $current[$flag] = !empty($current[$flag]) ? 1 : 0;
            }
            $current['fx_win_percent'] = max(0, min(100, (float) $current['fx_win_percent']));
            $current['royal_win_percent'] = max(0, min(100, (float) $current['royal_win_percent']));
            $current['bot_win_percent'] = max(0, min(100, (float) $current['bot_win_percent']));
            $current['fx_payout_mult'] = max(1, min(10, (float) $current['fx_payout_mult']));
            $current['bot_payout_mult'] = max(1, min(10, (float) $current['bot_payout_mult']));
            $current['fx_min_bet'] = max(0.01, (float) $current['fx_min_bet']);
            $current['fx_max_bet'] = max($current['fx_min_bet'], (float) $current['fx_max_bet']);
            $current['royal_min_bet'] = max(0.01, (float) $current['royal_min_bet']);
            $current['royal_max_bet'] = max($current['royal_min_bet'], (float) $current['royal_max_bet']);
            $current['bot_min_bet'] = max(0.01, (float) $current['bot_min_bet']);
            $current['bot_max_bet'] = max($current['bot_min_bet'], (float) $current['bot_max_bet']);
            $current['stocks_payout_pct'] = max(1, min(500, (float) ($current['stocks_payout_pct'] ?? 80)));
            $current['stocks_bot_win_percent'] = max(0, min(100, (float) ($current['stocks_bot_win_percent'] ?? 55)));
            $current['stocks_min_bet'] = max(0.01, (float) ($current['stocks_min_bet'] ?? 10));
            $current['stocks_max_bet'] = max($current['stocks_min_bet'], (float) ($current['stocks_max_bet'] ?? 1000));
            $current['stocks_max_open'] = max(1, min(50, (int) ($current['stocks_max_open'] ?? 5)));
            $rawDurations = (array) ($current['stocks_durations'] ?? []);
            $durations = [];
            foreach ($rawDurations as $seconds => $flag) {
                $seconds = (int) $seconds;
                if ($seconds >= 5 && $seconds <= 86400 && !empty($flag)) {
                    $durations[$seconds] = 1;
                }
            }
            ksort($durations);
            $current['stocks_durations'] = $durations;
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }
            file_put_contents($path, json_encode($current, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        }

        return (object) $current;
    }
}

if (!function_exists('checkTo')) {
    function checkTo($currencies, $selectedCurrency = 'USD')
    {
        foreach ($currencies as $key => $currency) {
            if (property_exists($currency, strtoupper($selectedCurrency))) {
                return $key;
            }
        }
    }
}


if (!function_exists('controlPanelRoutes')) {
    function controlPanelRoutes()
    {
        $listRoutes = collect([]);
        $listRoutes->push(config('generalsettings.settings'));
        $listRoutes->push(config('generalsettings.plugin'));
        $listRoutes->push(config('generalsettings.in-app-notification'));
        $listRoutes->push(config('generalsettings.push-notification'));
        $listRoutes->push(config('generalsettings.email'));
        $listRoutes->push(config('generalsettings.sms'));
        $list = $listRoutes->collapse()->map(function ($item) {
            return $item['route'];
        })->values()->push('admin.settings')->unique();
        return $list;
    }
}


if (!function_exists('menuActive')) {
    function menuActive($routeName, $type = null)
    {
        $class = 'active';
        if ($type == 3) {
            $class = 'active collapsed';
        } elseif ($type == 2) {
            $class = 'show';
        }

        if (is_array($routeName)) {
            foreach ($routeName as $key => $value) {
                if (request()->routeIs($value)) {
                    return $class;
                }
            }
        } elseif (request()->routeIs($routeName)) {
            return $class;
        }
    }
}

if (!function_exists('isMenuActive')) {
    function isMenuActive($routes, $type = 0)
    {
        $class = [
            '0' => 'active',
            '1' => 'style=display:block',
            '2' => true
        ];

        if (is_array($routes)) {
            foreach ($routes as $key => $route) {
                if (request()->routeIs($route)) {
                    return $class[$type];
                }
            }
        } elseif (request()->routeIs($routes)) {
            return $class[$type];
        }

        if ($type == 1) {
            return 'style=display:none';
        } else {
            return false;
        }
    }
}


if (!function_exists('strRandom')) {
    function strRandom($length = 12)
    {
        $characters = 'ABCDEFGHJKMNOPQRSTUVWXYZ123456789';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}


if (!function_exists('getFile')) {
    function getFile($disk = 'local', $image = '', $upload = false)
    {
        $default = ($upload == true) ? asset(config('filelocation.default2')) : asset(config('filelocation.default'));
        try {
            if ($disk == 'local') {
                $localImage = asset('/assets/upload') . '/' . $image;
                return !empty($image) && Storage::disk($disk)->exists($image) ? $localImage : $default;
            } else {
                return !empty($image) && Storage::disk($disk)->exists($image) ? Storage::disk($disk)->url($image) : $default;
            }
        } catch (Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('getFileForEdit')) {
    function getFileForEdit($disk = 'local', $image = null)
    {
        try {
            if ($disk == 'local') {
                $localImage = asset('/assets/upload') . '/' . $image;
                return !empty($image) && Storage::disk($disk)->exists($image) ? $localImage : null;
            } else {
                return !empty($image) && Storage::disk($disk)->exists($image) ? Storage::disk($disk)->url($image) : asset(config('filelocation.default'));
            }
        } catch (Exception $e) {
            return null;
        }
    }
}

if (!function_exists('title2snake')) {
    function title2snake($string)
    {
        return Str::title(str_replace(' ', '_', $string));
    }
}

if (!function_exists('snake2Title')) {
    function snake2Title($string)
    {
        return Str::title(str_replace('_', ' ', $string));
    }
}

if (!function_exists('kebab2Title')) {
    function kebab2Title($string)
    {
        return Str::title(str_replace('-', ' ', $string));
    }
}


if (!function_exists('getMethodCurrency')) {
    function getMethodCurrency($gateway)
    {
        foreach ($gateway->currencies ?? [] as $currency) {
            if (in_array($currency, (array)$gateway->supported_currency)) {
                return $currency; // Return the first matching currency
            }
        }
        return 'USD';
    }
}

if (!function_exists('twoStepPrevious')) {
    function twoStepPrevious($deposit)
    {
        if ($deposit->depositable_type == null) {
            return route('fund.initialize');
        }
        return route('fund.initialize');

    }
}


if (!function_exists('slug')) {
    function slug($title)
    {
        return Str::slug($title);
    }
}

if (!function_exists('clean')) {
    function clean($string)
    {
        $string = str_replace(' ', '_', $string); // Replaces all spaces with hyphens.
        return preg_replace('/[^A-Za-z0-9\-]/', '', $string); // Removes special chars.
    }
}

if (!function_exists('diffForHumans')) {
    function diffForHumans($date)
    {
        $lang = session()->get('lang') ?? 'en';
        \Carbon\Carbon::setlocale($lang);
        return \Carbon\Carbon::parse($date)->diffForHumans();
    }
}

if (!function_exists('loopIndex')) {
    function loopIndex($object)
    {
        return ($object->currentPage() - 1) * $object->perPage() + 1;
    }
}

if (!function_exists('dateTime')) {
    function dateTime($date = null, $format = null)
    {
        if (is_null($date)) {
            return 'N/A';
        }
        $format = $format ?? basicControl()->date_time_format;
        return date($format, strtotime($date));
    }
}


if (!function_exists('getProjectDirectory')) {
    function getProjectDirectory()
    {
        return str_replace((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]", "", url("/"));
    }
}

if (!function_exists('defaultLang')) {
    function defaultLang()
    {
        return Language::where('default_status', true)->first();
    }
}

if (!function_exists('removeHyphenInString')) {
    function removeHyphenInString($string)
    {
        return str_replace("_", " ", $string);
    }
}

function updateWallet($user_id, $currency_id, $amount, $action = 0)
{
    return DB::transaction(function () use ($user_id, $currency_id, $amount, $action) {

        // Lock the wallet row for this transaction
        $wallet = \App\Models\Wallet::where('user_id', $user_id)
            ->where('currency_id', $currency_id)
            ->lockForUpdate()
            ->first();

        // Create wallet if not exists (inside transaction)
        if (!$wallet) {
            $wallet = \App\Models\Wallet::create([
                'user_id' => $user_id,
                'currency_id' => $currency_id,
                'balance' => 0,
            ]);
        }

        if ($action == 1) { // add money
            $wallet->balance += $amount;
        } else { // deduct money
            if ($amount > $wallet->balance) {
                throw new \Exception('Insufficient balance');
            }
            $wallet->balance -= $amount;
        }

        $wallet->save();

        return $wallet->balance;
    });
}


function getAmount($amount, $length = 0)
{
    if ($amount == 0) {
        return 0;
    }
    if ($length == 0) {
        preg_match("#^([\+\-]|)([0-9]*)(\.([0-9]*?)|)(0*)$#", trim($amount), $o);
        return $o[1] . sprintf('%d', $o[2]) . ($o[3] != '.' ? $o[3] : '');
    }

    return round($amount, $length);
}


if (!function_exists('currencyPosition')) {
    function currencyPosition($amount, $currencyId = null)
    {
        $basic = basicControl();
        $amount = fractionNumber($amount, true, $currencyId);

        static $currencies = [];
        if ($currencyId) {
            if (!isset($currencies[$currencyId])) {
                $currencies[$currencyId] = \App\Models\Currency::find($currencyId);
            }
            $currency = $currencies[$currencyId];
        } else {
            $currency = null;
        }

        $symbol = $currency->symbol ?? $basic->currency_symbol;
        $code = $currency->code ?? $basic->base_currency;
        $isLeft = $basic->is_currency_position == 'left';
        $hasSpace = $basic->has_space_between_currency_and_amount;

        return $isLeft
            ? ($hasSpace ? "{$symbol} {$amount}" : "{$symbol}{$amount}")
            : ($hasSpace ? "{$amount} {$code}" : "{$amount}{$code}");
    }
}


if (!function_exists('fractionNumber')) {
    function fractionNumber($amount, $afterComma = true, $currencyId = null)
    {
        $basic = basicControl();
        $fraction = $basic->fraction_number;
        if ($currencyId) {
            $currency = \App\Models\Currency::find($currencyId);
            if ($currency) {
                $fraction = $currency->currency_type ? $fraction : 8;
            }
        }

        if (!$afterComma) {
            return number_format($amount + 0);
        }

        $formattedAmount = number_format($amount, $fraction ?? 2);
        return rtrim(rtrim($formattedAmount, '0'), '.');

    }
}


function hextorgb($hexstring)
{
    $integar = hexdec($hexstring);
    return array("red" => 0xFF & ($integar >> 0x10),
        "green" => 0xFF & ($integar >> 0x8),
        "blue" => 0xFF & $integar);
}

function renderCaptCha($rand)
{
//    session_start();
    $captcha_code = '';
    $captcha_image_height = 50;
    $captcha_image_width = 130;
    $total_characters_on_image = 6;

    $possible_captcha_letters = 'bcdfghjkmnpqrstvwxyz23456789';
    $captcha_font = 'assets/monofont.ttf';

    $random_captcha_dots = 50;
    $random_captcha_lines = 25;
    $captcha_text_color = "0x142864";
    $captcha_noise_color = "0x142864";


    $count = 0;
    while ($count < $total_characters_on_image) {
        $captcha_code .= substr(
            $possible_captcha_letters,
            mt_rand(0, strlen($possible_captcha_letters) - 1),
            1);
        $count++;
    }


    $captcha_font_size = $captcha_image_height * 0.65;
    $captcha_image = @imagecreate(
        $captcha_image_width,
        $captcha_image_height
    );

    /* setting the background, text and noise colours here */
    $background_color = imagecolorallocate(
        $captcha_image,
        255,
        255,
        255
    );

    $array_text_color = hextorgb($captcha_text_color);
    $captcha_text_color = imagecolorallocate(
        $captcha_image,
        $array_text_color['red'],
        $array_text_color['green'],
        $array_text_color['blue']
    );

    $array_noise_color = hextorgb($captcha_noise_color);
    $image_noise_color = imagecolorallocate(
        $captcha_image,
        $array_noise_color['red'],
        $array_noise_color['green'],
        $array_noise_color['blue']
    );

    /* Generate random dots in background of the captcha image */
    for ($count = 0; $count < $random_captcha_dots; $count++) {
        imagefilledellipse(
            $captcha_image,
            mt_rand(0, $captcha_image_width),
            mt_rand(0, $captcha_image_height),
            2,
            3,
            $image_noise_color
        );
    }

    /* Generate random lines in background of the captcha image */
    for ($count = 0; $count < $random_captcha_lines; $count++) {
        imageline(
            $captcha_image,
            mt_rand(0, $captcha_image_width),
            mt_rand(0, $captcha_image_height),
            mt_rand(0, $captcha_image_width),
            mt_rand(0, $captcha_image_height),
            $image_noise_color
        );
    }

    /* Create a text box and add 6 captcha letters code in it */
    $text_box = imagettfbbox(
        $captcha_font_size,
        0,
        $captcha_font,
        $captcha_code
    );
    $x = ($captcha_image_width - $text_box[4]) / 2;
    $y = ($captcha_image_height - $text_box[5]) / 2;
    imagettftext(
        $captcha_image,
        $captcha_font_size,
        0,
        $x,
        $y,
        $captcha_text_color,
        $captcha_font,
        $captcha_code
    );

    /* Show captcha image in the html page */
// defining the image type to be shown in browser widow
    header('Content-Type: image/jpeg');
    imagejpeg($captcha_image); //showing the image
    imagedestroy($captcha_image); //destroying the image instance
//    $_SESSION['captcha'] = $captcha_code;

    session()->put('captcha', $captcha_code);
}

function getIpInfo()
{
//	$ip = '210.1.246.42';
    $ip = null;
    $deep_detect = TRUE;

    if (filter_var($ip, FILTER_VALIDATE_IP) === FALSE) {
        $ip = $_SERVER["REMOTE_ADDR"];
        if ($deep_detect) {
            if (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP))
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        }
    }
    $xml = @simplexml_load_file("http://www.geoplugin.net/xml.gp?ip=" . $ip);

    $country = @$xml->geoplugin_countryName;
    $city = @$xml->geoplugin_city;
    $area = @$xml->geoplugin_areaCode;
    $code = @$xml->geoplugin_countryCode;
    $long = @$xml->geoplugin_longitude;
    $lat = @$xml->geoplugin_latitude;


    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $os_platform = "Unknown OS Platform";
    $os_array = array(
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/windows nt 6.0/i' => 'Windows Vista',
        '/windows nt 5.2/i' => 'Windows Server 2003/XP x64',
        '/windows nt 5.1/i' => 'Windows XP',
        '/windows xp/i' => 'Windows XP',
        '/windows nt 5.0/i' => 'Windows 2000',
        '/windows me/i' => 'Windows ME',
        '/win98/i' => 'Windows 98',
        '/win95/i' => 'Windows 95',
        '/win16/i' => 'Windows 3.11',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/mac_powerpc/i' => 'Mac OS 9',
        '/linux/i' => 'Linux',
        '/ubuntu/i' => 'Ubuntu',
        '/iphone/i' => 'iPhone',
        '/ipod/i' => 'iPod',
        '/ipad/i' => 'iPad',
        '/android/i' => 'Android',
        '/blackberry/i' => 'BlackBerry',
        '/webos/i' => 'Mobile'
    );
    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
        }
    }
    $browser = "Unknown Browser";
    $browser_array = array(
        '/msie/i' => 'Internet Explorer',
        '/firefox/i' => 'Firefox',
        '/safari/i' => 'Safari',
        '/chrome/i' => 'Chrome',
        '/edge/i' => 'Edge',
        '/opera/i' => 'Opera',
        '/netscape/i' => 'Netscape',
        '/maxthon/i' => 'Maxthon',
        '/konqueror/i' => 'Konqueror',
        '/mobile/i' => 'Handheld Browser'
    );
    foreach ($browser_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $browser = $value;
        }
    }

    $data['country'] = $country;
    $data['city'] = $city;
    $data['area'] = $area;
    $data['code'] = $code;
    $data['long'] = $long;
    $data['lat'] = $lat;
    $data['os_platform'] = $os_platform;
    $data['browser'] = $browser;
    $data['ip'] = request()->ip();
    $data['time'] = date('d-m-Y h:i:s A');

    return $data;
}


if (!function_exists('convertRate')) {
    function convertRate($currencyCode, $payout)
    {
        $convertRate = 0;
        $rate = optional($payout->method)->convert_rate;
        if ($rate) {
            $convertRate = $rate->$currencyCode;
        }
        return (float)$convertRate;
    }
}
if (!function_exists('stringToRouteName')) {
    function stringToRouteName($string)
    {
        $result = preg_replace('/[^a-zA-Z0-9]+/', '.', $string);
        return empty($result) || $result == '.' ? 'home' : $result;
    }
}
function browserIcon($string)
{
    $list = [
        "Unknown Browser" => "unknown",
        'Internet Explorer' => 'internetExplorer',
        'Firefox' => 'firefox',
        'Safari' => 'safari',
        'Chrome' => 'chrome',
        'Edge' => 'edge',
        'Opera' => 'opera',
        'Netscape' => 'netscape',
        'Maxthon' => 'maxthon',
        'Konqueror' => 'unknown',
        'UC Browser' => 'ucBrowser',
        'Safari Browser' => 'safari'];
    return $list[$string] ?? 'unknown';

}


function deviceIcon($string)
{
    $list = [
        'Tablet' => 'bi-laptop',
        'Mobile' => 'bi-phone',
        'Computer' => 'bi-display'];
    return $list[$string] ?? '';

}

if (!function_exists('timeAgo')) {
    function timeAgo($timestamp)
    {
        //$time_now = mktime(date('h')+0,date('i')+30,date('s'));
        $datetime1 = new DateTime("now");
        $datetime2 = date_create($timestamp);
        $diff = date_diff($datetime1, $datetime2);
        $timemsg = '';
        if ($diff->y > 0) {
            $timemsg = $diff->y . ' year' . ($diff->y > 1 ? "s" : '');

        } else if ($diff->m > 0) {
            $timemsg = $diff->m . ' month' . ($diff->m > 1 ? "s" : '');
        } else if ($diff->d > 0) {
            $timemsg = $diff->d . ' day' . ($diff->d > 1 ? "s" : '');
        } else if ($diff->h > 0) {
            $timemsg = $diff->h . ' hour' . ($diff->h > 1 ? "s" : '');
        } else if ($diff->i > 0) {
            $timemsg = $diff->i . ' minute' . ($diff->i > 1 ? "s" : '');
        } else if ($diff->s > 0) {
            $timemsg = $diff->s . ' second' . ($diff->s > 1 ? "s" : '');
        }
        if ($timemsg == "")
            $timemsg = "Just now";
        else
            $timemsg = $timemsg . ' ago';

        return $timemsg;
    }
}

if (!function_exists('code')) {
    function code($length)
    {
        if ($length == 0) return 0;
        $min = pow(10, $length - 1);
        $max = 0;
        while ($length > 0 && $length--) {
            $max = ($max * 10) + 9;
        }
        return random_int($min, $max);
    }
}


if (!function_exists('recursive_array_replace')) {
    function recursive_array_replace($find, $replace, $array)
    {
        if (!is_array($array)) {
            return str_ireplace($find, $replace, $array);
        }
        $newArray = [];
        foreach ($array as $key => $value) {
            $newArray[$key] = recursive_array_replace($find, $replace, $value);
        }
        return $newArray;
    }
}

if (!function_exists('getHeaderMenuData')) {
    function getHeaderMenuData()
    {
        $menu = ManageMenu::where('menu_section', 'header')->byActiveTheme()->first();
        $menuData = [];

        if ($menu->menu_items && count($menu->menu_items) > 0) {
            foreach ($menu->menu_items as $key => $menuItem) {
                if (is_numeric($key)) {
                    $pageDetails = getPageDetails($menuItem);
                    $menuIDetails = [
                        'name' => $pageDetails->page_name ?? $pageDetails->name ?? $menuItem,
                        'route' => isset($pageDetails->slug) ? route('page', $pageDetails->slug) : ($pageDetails->custom_link ?? staticPagesAndRoutes($menuItem)),
                    ];
                } elseif (is_array($menuItem)) {
                    $pageDetails = getPageDetails($key);
                    $child = getHeaderChildMenu($menuItem);
                    $menuIDetails = [
                        'name' => $pageDetails->page_name ?? $pageDetails->name,
                        'route' => isset($pageDetails->slug) ? route('page', $pageDetails->slug) : ($pageDetails->custom_link ?? staticPagesAndRoutes($key)),
                        'child' => $child
                    ];
                }
                $menuData[] = $menuIDetails;
            }
        }
        return $menuData;
    }
}

if (!function_exists('staticPagesAndRoutes')) {
    function staticPagesAndRoutes($name)
    {
        return [
            'blog' => 'blog',
        ][$name] ?? $name;
    }
}


if (!function_exists('getHeaderChildMenu')) {
    function getHeaderChildMenu($menuItem, $menuData = [])
    {
        foreach ($menuItem as $key => $item) {
            if (is_numeric($key)) {
                $pageDetails = getPageDetails($item);
                $menuData[] = [
                    'name' => $pageDetails->page_name ?? $pageDetails->name ?? $item,
                    'route' => isset($pageDetails->slug) ? route('page', $pageDetails->slug) : ($pageDetails->custom_link ?? staticPagesAndRoutes($item)),
                ];
            } elseif (is_array($item)) {
                $pageDetails = getPageDetails($key);
                $child = getHeaderChildMenu($item);
                $menuData[] = [
                    'name' => $pageDetails->page_name ?? $pageDetails->name ?? $key,
                    'route' => isset($pageDetails->slug) ? route('page', $pageDetails->slug) : ($pageDetails->custom_link ?? staticPagesAndRoutes($key)),
                    'child' => $child
                ];
            } else {
                $pageDetails = getPageDetails($key);
                $child = getHeaderChildMenu([$item]);
                $menuData[] = [
                    'name' => $pageDetails->page_name ?? $pageDetails->name ?? $key,
                    'route' => isset($pageDetails->slug) ? route('page', $pageDetails->slug) : ($pageDetails->custom_link ?? staticPagesAndRoutes($key)),
                    'child' => $child
                ];
            }
        }
        return $menuData;
    }
}


if (!function_exists('getPageDetails')) {
    function getPageDetails($name)
    {
        try {
            DB::connection()->getPdo();
            $lang = session('lang');
            return Cache::remember("page_details_{$name}_{$lang}", now()->addMinutes(30),
                function () use ($name, $lang) {
                    return Page::select('id', 'name', 'slug', 'custom_link')
                        ->where('name', $name)
                        ->addSelect([
                            'page_name' => PageDetail::with('language')
                                ->select('name')
                                ->whereHas('language', function ($query) use ($lang) {
                                    $query->where('short_name', $lang);
                                })
                                ->whereColumn('page_id', 'pages.id')
                                ->limit(1)
                        ])
                        ->first();
                });
        } catch (\Exception $e) {
            \Log::error("Error fetching page details: " . $e->getMessage());
            return null;
        }
    }
}

if (!function_exists('renderHeaderMenu')) {
    function renderHeaderMenu($menuItems)
    {
        $theme = getTheme();
        if ($menuItems) {
            foreach ($menuItems as $menuItem) {
                if ($theme == "nova") {
                    if (isset($menuItem['child']) && count($menuItem['child']) > 0) {
                        echo "<li class=\"dropdown\">";
                        echo "<a href=\"javascript:void(0);\">{$menuItem['name']}</a>";
                        echo "<ul>";
                        renderHeaderMenu($menuItem['child'], true);
                        echo "</ul>";
                    } else {
                        $activeClass = (strtolower(getLastSegment()) == strtolower($menuItem['name'])) ? "class=\"active\"" : "";
                        echo "<li><a href=\"{$menuItem['route']}\" $activeClass>{$menuItem['name']}</a></li>";
                    }
                } else {

                    if (isset($menuItem['child'])) {
                        echo '<li class="dropdown text-capitalize">';
                        echo '<a href="javascript:void(0)"><span>' . $menuItem['name'] . '</span> <i class="bi bi-chevron-down"></i></a>';
                        renderHeaderMenu($menuItem['child']);
                    } else {
                        echo '<li>';
                        echo '<a  href="' . $menuItem['route'] . '">' . $menuItem['name'] . '</a>';
                    }
                    echo '</li>';
                }
            }
        }
        return '';
    }
}

function getLastSegment()
{
    $currentUrl = url()->current();
    $lastSegment = last(explode('/', $currentUrl));
    return $lastSegment;
}

if (!function_exists('getFooterMenuData')) {
    function getFooterMenuData($type)
    {
        $activeTheme = getTheme();
        $menu = Cache::remember("footer_manage_menu_{$activeTheme}", now()->addMinutes(30), function () {
            return ManageMenu::where('menu_section', 'footer')->byActiveTheme()->first();
        });
        $menuData = [];

        if (isset($menu->menu_items[$type])) {
            foreach ($menu->menu_items[$type] as $key => $menuItem) {
                $pageDetails = getPageDetails($menuItem);
                $menuIDetails = [
                    'name' => $pageDetails->page_name ?? $pageDetails->name ?? $menuItem,
                    'route' => isset($pageDetails->slug) ? route('page', $pageDetails->slug) : ($pageDetails->custom_link ?? staticPagesAndRoutes($menuItem)),
                ];
                $menuData[] = $menuIDetails;
            }
            foreach ($menuData as $item) {
                $che = '<li><i class="bi bi-chevron-right"></i><a class="text-capitalize" href="' . $item['route'] . '">' . $item['name'] . '</a></li>';
                $flattenedMenuData[] = $che;
            }
            return $flattenedMenuData;
        }
    }
}

function getPageName($name)
{
    try {
        DB::connection()->getPdo();
        $defaultLanguage = Cache::remember('default_language', now()->addMinutes(30), function () {
            return Language::query()->where('default_status', true)->first();
        });

        $pageDetails = Cache::remember("page_details_{$defaultLanguage->id}_{$name}", now()->addMinutes(30),
            function () use ($defaultLanguage, $name) {
                return PageDetail::select('id', 'page_id', 'name')
                    ->with('page:id,name,slug')
                    ->where('language_id', $defaultLanguage->id)
                    ->whereHas('page', function ($query) use ($name) {
                        $query->where('name', $name);
                    })
                    ->first();
            });
        return $pageDetails->name ?? $pageDetails->page->name ?? $name;
    } catch (\Exception $e) {

    }
}


function filterCustomLinkRecursive($collection, $lookingKey = '')
{

    $filterCustomLinkRecursive = function ($array) use (&$filterCustomLinkRecursive, $lookingKey) {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $filterCustomLinkRecursive($value);
            } elseif ($value === $lookingKey || $key === $lookingKey) {
                unset($array[$key]);
            }
        }
        return $array;
    };
    $filteredCollection = $filterCustomLinkRecursive($collection);

    return $filteredCollection;
}

if (!function_exists('maskString')) {
    function maskString($input)
    {
        $length = strlen($input);
        $visibleCharacters = 2;
        $maskedString = '<span class="masked ms-2">' . substr($input, 0, $visibleCharacters) . '<span class="highlight">' . str_repeat('*', $length - 2 * $visibleCharacters) . '</span>' . substr($input, -$visibleCharacters) . '</span>';
        return $maskedString;
    }
}

if (!function_exists('maskEmail')) {
    function maskEmail($email)
    {
        list($username, $domain) = explode('@', $email);
        $usernameLength = strlen($username);
        if ($usernameLength <= 4) {
            return $username[0] . str_repeat('*', $usernameLength - 1) . '@' . $domain;
        }
        $visibleCharacters = 2;
        $maskedUsername = substr($username, 0, $visibleCharacters)
            . str_repeat('*', $usernameLength - 2 * $visibleCharacters)
            . substr($username, -$visibleCharacters);

        return $maskedUsername . '@' . $domain;
    }
}

if (!function_exists('removeValue')) {
    function removeValue(&$array, $value)
    {
        foreach ($array as $key => &$subArray) {
            if (is_array($subArray)) {
                removeValue($subArray, $value);
            } else {
                if ($subArray === $value) {
                    unset($array[$key]);
                }
            }
        }
    }
}


if (!function_exists('strRandomNum')) {
    function strRandomNum($length = 15)
    {
        $characters = '1234567890';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}

if (!function_exists('getReloadlyFixedAmountAirtime')) {
    function getReloadlyFixedAmountAirtime($res)
    {
        $fixedAmount = [];
        if (isset($res['fixedAmounts']) && count($res['fixedAmounts']) > 0) {
            if (isset($res['fixedAmountsDescriptions']) && count((array)$res['fixedAmountsDescriptions']) > 0) {
                foreach ($res['fixedAmountsDescriptions'] as $key => $fix) {
                    $fixedAmount[] = [
                        'id' => $key,
                        'description' => $fix,
                    ];
                }
            } elseif (isset($res['localFixedAmounts']) && count((array)$res['localFixedAmounts']) > 0) {
                foreach ($res['localFixedAmounts'] as $key => $fix) {
                    $fixedAmount[] = [
                        'id' => $res['localFixedAmounts'][$key],
                        'description' => $fix,
                    ];
                }
            }
        } elseif (isset($res['geographicalRechargePlans']) && count((array)$res['geographicalRechargePlans']) > 0) {
            $geos = (array)$res['geographicalRechargePlans'][0]->fixedAmountsDescriptions;
            foreach ($geos as $key => $fix) {
                $fixedAmount[] = [
                    'id' => $key,
                    'description' => $fix,
                ];
            }
        }

        return $fixedAmount;
    }
}

if (!function_exists('getPhoneCode')) {
    function getPhoneCode($countryIso)
    {
        foreach (config('country') as $country) {
            if ($country['code'] == $countryIso) {
                return $country['phone_code'];
            }
        }
        return "+880";
    }
}

function getCountryName($countryCode)
{
    foreach (config('country') as $country) {
        if ($country['code'] == $countryCode) {
            return $country['name'];
        }
    }
    return $countryCode;
}

function cardCurrencyCheck($orderId = null)
{
    $data = array();
    $order = \App\Models\VirtualCardOrder::with(['cardMethod'])->find($orderId);
    if ($order) {
        $currencyCode = $order->currency;
        $data['currencyCode'] = $order->currency;
        $fundCurrencies = optional($order->cardMethod)->add_fund_parameter;
        foreach ($fundCurrencies as $key => $fundCurrency) {
            if ($currencyCode == $key) {
                foreach ($fundCurrency as $key => $item) {
                    $data[$key] = $item->field_value;
                }
                $data['status'] = 'success';
                return $data;
            }
        }
    }
    $data['status'] = 'fail';
    return $data;
}

if (!function_exists('generateRandomAlphaNumeric')) {
    function generateRandomAlphaNumeric($prefix = null, $length = 10)
    {
        $randomAlpha = Str::upper(Str::random($length));
        $numericChars = '0123456789';
        $randomAlphaNumeric = Str::replaceArray('X', str_split(Str::random(2, $numericChars)), $randomAlpha);
        return $prefix . $randomAlphaNumeric;
    }
}

if (!function_exists('getReloadlyAmount')) {
    function getReloadlyAmount($service, $amountId)
    {
        foreach ($service->extra_response as $res) {
            if ($service->service == 'AIRTIME') {
                $billMethod = $service->method;
                $convertRate = $billMethod->convert_rate->{$service->currency} ?? 1;
                return $convertRate * $amountId;
            } else {
                if ($res['id'] == $amountId) {
                    return $res['amount'];
                }
            }
        }
    }
}


if (!function_exists('wordTruncate')) {
    function wordTruncate($string, $offset = 0, $length = null)
    {
        $words = explode(" ", $string);
        isset($length) ? array_splice($words, $offset, $length) : array_splice($words, $offset);
        return implode(" ", $words);
    }
}

function shopImage($storeLink)
{
    return Cache::remember("shop_image_{$storeLink}", now()->addMinutes(30), function () use ($storeLink) {
        $store = \App\Models\Store::where('link', $storeLink)->first();
        return $store ? getFile($store->driver, $store->image) : null;
    });
}

function currencyCode($storeLink)
{
    return Cache::remember("currency_code_{$storeLink}", now()->addMinutes(30), function () use ($storeLink) {
        $store = \App\Models\Store::with('user.storeCurrency')->where('link', $storeLink)->first();
        return optional($store?->user?->storeCurrency)->code ?? '@';
    });
}

function currencySymbol($storeLink)
{
    return Cache::remember("currency_symbol_{$storeLink}", now()->addMinutes(30), function () use ($storeLink) {
        $store = \App\Models\Store::with('user.storeCurrency')->where('link', $storeLink)->first();
        return optional($store?->user?->storeCurrency)->symbol ?? '@';
    });
}

function isStageDone($position, $stage)
{
    if ($stage == null) {
        if ($position == 0) {
            return 'completed';
        }
    }
    if ($position <= $stage && $stage < 5) {
        return 'completed';
    }
    return 0;
}


if (!function_exists('getFirebaseFileName')) {
    function getFirebaseFileName()
    {
        return 'firebase-service.json';
    }
}


if (!function_exists('generateOrderedId')) {
    function generateOrderedId(string $table, string $column, string $prefix)
    {
        return DB::transaction(function () use ($table, $column, $prefix) {
            // Fetch the last entry and lock the row for update using raw SQL
            $lastTrxId = DB::selectOne(
                "SELECT {$column} FROM {$table} ORDER BY id DESC LIMIT 1 FOR UPDATE"
            );
            $lastNumber = 1;
            if ($lastTrxId) {
                $lastNumber = (int)substr($lastTrxId->$column, strlen($prefix));
                $lastNumber++;
            }
            return $prefix . str_pad($lastNumber, 10, '0', STR_PAD_LEFT);
        });
    }
}


if (!function_exists('userLayout')) {
    function userLayout(): string
    {
        if (!auth()->check()) {
            return 'user.layouts.payment';
        }

        $layout = session('user_layout', config('basic.default_user_layout'));
        session(['user_layout' => $layout]);
        return $layout === 'vertical' ? 'user.layouts.vertical' : 'user.layouts.horizontal';
    }
}

if (!function_exists('containerClass')) {
    function containerClass(): string
    {
        return session('user_layout', config('basic.default_user_layout')) === 'horizontal'
            ? 'container-xxl'
            : 'container-fluid';
    }
}


function getUserPermissions($user): array
{
    if (is_null($user->role_id)) {
        return config('permissionList');
    }
    if ($user->status != 1) {
        return [];
    }

    return cache()->remember("role_permissions_{$user->role_id}", now()->addMinutes(60), function () use ($user) {
        return $user->activeRole?->permission ?? [];
    });
}

function checkPermission($role): bool
{
    $loginUser = auth()->user();
    $permissions = getUserPermissions($loginUser);

    return in_array($role, $permissions);
}

function checkPermissionByKey($key): bool
{
    $loginUser = auth()->user();
    if ($loginUser->status != 1) return false;
    if (is_null($loginUser->role_id)) return true;

    $roleId = config('permissionList')[$key] ?? null;
    if (!$roleId) return true;

    $permissions = getUserPermissions($loginUser);

    return in_array($roleId, $permissions);
}

if (!function_exists('renderAmountTypeIcon')) {
    function renderAmountTypeIcon($senderId, $authId = null): string
    {
        $authId = $authId ?? auth()->id();

        $isSender = $senderId == $authId;
        $iconClass = $isSender ? 'bi-arrow-up-right' : 'bi-arrow-down-right';
        $colorClass = $isSender ? 'icon-soft-danger' : 'icon-soft-success';
        $label = $isSender ? trans('Sent') : trans('Received');

        return <<<HTML
            <div class="d-flex align-items-center">
                <span class="icon {$colorClass} icon-sm icon-circle" title="{$label}">
                    <i class="bi {$iconClass}"></i>
                </span>
            </div>
        HTML;
    }
}


if (!function_exists('formatNumber')) {
    function formatNumber($number)
    {
        if ($number >= 1000000) {
            return floor($number / 1000000) == $number / 1000000
                ? number_format($number / 1000000, 0) . 'M'
                : number_format($number / 1000000, 1) . 'M';
        } elseif ($number >= 1000) {
            return floor($number / 1000) == $number / 1000
                ? number_format($number / 1000, 0) . 'k'
                : number_format($number / 1000, 1) . 'k';
        }
        return $number;
    }
}


if (!function_exists('str_headline')) {
    function str_headline($string = '-'): string
    {
        return Str::headline($string);
    }
}


if (!function_exists('throwIfInvalid')) {
    function throwIfInvalid(bool $condition, string $message): void
    {
        if ($condition) {
            throw new \Exception($message);
        }
    }
}


if (!function_exists('userRoute')) {
    function userRoute(string $name, $params = [], $absolute = true): string
    {
        $user = auth()->user();
        $type = $user?->type;

        $prefixes = match ($type) {
            'agent' => ['agent', 'user'],
            'merchant' => ['merchant', 'user'],
            default => ['user'],
        };

        foreach ($prefixes as $prefix) {
            $routeName = "{$prefix}.{$name}";
            if (Route::has($routeName)) {
                return route($routeName, $params, $absolute);
            }
        }
        return route("user.{$name}", $params, $absolute);
    }
}

if (!function_exists('resolveUserView')) {
    function resolveUserView($view)
    {
        static $cache = [];

        $user = auth()->user();
        $type = $user?->type ?? 'user';
        $key = "{$type}.{$view}";

        if (!isset($cache[$key])) {
            $customView = "{$type}::partials.{$view}";
            $cache[$key] = View::exists($customView) ? $customView : "user.partials.{$view}";
        }

        return $cache[$key];
    }
}

if (!function_exists('module_exists')) {
    function module_exists(string $moduleName): bool
    {
        if (!class_exists(\Nwidart\Modules\Facades\Module::class)) {
            return false;
        }
        try {
            return \Module::has($moduleName) && \Module::find($moduleName)?->isEnabled();
        } catch (\Throwable $e) {
            return false;
        }
    }
}

function any_modules_exist(array $modules = []): bool
{
    $modules = $modules ?: File::directories(base_path('Modules'));
    return collect($modules)->contains(function ($modulePath) {
        return file_exists("{$modulePath}/module.json");
    });
}


if (!function_exists('getModuleStatuses')) {
    function getModuleStatuses(): array
    {
        $path = base_path('modules_statuses.json');
        if (!file_exists($path)) {
            return [];
        }
        return json_decode(file_get_contents($path), true) ?? [];
    }
}

if (!function_exists('getAllowedUserTypes')) {
    function getAllowedUserTypes(): array
    {
        $statuses = getModuleStatuses();
        $types = ['user'];
        if (!empty($statuses['Agent'])) {
            $types[] = 'agent';
        }
        if (!empty($statuses['Merchant'])) {
            $types[] = 'merchant';
        }
        return $types;
    }
}


if (!function_exists('validateUserType')) {
    function validateUserType(?string $type = null): ?string
    {
        $type = $type ?? request('type');
        $allowedTypes = getAllowedUserTypes();

        if ($type && !in_array($type, $allowedTypes)) {
            abort(404);
        }

        return $type;
    }
}

if (!function_exists('renderUserTypeBadge')) {
    function renderUserTypeBadge(?string $type): string
    {
        $type = strtolower(trim($type ?? ''));
        $map = [
            'user' => '',
            'agent' => '<span class="badge bg-soft-primary text-primary ms-1">Agent</span>',
            'merchant' => '<span class="badge bg-soft-info text-info ms-1">Merchant</span>',
        ];
        return $map[$type] ?? '';
    }
}

if (!function_exists('filterUsersByAllowedType')) {
    function filterUsersByAllowedType($query, ?string $type = null)
    {
        $allowedTypes = getAllowedUserTypes();
        $query->whereIn('type', $allowedTypes);
        if ($type && in_array($type, $allowedTypes)) {
            $query->where('type', $type);
        }
        return $query;
    }
}

if (!function_exists('getConvertAmount')) {
    function getConvertAmount($deposit)
    {
        $currency = \App\Models\Currency::where('code', $deposit->receive_currency_code)->first();
        if (!$currency) {
            $currency = \App\Models\Currency::where('code', \basicControl()->base_currency)->first();
        }

        $receiveAmount = $deposit->payable_amount_in_base_currency * $currency->exchange_rate;

        return [
            'amount' => $receiveAmount,
            'currency_id' => $currency->id,
            'currency_code' => $currency->code,
        ];
    }
}





