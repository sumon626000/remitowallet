<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Currency;
use App\Models\FxAsset;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\AllStocksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PlayGameController extends Controller
{
    private const ROYAL_MAX_STEPS = 10;
    private const ROYAL_BASE_MULT = 1.15;
    private const ROYAL_SESSION = 'play_game.royal_round';

    public function __construct()
    {
        $this->middleware(['auth']);
    }

    protected function gameSettings(): object
    {
        return fxTradeSettings();
    }

    protected function fxWinChance(): float
    {
        return max(0, min(1, ((float) $this->gameSettings()->fx_win_percent) / 100));
    }

    protected function fxPayoutMult(): float
    {
        return max(1, (float) $this->gameSettings()->fx_payout_mult);
    }

    protected function fxMinBet(): float
    {
        return max(0.01, (float) $this->gameSettings()->fx_min_bet);
    }

    protected function fxMaxBet(): float
    {
        return max($this->fxMinBet(), (float) $this->gameSettings()->fx_max_bet);
    }

    protected function royalMinBet(): float
    {
        return max(0.01, (float) $this->gameSettings()->royal_min_bet);
    }

    protected function royalMaxBet(): float
    {
        return max($this->royalMinBet(), (float) $this->gameSettings()->royal_max_bet);
    }

    protected function royalTrapChance(): float
    {
        $winChance = max(0, min(1, ((float) $this->gameSettings()->royal_win_percent) / 100));

        return 1 - $winChance;
    }

    protected function fxPresetStakes(): array
    {
        $min = $this->fxMinBet();
        $max = $this->fxMaxBet();
        $candidates = [$min, 50, 100, 250, 500, 1000, $max];
        $presets = [];
        foreach ($candidates as $value) {
            $value = round((float) $value, 2);
            if ($value >= $min && $value <= $max && !in_array($value, $presets, true)) {
                $presets[] = $value;
            }
        }
        sort($presets);

        return $presets ?: [$min];
    }

    protected function botMinBet(): float
    {
        return max(0.01, (float) $this->gameSettings()->bot_min_bet);
    }

    protected function botMaxBet(): float
    {
        return max($this->botMinBet(), (float) $this->gameSettings()->bot_max_bet);
    }

    protected function botWinChance(): float
    {
        return max(0, min(1, ((float) $this->gameSettings()->bot_win_percent) / 100));
    }

    protected function botPayoutMult(): float
    {
        return max(1, (float) $this->gameSettings()->bot_payout_mult);
    }

    protected function isGameEnabled(string $slug): bool
    {
        $s = $this->gameSettings();

        return match ($slug) {
            'fx-casino' => (int) $s->fx_enabled === 1,
            'royal-step-casino' => (int) $s->royal_enabled === 1,
            'all-stocks' => (int) ($s->stocks_enabled ?? 1) === 1,
            'bot-game' => (int) $s->bot_enabled === 1,
            default => false,
        };
    }

    protected function playWallet(): Wallet
    {
        $userId = Auth::id();
        $usdId = Currency::where('code', 'USD')->value('id')
            ?? Currency::where('code', basicControl()->base_currency)->value('id')
            ?? 2;

        $wallet = Wallet::with('currency')
            ->where('user_id', $userId)
            ->where('currency_id', $usdId)
            ->first();

        if ($wallet) {
            return $wallet;
        }

        return Wallet::create([
            'user_id' => $userId,
            'currency_id' => $usdId,
            'balance' => 0,
        ])->load('currency');
    }

    protected function gameData(): array
    {
        $wallet = $this->playWallet();
        $user = Auth::user();
        $settings = $this->gameSettings();

        return [
            'user' => $user,
            'wallet' => $wallet,
            'balance' => (float) $wallet->balance,
            'currency' => $wallet->currency->symbol ?? (basicControl()->currency_symbol ?? '$'),
            'currencyCode' => $wallet->currency->code ?? (basicControl()->base_currency ?? 'USD'),
            'gameSettings' => $settings,
            'fxMinBet' => $this->fxMinBet(),
            'fxMaxBet' => $this->fxMaxBet(),
            'fxPresetStakes' => $this->fxPresetStakes(),
            'royalMinBet' => $this->royalMinBet(),
            'royalMaxBet' => $this->royalMaxBet(),
            'botMinBet' => $this->botMinBet(),
            'botMaxBet' => $this->botMaxBet(),
            'games' => collect([
                [
                    'slug' => 'fx-casino',
                    'name' => 'Trade Binary',
                    'badge' => 'NEW',
                    'icon' => 'bi-graph-up-arrow',
                    'tile' => 'fx',
                    'glow' => 'rgba(16,185,129,0.55)',
                    'enabled' => (int) $settings->fx_enabled === 1,
                ],
                [
                    'slug' => 'royal-step-casino',
                    'name' => 'Royal Steps',
                    'badge' => 'HOT',
                    'icon' => 'bi-trophy-fill',
                    'tile' => 'royal',
                    'glow' => 'rgba(251,191,36,0.6)',
                    'enabled' => (int) $settings->royal_enabled === 1,
                ],
                [
                    'slug' => 'all-stocks',
                    'name' => 'All Stocks',
                    'badge' => 'NEW',
                    'icon' => 'bi-bar-chart-line',
                    'tile' => 'stocks',
                    'glow' => 'rgba(55,125,255,0.55)',
                    'enabled' => (int) ($settings->stocks_enabled ?? 1) === 1,
                ],
                [
                    'slug' => 'bot-game',
                    'name' => 'Bot Game',
                    'badge' => 'BOT',
                    'icon' => 'bi-robot',
                    'tile' => 'bot',
                    'glow' => 'rgba(55,125,255,0.55)',
                    'enabled' => (int) $settings->bot_enabled === 1,
                ],
            ])->filter(fn ($g) => $g['enabled'])->values()->all(),
        ];
    }

    public function index()
    {
        return view('user.play_game.index', $this->gameData());
    }

    public function play(string $slug)
    {
        $views = [
            'fx-casino' => 'user.play_game.fx-casino',
            'royal-step-casino' => 'user.play_game.royal-step',
            'all-stocks' => 'user.play_game.all-stocks',
            'bot-game' => 'user.play_game.bot-game',
        ];

        abort_unless(isset($views[$slug]), 404);
        abort_unless($this->isGameEnabled($slug), 404);

        $data = $this->gameData();
        $allGames = [
            'fx-casino' => ['slug' => 'fx-casino', 'name' => 'Trade Binary', 'tile' => 'fx'],
            'royal-step-casino' => ['slug' => 'royal-step-casino', 'name' => 'Royal Steps', 'tile' => 'royal'],
            'all-stocks' => ['slug' => 'all-stocks', 'name' => 'All Stocks', 'tile' => 'stocks'],
            'bot-game' => ['slug' => 'bot-game', 'name' => 'Bot Game', 'tile' => 'bot'],
        ];
        $data['game'] = $allGames[$slug];
        $data['royalRound'] = $slug === 'royal-step-casino' ? session(self::ROYAL_SESSION) : null;
        if ($slug === 'all-stocks') {
            $service = app(AllStocksService::class);
            $settings = $data['gameSettings'];
            $data['stockAssets'] = FxAsset::where('active', 1)->orderBy('sort_order')->orderBy('symbol')->get();
            $data['stockDurations'] = $service->enabledDurations($settings);
            $data['stockMin'] = (float) $settings->stocks_min_bet;
            $data['stockMax'] = (float) $settings->stocks_max_bet;
            $data['stockPayout'] = (float) $settings->stocks_payout_pct;
            $data['stockConfig'] = [
                'assets' => $data['stockAssets']->map(fn ($asset) => [
                    'symbol' => $asset->symbol,
                    'name' => $asset->name,
                    'decimals' => (int) $asset->decimals,
                ])->values()->all(),
                'durations' => $data['stockDurations'],
                'min' => $data['stockMin'],
                'max' => $data['stockMax'],
                'payout' => $data['stockPayout'],
                'currency' => $data['currency'],
                'stateUrl' => route('user.play.game.stocks.state'),
                'openUrl' => route('user.play.game.stocks.open'),
            ];
        }

        return view($views[$slug], $data);
    }

    public function fxBet(Request $request): JsonResponse
    {
        if (!$this->isGameEnabled('fx-casino')) {
            return $this->fail('Trade Binary is currently off');
        }

        $stake = round((float) $request->input('stake'), 2);
        $minBet = $this->fxMinBet();
        $maxBet = $this->fxMaxBet();

        if ($stake < $minBet || $stake > $maxBet) {
            return $this->fail('Bet must be ' . $minBet . '-' . $maxBet);
        }

        try {
            $result = DB::transaction(function () use ($stake) {
                $wallet = $this->playWallet();
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

                if ($stake > (float) $wallet->balance) {
                    throw new \RuntimeException('Insufficient wallet balance');
                }

                $newBalance = updateWallet($wallet->user_id, $wallet->currency_id, $stake, 0);
                $this->recordTrx($wallet, $stake, '-', 'Trade Binary bet');

                $won = (mt_rand() / mt_getrandmax()) < $this->fxWinChance();
                $payout = $won ? round($stake * $this->fxPayoutMult(), 2) : 0;

                if ($won && $payout > 0) {
                    $newBalance = updateWallet($wallet->user_id, $wallet->currency_id, $payout, 1);
                    $this->recordTrx($wallet, $payout, '+', 'Trade Binary win');
                }

                return [
                    'won' => $won,
                    'stake' => $stake,
                    'payout' => $payout,
                    'profit' => $won ? round($payout - $stake, 2) : -$stake,
                    'balance' => (float) $newBalance,
                ];
            });
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage() ?: 'Unable to place bet');
        }

        return response()->json(['ok' => true] + $result);
    }

    public function royalStart(Request $request): JsonResponse
    {
        if (!$this->isGameEnabled('royal-step-casino')) {
            return $this->fail('Royal Steps is currently off');
        }

        if (session()->has(self::ROYAL_SESSION)) {
            return $this->fail('Finish or collect the current round first');
        }

        $bet = round((float) $request->input('bet'), 2);
        $minBet = $this->royalMinBet();
        $maxBet = $this->royalMaxBet();
        if ($bet < $minBet || $bet > $maxBet) {
            return $this->fail('Bet must be ' . $minBet . '-' . $maxBet);
        }

        try {
            $result = DB::transaction(function () use ($bet) {
                $wallet = $this->playWallet();
                $newBalance = updateWallet($wallet->user_id, $wallet->currency_id, $bet, 0);
                $this->recordTrx($wallet, $bet, '-', 'Royal Steps bet');

                $round = ['bet' => $bet, 'step' => 0];
                session([self::ROYAL_SESSION => $round]);

                return [
                    'balance' => (float) $newBalance,
                    'round' => $round,
                ];
            });
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage() ?: 'Unable to start game');
        }

        return response()->json(['ok' => true] + $result);
    }

    public function royalClimb(): JsonResponse
    {
        if (!$this->isGameEnabled('royal-step-casino')) {
            return $this->fail('Royal Steps is currently off');
        }

        $round = session(self::ROYAL_SESSION);
        if (!$round) {
            return $this->fail('No active round');
        }

        $step = (int) $round['step'];
        if ($step >= self::ROYAL_MAX_STEPS) {
            return $this->fail('Already at the top');
        }

        if ((mt_rand() / mt_getrandmax()) < $this->royalTrapChance()) {
            session()->forget(self::ROYAL_SESSION);
            return response()->json([
                'ok' => true,
                'busted' => true,
                'step' => $step,
                'balance' => (float) $this->playWallet()->balance,
            ]);
        }

        $round['step'] = $step + 1;
        $payload = [
            'ok' => true,
            'busted' => false,
            'step' => $round['step'],
            'balance' => (float) $this->playWallet()->balance,
        ];

        if ($round['step'] >= self::ROYAL_MAX_STEPS) {
            $payload = array_merge($payload, $this->royalPayout($round));
        } else {
            session([self::ROYAL_SESSION => $round]);
        }

        return response()->json($payload);
    }

    public function royalCash(): JsonResponse
    {
        if (!$this->isGameEnabled('royal-step-casino')) {
            return $this->fail('Royal Steps is currently off');
        }

        $round = session(self::ROYAL_SESSION);
        if (!$round || (int) $round['step'] < 1) {
            return $this->fail('Nothing to collect yet');
        }

        return response()->json(['ok' => true] + $this->royalPayout($round));
    }

    public function botBet(Request $request): JsonResponse
    {
        if (!$this->isGameEnabled('bot-game')) {
            return $this->fail('Bot Game is currently off');
        }

        $stake = round((float) $request->input('stake'), 2);
        $side = strtolower((string) $request->input('side', ''));
        $minBet = $this->botMinBet();
        $maxBet = $this->botMaxBet();

        if (!in_array($side, ['up', 'down'], true)) {
            return $this->fail('Choose Up or Down');
        }
        if ($stake < $minBet || $stake > $maxBet) {
            return $this->fail('Bet must be ' . $minBet . '-' . $maxBet);
        }

        try {
            $result = DB::transaction(function () use ($stake, $side) {
                $wallet = $this->playWallet();
                $wallet = Wallet::where('id', $wallet->id)->lockForUpdate()->first();

                if ($stake > (float) $wallet->balance) {
                    throw new \RuntimeException('Insufficient wallet balance');
                }

                $newBalance = updateWallet($wallet->user_id, $wallet->currency_id, $stake, 0);
                $this->recordTrx($wallet, $stake, '-', 'Bot Game bet');

                $botWins = (mt_rand() / mt_getrandmax()) < $this->botWinChance();
                $playerWon = !$botWins;
                // Market result is set so that if player won, market matches their side
                $market = $playerWon ? $side : ($side === 'up' ? 'down' : 'up');
                $payout = $playerWon ? round($stake * $this->botPayoutMult(), 2) : 0;

                if ($playerWon && $payout > 0) {
                    $newBalance = updateWallet($wallet->user_id, $wallet->currency_id, $payout, 1);
                    $this->recordTrx($wallet, $payout, '+', 'Bot Game win');
                }

                return [
                    'won' => $playerWon,
                    'bot_won' => $botWins,
                    'side' => $side,
                    'market' => $market,
                    'stake' => $stake,
                    'payout' => $payout,
                    'profit' => $playerWon ? round($payout - $stake, 2) : -$stake,
                    'balance' => (float) $newBalance,
                ];
            });
        } catch (\Throwable $e) {
            return $this->fail($e->getMessage() ?: 'Unable to place bet');
        }

        return response()->json(['ok' => true] + $result);
    }

    protected function royalPayout(array $round): array
    {
        $step = (int) $round['step'];
        $mult = $this->royalMult($step);
        $prize = round(((float) $round['bet']) * $mult, 2);
        $wallet = $this->playWallet();
        $newBalance = updateWallet($wallet->user_id, $wallet->currency_id, $prize, 1);
        $this->recordTrx($wallet, $prize, '+', 'Royal Steps win');
        session()->forget(self::ROYAL_SESSION);

        return [
            'busted' => false,
            'step' => $step,
            'won' => [
                'prize' => $prize,
                'mult' => $mult,
            ],
            'balance' => (float) $newBalance,
        ];
    }

    protected function royalMult(int $step): float
    {
        if ($step < 1) {
            return 1.0;
        }

        return round(pow(self::ROYAL_BASE_MULT, $step), 2);
    }

    protected function recordTrx(Wallet $wallet, float $amount, string $type, string $remarks): void
    {
        $transaction = new Transaction();
        $transaction->user_id = $wallet->user_id;
        $transaction->currency_id = $wallet->currency_id;
        $transaction->amount = $amount;
        $transaction->charge = 0;
        $transaction->trx_type = $type;
        $transaction->remarks = $remarks;
        $transaction->save();
    }

    protected function fail(string $message): JsonResponse
    {
        return response()->json([
            'ok' => false,
            'message' => $message,
            'balance' => (float) $this->playWallet()->balance,
        ], 422);
    }
}
