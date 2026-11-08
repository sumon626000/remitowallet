<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Services\AllStocksService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class AllStocksController extends Controller
{
    public function __construct(private AllStocksService $stocks)
    {
        $this->middleware(['auth']);
    }

    public function state(Request $request): JsonResponse
    {
        if ((int) fxTradeSettings()->stocks_enabled !== 1) {
            return $this->fail('Forex Trade is currently off', 403);
        }

        $userId = (int) Auth::id();
        $key = 'stocks-state:'.$userId;
        if (RateLimiter::tooManyAttempts($key, 120)) {
            return $this->fail('Too many requests. Wait a moment.');
        }
        RateLimiter::hit($key, 60);

        $symbol = $request->query('asset');
        $symbol = is_string($symbol) ? trim($symbol) : null;
        if ($symbol !== null && !preg_match('/^[A-Za-z0-9\/\.\-]{2,20}$/', $symbol)) {
            return $this->fail('Unknown market');
        }

        $data = $this->stocks->snapshot($userId, $symbol);

        return response()->json(array_merge(['ok' => true], $data));
    }

    public function open(Request $request): JsonResponse
    {
        if ((int) fxTradeSettings()->stocks_enabled !== 1) {
            return $this->fail('Forex Trade is currently off', 403);
        }

        $userId = (int) Auth::id();
        $key = 'stocks-open:'.$userId;
        if (RateLimiter::tooManyAttempts($key, 30)) {
            return $this->fail('Too many trades. Wait a moment.');
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'asset' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\/\.\-]+$/'],
            'direction' => ['required', 'in:high,low'],
            'stake' => ['required', 'numeric'],
            'duration' => ['required', 'integer'],
        ]);

        try {
            $result = $this->stocks->open(
                $userId,
                $data['asset'],
                $data['direction'],
                (float) $data['stake'],
                (int) $data['duration']
            );
        } catch (\RuntimeException $e) {
            return $this->fail($e->getMessage());
        }

        return response()->json([
            'ok' => true,
            'balance' => $result['balance'],
            'trade' => $result['trade'],
        ]);
    }

    private function fail(string $message, int $status = 422): JsonResponse
    {
        $balance = 0.0;
        try {
            $balance = (float) $this->stocks->wallet((int) Auth::id())->balance;
        } catch (\Throwable $e) {
            $balance = 0.0;
        }

        return response()->json([
            'ok' => false,
            'message' => $message,
            'balance' => $balance,
        ], $status);
    }
}
