@extends('admin.layouts.app')
@section('page_title', __('FX Trade Settings'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{ route('admin.dashboard') }}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('FX Trade Settings')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang('FX Trade Settings')</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-3">
                @include('admin.control_panel.components.sidebar', ['settings' => config('generalsettings.settings'), 'suffix' => 'Settings'])
            </div>
            <div class="col-lg-9 seo-setting">
                <form action="{{ route('admin.fx.trade.settings') }}" method="post">
                    @csrf
                    <div class="d-grid gap-3 gap-lg-5">

                        {{-- FX Casino --}}
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h2 class="card-title h4 mb-0">@lang('Trade Binary')</h2>
                                <label class="form-check form-switch mb-0" for="fx_enabled">
                                    <input type="hidden" name="fx_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" name="fx_enabled" id="fx_enabled"
                                           value="1" {{ (int)$settings->fx_enabled === 1 ? 'checked' : '' }}>
                                    <span class="ms-2">@lang('On / Off')</span>
                                </label>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="fx_min_bet">@lang('Minimum Bet')</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control @error('fx_min_bet') is-invalid @enderror"
                                               name="fx_min_bet" id="fx_min_bet" value="{{ old('fx_min_bet', $settings->fx_min_bet) }}">
                                        @error('fx_min_bet')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="fx_max_bet">@lang('Maximum Bet')</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control @error('fx_max_bet') is-invalid @enderror"
                                               name="fx_max_bet" id="fx_max_bet" value="{{ old('fx_max_bet', $settings->fx_max_bet) }}">
                                        @error('fx_max_bet')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label" for="fx_win_percent">@lang('Player Win %')</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('fx_win_percent') is-invalid @enderror"
                                                   name="fx_win_percent" id="fx_win_percent" value="{{ old('fx_win_percent', $settings->fx_win_percent) }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @error('fx_win_percent')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="fx_payout_mult">@lang('Payout Multiplier')</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="1" max="10" class="form-control @error('fx_payout_mult') is-invalid @enderror"
                                                   name="fx_payout_mult" id="fx_payout_mult" value="{{ old('fx_payout_mult', $settings->fx_payout_mult) }}">
                                            <span class="input-group-text">×</span>
                                        </div>
                                        @error('fx_payout_mult')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Royal Steps --}}
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h2 class="card-title h4 mb-0">@lang('Royal Steps')</h2>
                                <label class="form-check form-switch mb-0" for="royal_enabled">
                                    <input type="hidden" name="royal_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" name="royal_enabled" id="royal_enabled"
                                           value="1" {{ (int)$settings->royal_enabled === 1 ? 'checked' : '' }}>
                                    <span class="ms-2">@lang('On / Off')</span>
                                </label>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="royal_min_bet">@lang('Minimum Bet')</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control @error('royal_min_bet') is-invalid @enderror"
                                               name="royal_min_bet" id="royal_min_bet" value="{{ old('royal_min_bet', $settings->royal_min_bet) }}">
                                        @error('royal_min_bet')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="royal_max_bet">@lang('Maximum Bet')</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control @error('royal_max_bet') is-invalid @enderror"
                                               name="royal_max_bet" id="royal_max_bet" value="{{ old('royal_max_bet', $settings->royal_max_bet) }}">
                                        @error('royal_max_bet')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label" for="royal_win_percent">@lang('Player Win %')</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('royal_win_percent') is-invalid @enderror"
                                                   name="royal_win_percent" id="royal_win_percent" value="{{ old('royal_win_percent', $settings->royal_win_percent) }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="text-muted">@lang('Safe climb chance each step.')</small>
                                        @error('royal_win_percent')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Bot Game --}}
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h2 class="card-title h4 mb-0">@lang('Bot Game')</h2>
                                <label class="form-check form-switch mb-0" for="bot_enabled">
                                    <input type="hidden" name="bot_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" name="bot_enabled" id="bot_enabled"
                                           value="1" {{ (int)$settings->bot_enabled === 1 ? 'checked' : '' }}>
                                    <span class="ms-2">@lang('On / Off')</span>
                                </label>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-soft-warning mb-3">
                                    @lang('Bot Win % = how often the bot beats the player. Example: 60 means bot wins about 60% of rounds.')
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label" for="bot_min_bet">@lang('Minimum Bet')</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control @error('bot_min_bet') is-invalid @enderror"
                                               name="bot_min_bet" id="bot_min_bet" value="{{ old('bot_min_bet', $settings->bot_min_bet) }}">
                                        @error('bot_min_bet')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="bot_max_bet">@lang('Maximum Bet')</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control @error('bot_max_bet') is-invalid @enderror"
                                               name="bot_max_bet" id="bot_max_bet" value="{{ old('bot_max_bet', $settings->bot_max_bet) }}">
                                        @error('bot_max_bet')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="form-label" for="bot_win_percent">@lang('Bot Win %')</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control @error('bot_win_percent') is-invalid @enderror"
                                                   name="bot_win_percent" id="bot_win_percent" value="{{ old('bot_win_percent', $settings->bot_win_percent) }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        @error('bot_win_percent')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label" for="bot_payout_mult">@lang('Player Payout Multiplier')</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="1" max="10" class="form-control @error('bot_payout_mult') is-invalid @enderror"
                                                   name="bot_payout_mult" id="bot_payout_mult" value="{{ old('bot_payout_mult', $settings->bot_payout_mult) }}">
                                            <span class="input-group-text">×</span>
                                        </div>
                                        <small class="text-muted">@lang('When player beats the bot.')</small>
                                        @error('bot_payout_mult')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- All Stocks --}}
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h2 class="card-title h4 mb-0">@lang('All Stocks')</h2>
                                <label class="form-check form-switch mb-0" for="stocks_enabled">
                                    <input type="hidden" name="stocks_enabled" value="0">
                                    <input class="form-check-input" type="checkbox" name="stocks_enabled" id="stocks_enabled"
                                           value="1" {{ (int)($settings->stocks_enabled ?? 1) === 1 ? 'checked' : '' }}>
                                    <span class="ms-2">@lang('On / Off')</span>
                                </label>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">
                                    @lang('Win pays the stake back plus this percent. 80 on a 10 stake returns 18. Bot Win % is how often the bot beats the player.')
                                    @if($feedSymbol)
                                        @lang('Feed'): {{ $feedSymbol }} {{ $feedPrice ?: __('unavailable') }}
                                    @endif
                                </p>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="stocks_min_bet">@lang('Minimum Bet')</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control" name="stocks_min_bet" id="stocks_min_bet" value="{{ old('stocks_min_bet', $settings->stocks_min_bet ?? 10) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="stocks_max_bet">@lang('Maximum Bet')</label>
                                        <input type="number" step="0.01" min="0.01" class="form-control" name="stocks_max_bet" id="stocks_max_bet" value="{{ old('stocks_max_bet', $settings->stocks_max_bet ?? 1000) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="stocks_payout_pct">@lang('Payout %')</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="1" max="500" class="form-control" name="stocks_payout_pct" id="stocks_payout_pct" value="{{ old('stocks_payout_pct', $settings->stocks_payout_pct ?? 80) }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row mb-3">
                                    <div class="col-md-4">
                                        <label class="form-label" for="stocks_max_open">@lang('Max open trades')</label>
                                        <input type="number" min="1" max="50" class="form-control" name="stocks_max_open" id="stocks_max_open" value="{{ old('stocks_max_open', $settings->stocks_max_open ?? 5) }}">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label" for="stocks_bot_win_percent">@lang('Bot Win %')</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0" max="100" class="form-control" name="stocks_bot_win_percent" id="stocks_bot_win_percent" value="{{ old('stocks_bot_win_percent', $settings->stocks_bot_win_percent ?? 55) }}">
                                            <span class="input-group-text">%</span>
                                        </div>
                                        <small class="text-muted">@lang('60 means the bot wins about 60% of trades.')</small>
                                    </div>
                                </div>
                                <label class="form-label">@lang('Durations')</label>
                                <div class="d-flex flex-column gap-2 mb-3">
                                    @foreach(\App\Services\AllStocksService::durationCatalog() as $seconds => $label)
                                        <div class="d-flex align-items-center justify-content-between border rounded px-3 py-2">
                                            <input type="hidden" name="stocks_durations[{{ $seconds }}]" value="1">
                                            <span>{{ $label }}</span>
                                            <label class="form-check mb-0">
                                                <input class="form-check-input" type="checkbox" name="stocks_duration_remove[{{ $seconds }}]" value="1">
                                                <span class="ms-1">@lang('Remove')</span>
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                                <label class="form-label" for="new_duration_value">@lang('Add duration')</label>
                                <div class="row g-2">
                                    <div class="col-md-4">
                                        <input type="number" min="1" class="form-control @error('new_duration_value') is-invalid @enderror" name="new_duration_value" id="new_duration_value" value="{{ old('new_duration_value') }}" placeholder="15">
                                        @error('new_duration_value')<span class="invalid-feedback d-block">{{ $message }}</span>@enderror
                                    </div>
                                    <div class="col-md-4">
                                        <select class="form-select" name="new_duration_unit" id="new_duration_unit">
                                            <option value="seconds" @selected(old('new_duration_unit') === 'seconds')>@lang('Seconds')</option>
                                            <option value="minutes" @selected(old('new_duration_unit', 'minutes') === 'minutes')>@lang('Minutes')</option>
                                            <option value="hours" @selected(old('new_duration_unit') === 'hours')>@lang('Hours')</option>
                                        </select>
                                    </div>
                                </div>
                                <small class="text-muted">@lang('Tick Remove to delete one, or type a number and save to add one. Minimum 5 seconds, maximum 24 hours.')</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">@lang('Save changes')</button>
                        </div>
                    </div>
                </form>

                <form action="{{ route('admin.fx.trade.assets') }}" method="post" class="mt-4">
                    @csrf
                    <div class="card">
                        <div class="card-header"><h2 class="card-title h4 mb-0">@lang('All Stocks markets')</h2></div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm align-middle">
                                    <thead><tr><th>@lang('Symbol')</th><th>@lang('Name')</th><th>@lang('On')</th></tr></thead>
                                    <tbody>
                                    @foreach($stockAssets as $asset)
                                        <tr>
                                            <td>{{ $asset->symbol }}</td>
                                            <td>
                                                <input type="text" class="form-control form-control-sm" name="assets[{{ $asset->id }}][name]" value="{{ $asset->name }}" maxlength="80">
                                            </td>
                                            <td>
                                                <input type="hidden" name="assets[{{ $asset->id }}][active]" value="0">
                                                <input type="checkbox" name="assets[{{ $asset->id }}][active]" value="1" {{ $asset->active ? 'checked' : '' }}>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="row g-2">
                                <div class="col-md-3"><input class="form-control form-control-sm" name="new_symbol" placeholder="EUR/USD"></div>
                                <div class="col-md-3"><input class="form-control form-control-sm" name="new_name" placeholder="@lang('Name')"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" name="new_yahoo" placeholder="EURUSD=X"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" name="new_twelve" placeholder="EUR/USD"></div>
                                <div class="col-md-2"><input class="form-control form-control-sm" type="number" name="new_decimals" min="0" max="8" placeholder="5"></div>
                            </div>
                        </div>
                        <div class="card-footer text-end">
                            <button class="btn btn-primary btn-sm" type="submit">@lang('Save markets')</button>
                        </div>
                    </div>
                </form>

                <div class="card mt-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h2 class="card-title h4 mb-0">@lang('All Stocks report')</h2>
                        <form method="get">
                            <input class="form-control form-control-sm" name="user" value="{{ $stockQuery }}" placeholder="@lang('Username or email')">
                        </form>
                    </div>
                    <div class="card-body">
                        <div class="row text-center mb-3">
                            <div class="col"><div class="text-muted small">@lang('Trades')</div><b>{{ $stockReport['trades'] }}</b></div>
                            <div class="col"><div class="text-muted small">@lang('Wins')</div><b>{{ $stockReport['wins'] }}</b></div>
                            <div class="col"><div class="text-muted small">@lang('Losses')</div><b>{{ $stockReport['losses'] }}</b></div>
                            <div class="col"><div class="text-muted small">@lang('Staked')</div><b>{{ number_format($stockReport['staked'], 2) }}</b></div>
                            <div class="col"><div class="text-muted small">@lang('Paid out')</div><b>{{ number_format($stockReport['paid'], 2) }}</b></div>
                            <div class="col"><div class="text-muted small">@lang('House')</div><b>{{ number_format($stockReport['house'], 2) }}</b></div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>@lang('User')</th>
                                    <th>@lang('Asset')</th>
                                    <th>@lang('Side')</th>
                                    <th>@lang('Stake')</th>
                                    <th>@lang('Open')</th>
                                    <th>@lang('Close')</th>
                                    <th>@lang('Result')</th>
                                    <th>@lang('Paid')</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($stockTrades as $trade)
                                    <tr>
                                        <td>{{ $trade->user->username ?? $trade->user->email ?? '—' }}</td>
                                        <td>{{ $trade->asset }}</td>
                                        <td>{{ $trade->direction === 'high' ? 'Buy' : 'Sell' }}</td>
                                        <td>{{ number_format((float) $trade->stake, 2) }}</td>
                                        <td>{{ $trade->open_price }}</td>
                                        <td>{{ $trade->close_price ?? '—' }}</td>
                                        <td>{{ $trade->result ?? $trade->status }}</td>
                                        <td>{{ number_format((float) $trade->payout_amount, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="8" class="text-muted">@lang('No trades yet.')</td></tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
