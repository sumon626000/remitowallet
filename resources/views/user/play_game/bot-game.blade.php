@extends(userLayout())
@section('title', trans('Bot Game'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="FX Trade" submenu="Bot Game"/>

        <div class="bot-wrap">
            <div class="bot-head">
                <a class="rs-icon-btn" href="{{ route('user.play.game') }}"><i class="bi-chevron-left"></i></a>
                <h1>@lang('Bot Game')</h1>
                <div class="rs-bal"><i class="bi-wallet2"></i> <span id="botBal">{{ $currency }}{{ number_format($balance, 2) }}</span> <small>{{ $currencyCode }}</small></div>
            </div>

            <div class="bot-stage">
                <div class="bot-vs">
                    <div class="bot-side you">
                        <i class="bi-person-fill"></i>
                        <span>@lang('You')</span>
                    </div>
                    <div class="bot-vs-label">VS</div>
                    <div class="bot-side bot">
                        <i class="bi-robot"></i>
                        <span>@lang('Bot')</span>
                    </div>
                </div>
                <div class="bot-result" id="botResult">@lang('Pick Up or Down')</div>
                <div class="bot-market" id="botMarket">—</div>
            </div>

            <div class="bot-betbox">
                <div class="rs-bet-label">@lang('Bet')</div>
                <div class="rs-bet-row">
                    <input type="number" id="botBet" value="{{ $botMinBet }}" min="{{ $botMinBet }}" max="{{ $botMaxBet }}" step="0.01">
                    <button type="button" data-bot-bet="min">Min</button>
                    <button type="button" data-bot-bet="half">½</button>
                    <button type="button" data-bot-bet="double">2×</button>
                    <button type="button" data-bot-bet="max">Max</button>
                </div>
            </div>

            <div class="bot-actions">
                <button type="button" class="rs-btn green" id="botUp"><i class="bi-arrow-up-circle"></i> @lang('Up')</button>
                <button type="button" class="rs-btn gold" id="botDown"><i class="bi-arrow-down-circle"></i> @lang('Down')</button>
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/user/css/play-game.css') }}">
@endpush

@push('script')
    <script>
        (function () {
            const MIN_BET = Number(@json($botMinBet));
            const MAX_BET = Number(@json($botMaxBet));
            const currency = @json($currency);
            const csrf = @json(csrf_token());
            const betUrl = @json(route('user.play.game.bot.bet'));
            let chips = Number(@json($balance)) || 0;
            let busy = false;
            const betInput = document.getElementById('botBet');

            function setBal() {
                document.getElementById('botBal').textContent = currency + chips.toFixed(2);
            }

            async function play(side) {
                if (busy) return;
                const stake = Math.max(MIN_BET, Number(betInput.value) || MIN_BET);
                if (stake > MAX_BET) { alert(@json(__('Bet exceeds maximum'))); return; }
                if (stake > chips) { alert(@json(__('Insufficient wallet balance'))); return; }
                busy = true;
                document.getElementById('botResult').textContent = @json(__('Matching bot...'));
                document.getElementById('botMarket').textContent = '—';
                try {
                    const res = await fetch(betUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ stake: stake, side: side })
                    });
                    const data = await res.json();
                    if (typeof data.balance === 'number') {
                        chips = data.balance;
                        setBal();
                    }
                    if (!res.ok || !data.ok) {
                        throw new Error(data.message || @json(__('Unable to update wallet')));
                    }
                    document.getElementById('botMarket').textContent =
                        @json(__('Market')) + ': ' + String(data.market || '').toUpperCase();
                    document.getElementById('botResult').textContent = data.won
                        ? (@json(__('You beat the bot! +')) + currency + Number(data.profit).toFixed(2))
                        : (@json(__('Bot won. -')) + currency + Math.abs(Number(data.profit)).toFixed(2));
                    document.getElementById('botResult').className = 'bot-result ' + (data.won ? 'win' : 'lose');
                } catch (err) {
                    document.getElementById('botResult').textContent = err.message;
                    document.getElementById('botResult').className = 'bot-result lose';
                } finally {
                    busy = false;
                }
            }

            document.querySelectorAll('[data-bot-bet]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const v = Number(betInput.value) || MIN_BET;
                    if (this.dataset.botBet === 'min') betInput.value = MIN_BET;
                    if (this.dataset.botBet === 'half') betInput.value = Math.max(MIN_BET, Math.floor(v / 2));
                    if (this.dataset.botBet === 'double') betInput.value = Math.min(MAX_BET, Math.max(MIN_BET, v * 2));
                    if (this.dataset.botBet === 'max') betInput.value = Math.max(MIN_BET, Math.min(MAX_BET, Math.floor(chips)));
                });
            });

            document.getElementById('botUp').addEventListener('click', function () { play('up'); });
            document.getElementById('botDown').addEventListener('click', function () { play('down'); });
            setBal();
        })();
    </script>
@endpush
