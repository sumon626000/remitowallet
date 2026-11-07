@extends(userLayout())
@section('title', trans('Royal Steps'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="FX Trade" submenu="Royal Steps"/>

        <div class="rs-wrap">
            <div class="rs-head">
                <a class="rs-icon-btn" href="{{ route('user.play.game') }}"><i class="bi-chevron-left"></i></a>
                <h1>@lang('Royal Steps')</h1>
                <div class="rs-bal"><i class="bi-wallet2"></i> <span id="rsBal">{{ $currency }}{{ number_format($balance, 2) }}</span> <small>{{ $currencyCode }}</small></div>
            </div>

            <div class="rs-stage">
                <div class="rs-mult">
                    <div class="rs-mult-label" id="rsMultLabel">@lang('Multiplier')</div>
                    <div class="rs-mult-val"><span id="rsMultVal">1.00</span><small>×</small></div>
                </div>
                <div id="rsStairs"></div>
                <div class="rs-char" id="rsChar">
                    <i class="bi-crown-fill rs-crown"></i>
                    <span class="rs-head-dot"></span>
                    <span class="rs-body-dot"></span>
                </div>
                <div class="rs-betline" id="rsBetline"></div>
                <div class="rs-overlay d-none" id="rsOverlay">
                    <div class="rs-modal" id="rsModal"></div>
                </div>
            </div>

            <div id="rsSetup">
                <div class="rs-betbox">
                    <div class="rs-bet-label">@lang('Bet')</div>
                    <div class="rs-bet-row">
                        <input type="number" id="rsBet" value="{{ $royalMinBet }}" min="{{ $royalMinBet }}" max="{{ $royalMaxBet }}" step="0.01">
                        <button type="button" data-bet="min">Min</button>
                        <button type="button" data-bet="half">½</button>
                        <button type="button" data-bet="double">2×</button>
                        <button type="button" data-bet="max">Max</button>
                    </div>
                </div>
                <button type="button" class="rs-btn gold" id="rsStart">@lang('Start Game')</button>
            </div>

            <div class="rs-actions d-none" id="rsActions">
                <button type="button" class="rs-btn green" id="rsCash"><span>@lang('Collect')</span><small id="rsCashAmt"></small></button>
                <button type="button" class="rs-btn gold" id="rsClimb"><span>@lang('Climb')</span><small id="rsNextMult"></small></button>
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
            const MAX_STEPS = 10;
            const BASE_MULT = 1.15;
            const MIN_BET = Number(@json($royalMinBet));
            const MAX_BET = Number(@json($royalMaxBet));
            const currency = @json($currency);
            const csrf = @json(csrf_token());
            const urls = {
                start: @json(route('user.play.game.royal.start')),
                climb: @json(route('user.play.game.royal.climb')),
                cash: @json(route('user.play.game.royal.cash'))
            };
            let chips = Number(@json($balance)) || 0;
            let bet = MIN_BET;
            let round = @json($royalRound);
            let step = round ? Number(round.step) || 0 : 0;
            let busted = false;
            let won = null;
            let jumping = false;
            let busy = false;

            const stairsEl = document.getElementById('rsStairs');
            const charEl = document.getElementById('rsChar');
            const overlay = document.getElementById('rsOverlay');
            const modal = document.getElementById('rsModal');
            const setup = document.getElementById('rsSetup');
            const actions = document.getElementById('rsActions');
            const betInput = document.getElementById('rsBet');

            function mults() {
                return Array.from({ length: MAX_STEPS }, (_, i) => Number(Math.pow(BASE_MULT, i + 1).toFixed(2)));
            }
            function posFor(i) {
                const t = MAX_STEPS <= 1 ? 0 : i / (MAX_STEPS - 1);
                return { x: 15 + t * 65, y: 78 - t * 58 };
            }
            function currentMult() { return step > 0 ? mults()[step - 1] : 1; }
            function nextMult() { return mults()[step] || null; }
            function roundBet() {
                if (round && round.bet != null) return Number(round.bet);
                return Number(bet) || 0;
            }
            function setBal() { document.getElementById('rsBal').textContent = currency + chips.toFixed(2); }

            function drawStairs() {
                const m = mults();
                const stake = roundBet();
                stairsEl.innerHTML = '';
                for (let i = 0; i < MAX_STEPS; i++) {
                    const reached = i < step;
                    const isCurrent = i === step - 1;
                    const isNext = i === step && round && !busted && !won;
                    const p = posFor(i);
                    const block = document.createElement('div');
                    block.className = 'rs-step' + (reached ? ' reached' : '') + (isNext ? ' next' : '');
                    block.style.left = p.x + '%';
                    block.style.bottom = p.y + '%';
                    block.style.zIndex = String(5 + i);
                    block.innerHTML = '<div class="rs-3d"><div class="rs-side"></div><div class="rs-front"></div><div class="rs-top">×' + m[i].toFixed(2) + (busted && isCurrent ? '<span class="rs-boom">💥</span>' : '') + '</div></div>';
                    stairsEl.appendChild(block);
                }
                const base = step === 0 ? { x: 8, y: 82 } : posFor(Math.max(0, Math.min(step, MAX_STEPS - 1)));
                charEl.style.left = base.x + '%';
                charEl.style.bottom = base.y + '%';
                charEl.classList.toggle('jump', jumping);
                document.getElementById('rsMultVal').textContent = (won?.mult ?? currentMult()).toFixed(2);
                document.getElementById('rsMultLabel').textContent = busted ? @json(__('Trap!')) : (won ? @json(__('Won')) : @json(__('Multiplier')));
                document.getElementById('rsBetline').textContent = (round || step > 0)
                    ? (@json(__('Step')) + ' ' + step + '/' + MAX_STEPS + '  •  ' + currency + stake)
                    : '';
                const nm = nextMult();
                document.getElementById('rsNextMult').textContent = nm ? ('×' + nm.toFixed(2)) : '';
                document.getElementById('rsCashAmt').textContent = (step > 0 && stake > 0)
                    ? (currency + (stake * currentMult()).toFixed(2))
                    : '';
                document.getElementById('rsCash').disabled = step < 1 || !round || busted || !!won;
                document.getElementById('rsClimb').disabled = step >= MAX_STEPS || !round || busted || !!won;
            }

            function showEnd() {
                overlay.classList.remove('d-none');
                if (won) {
                    modal.innerHTML = '<i class="bi-trophy-fill"></i><div class="tiny">@lang("You Won")</div><div class="prize">' + currency + won.prize.toFixed(2) + '</div><div class="tiny">×' + won.mult.toFixed(2) + '</div><button type="button" class="rs-btn gold" id="rsAgain">@lang("Play Again")</button>';
                } else {
                    modal.innerHTML = '<i class="bi-fire"></i><div class="tiny bust">@lang("Busted")</div><button type="button" class="rs-btn gold" id="rsAgain">@lang("Play Again")</button>';
                }
                document.getElementById('rsAgain').onclick = reset;
            }

            function reset() {
                round = null; step = 0; busted = false; won = null; busy = false;
                overlay.classList.add('d-none');
                setup.classList.remove('d-none');
                actions.classList.add('d-none');
                drawStairs();
            }

            function enterRound(data) {
                if (typeof data.balance === 'number') {
                    chips = data.balance;
                    setBal();
                }
                if (data.round) {
                    round = data.round;
                    bet = Number(round.bet);
                    step = Number(round.step) || 0;
                }
                busted = false;
                won = null;
                setup.classList.add('d-none');
                actions.classList.remove('d-none');
                drawStairs();
            }

            async function api(url, body) {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(body || {})
                });
                const data = await res.json();
                if (typeof data.balance === 'number') {
                    chips = data.balance;
                    setBal();
                }
                if (!res.ok || !data.ok) {
                    throw new Error(data.message || @json(__('Unable to update wallet')));
                }
                return data;
            }

            document.querySelectorAll('[data-bet]').forEach(btn => {
                btn.addEventListener('click', function () {
                    const v = Number(betInput.value) || MIN_BET;
                    if (this.dataset.bet === 'min') betInput.value = MIN_BET;
                    if (this.dataset.bet === 'half') betInput.value = Math.max(MIN_BET, Math.floor(v / 2));
                    if (this.dataset.bet === 'double') betInput.value = Math.min(MAX_BET, Math.max(MIN_BET, v * 2));
                    if (this.dataset.bet === 'max') betInput.value = Math.max(MIN_BET, Math.min(MAX_BET, Math.floor(chips)));
                });
            });

            document.getElementById('rsStart').addEventListener('click', async function () {
                if (busy) return;
                bet = Math.max(MIN_BET, Number(betInput.value) || MIN_BET);
                if (bet > MAX_BET) { alert(@json(__('Bet exceeds maximum'))); return; }
                if (bet > chips) { alert(@json(__('Insufficient wallet balance'))); return; }
                busy = true;
                try {
                    const data = await api(urls.start, { bet: bet });
                    enterRound(data);
                } catch (err) {
                    alert(err.message);
                } finally {
                    busy = false;
                }
            });

            document.getElementById('rsClimb').addEventListener('click', async function () {
                if (!round || busted || won || step >= MAX_STEPS || busy) return;
                busy = true;
                jumping = true;
                drawStairs();
                try {
                    const data = await Promise.all([
                        api(urls.climb),
                        new Promise(function (resolve) { setTimeout(resolve, 420); })
                    ]).then(function (parts) { return parts[0]; });
                    jumping = false;
                    if (data.busted) {
                        busted = true;
                        if (round && round.bet != null) bet = Number(round.bet);
                        round = null;
                        document.getElementById('rsMultLabel').textContent = @json(__('Trap!'));
                        actions.classList.add('d-none');
                        drawStairs();
                        showEnd();
                        return;
                    }
                    step = Number(data.step) || 0;
                    if (data.round) {
                        round = data.round;
                        bet = Number(round.bet);
                    } else if (round) {
                        round.step = step;
                        bet = Number(round.bet);
                    }
                    if (data.won) {
                        won = data.won;
                        if (round && round.bet != null) bet = Number(round.bet);
                        round = null;
                        actions.classList.add('d-none');
                        drawStairs();
                        showEnd();
                        return;
                    }
                    drawStairs();
                } catch (err) {
                    jumping = false;
                    alert(err.message);
                    drawStairs();
                } finally {
                    busy = false;
                }
            });

            document.getElementById('rsCash').addEventListener('click', async function () {
                if (!round || step < 1 || busted || won || busy) return;
                busy = true;
                try {
                    const data = await api(urls.cash);
                    if (data.won) {
                        won = data.won;
                        if (round && round.bet != null) bet = Number(round.bet);
                        round = null;
                        actions.classList.add('d-none');
                        drawStairs();
                        showEnd();
                    }
                } catch (err) {
                    alert(err.message);
                } finally {
                    busy = false;
                }
            });

            setBal();
            if (round) {
                enterRound({ round: round, balance: chips });
            } else {
                drawStairs();
            }
        })();
    </script>
@endpush
