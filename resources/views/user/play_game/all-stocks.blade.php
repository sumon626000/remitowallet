@extends(userLayout())
@section('title', trans('All Stocks'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="FX Trade" submenu="All Stocks"/>

        <div class="as-wrap">
            <div class="as-head">
                <a class="as-back" href="{{ route('user.play.game') }}"><i class="bi-chevron-left"></i></a>
                <div>
                    <h1>@lang('All Stocks')</h1>
                    <p>@lang('Predict whether the price finishes higher or lower.')</p>
                </div>
            </div>

            @if($stockAssets->isEmpty() || empty($stockDurations))
                <div class="as-empty">@lang('All Stocks markets are not available right now.')</div>
            @else
                <div class="as-grid">
                    <label class="as-field">
                        <span>@lang('Asset')</span>
                        <div class="as-select" id="asAsset">
                            <button type="button" class="as-select-btn" id="asAssetBtn"></button>
                            <div class="as-menu" id="asAssetMenu" hidden>
                                <input type="search" id="asAssetSearch" placeholder="@lang('Search')" autocomplete="off">
                                <div id="asAssetList"></div>
                            </div>
                        </div>
                    </label>
                    <label class="as-field">
                        <span>@lang('Duration')</span>
                        <div class="as-select" id="asDuration">
                            <button type="button" class="as-select-btn" id="asDurationBtn"></button>
                            <div class="as-menu" id="asDurationMenu" hidden>
                                <div id="asDurationList"></div>
                            </div>
                        </div>
                    </label>
                    <label class="as-field">
                        <span>@lang('Stake')</span>
                        <input type="number" id="asStake" min="{{ $stockMin }}" max="{{ $stockMax }}" step="0.01" value="{{ $stockMin }}">
                    </label>
                    <label class="as-field">
                        <span>@lang('Payout')</span>
                        <input type="text" id="asPayout" readonly>
                    </label>
                </div>

                <div class="as-chart-card">
                    <canvas id="asChart" height="180"></canvas>
                </div>

                <div class="as-price-wrap">
                    <div class="as-symbol" id="asSymbol">—</div>
                    <div class="as-price" id="asPrice">—</div>
                    <div class="as-feed" id="asFeed">@lang('Connecting to the live price')</div>
                </div>

                <div class="as-actions">
                    <button type="button" class="as-btn high" id="asHigh" disabled>@lang('Buy')</button>
                    <button type="button" class="as-btn low" id="asLow" disabled>@lang('Sell')</button>
                </div>
                <div class="as-msg" id="asMsg"></div>

                <div class="as-stats">
                    <div><span>@lang('Balance')</span><b id="asBalance">{{ $currency }}{{ number_format($balance, 2) }}</b></div>
                    <div><span>@lang('Wins')</span><b id="asWins">0</b></div>
                    <div><span>@lang('Losses')</span><b id="asLosses">0</b></div>
                </div>

                <div class="as-history">
                    <h2>@lang('Trades')</h2>
                    <div class="as-table-wrap">
                        <table class="as-table">
                            <thead>
                            <tr>
                                <th>@lang('Asset')</th>
                                <th>@lang('Side')</th>
                                <th>@lang('Stake')</th>
                                <th>@lang('Open')</th>
                                <th>@lang('Close')</th>
                                <th>@lang('Result')</th>
                            </tr>
                            </thead>
                            <tbody id="asTrades"></tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/user/css/play-game.css') }}">
@endpush

@if($stockAssets->isNotEmpty() && !empty($stockDurations))
@push('script')
    <script>
        (function () {
            const config = @json($stockConfig);
            const csrf = @json(csrf_token());
            const currency = config.currency || '$';
            let asset = config.assets[0];
            let duration = config.durations[0];
            let price = null;
            let series = [];
            let busy = false;
            let serverOffset = 0;
            let trades = [];
            let pollTimer = null;

            const assetBtn = document.getElementById('asAssetBtn');
            const assetMenu = document.getElementById('asAssetMenu');
            const assetList = document.getElementById('asAssetList');
            const assetSearch = document.getElementById('asAssetSearch');
            const durationBtn = document.getElementById('asDurationBtn');
            const durationMenu = document.getElementById('asDurationMenu');
            const durationList = document.getElementById('asDurationList');
            const stakeEl = document.getElementById('asStake');
            const payoutEl = document.getElementById('asPayout');
            const priceEl = document.getElementById('asPrice');
            const symbolEl = document.getElementById('asSymbol');
            const feedEl = document.getElementById('asFeed');
            const msgEl = document.getElementById('asMsg');
            const highBtn = document.getElementById('asHigh');
            const lowBtn = document.getElementById('asLow');
            const canvas = document.getElementById('asChart');

            function money(value) {
                const n = Number(value);
                return currency + (Number.isFinite(n) ? n.toFixed(2) : '0.00');
            }

            function setMsg(text, kind) {
                msgEl.textContent = text || '';
                msgEl.className = 'as-msg' + (kind ? ' ' + kind : '');
            }

            function renderPayout() {
                const stake = Number(stakeEl.value);
                const pct = Number(config.payout);
                if (!Number.isFinite(stake) || stake <= 0) {
                    payoutEl.value = '';
                    return;
                }
                const total = stake + (stake * pct / 100);
                payoutEl.value = money(total) + ' (' + pct.toFixed(2) + '%)';
            }

            function closeMenus() {
                assetMenu.hidden = true;
                durationMenu.hidden = true;
            }

            function renderAssetList(filter) {
                assetList.replaceChildren();
                const q = (filter || '').toLowerCase();
                config.assets.forEach(function (item) {
                    const hay = (item.symbol + ' ' + item.name).toLowerCase();
                    if (q && hay.indexOf(q) === -1) return;
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = item.symbol + '  ' + item.name;
                    if (item.symbol === asset.symbol) btn.className = 'is-active';
                    btn.addEventListener('click', function () {
                        asset = item;
                        assetBtn.textContent = item.symbol;
                        symbolEl.textContent = item.symbol;
                        closeMenus();
                        refresh();
                    });
                    assetList.appendChild(btn);
                });
            }

            function renderDurationList() {
                durationList.replaceChildren();
                config.durations.forEach(function (item) {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = item.label;
                    if (item.seconds === duration.seconds) btn.className = 'is-active';
                    btn.addEventListener('click', function () {
                        duration = item;
                        durationBtn.textContent = item.label;
                        closeMenus();
                    });
                    durationList.appendChild(btn);
                });
            }

            function drawChart() {
                const ctx = canvas.getContext('2d');
                const width = canvas.clientWidth || 320;
                const height = 180;
                const ratio = window.devicePixelRatio || 1;
                canvas.width = Math.floor(width * ratio);
                canvas.height = Math.floor(height * ratio);
                ctx.setTransform(ratio, 0, 0, ratio, 0, 0);
                ctx.clearRect(0, 0, width, height);
                const points = series.filter(function (n) { return Number.isFinite(n); });
                if (points.length < 2) return;
                const min = Math.min.apply(null, points);
                const max = Math.max.apply(null, points);
                const span = (max - min) || (min * 0.002) || 1;
                const pad = 12;
                ctx.beginPath();
                points.forEach(function (point, i) {
                    const x = pad + (i / (points.length - 1)) * (width - pad * 2);
                    const y = height - pad - ((point - min) / span) * (height - pad * 2);
                    if (i === 0) ctx.moveTo(x, y);
                    else ctx.lineTo(x, y);
                });
                ctx.strokeStyle = '#377dff';
                ctx.lineWidth = 2;
                ctx.stroke();
                const last = points[points.length - 1];
                const x = width - pad;
                const y = height - pad - ((last - min) / span) * (height - pad * 2);
                ctx.fillStyle = '#377dff';
                ctx.beginPath();
                ctx.arc(x, y, 3.5, 0, Math.PI * 2);
                ctx.fill();
            }

            function resultText(trade) {
                if (trade.status === 'open') {
                    const left = Math.max(0, Number(trade.expire_time) - (Math.floor(Date.now() / 1000) + serverOffset));
                    return 'Open ' + left + 's';
                }
                if (trade.result === 'win') return 'Win';
                if (trade.result === 'loss') return 'Loss';
                if (trade.result === 'tie') return 'Tie';
                if (trade.result === 'void') return 'Void';
                return trade.status || '';
            }

            function renderTrades() {
                const body = document.getElementById('asTrades');
                body.replaceChildren();
                if (!trades.length) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 6;
                    td.className = 'as-empty-cell';
                    td.textContent = 'No trades yet';
                    tr.appendChild(td);
                    body.appendChild(tr);
                    return;
                }
                trades.forEach(function (trade) {
                    const tr = document.createElement('tr');
                    [trade.asset, trade.direction === 'high' ? 'Buy' : 'Sell', money(trade.stake), trade.open_price, trade.close_price || '—', resultText(trade)]
                        .forEach(function (value, index) {
                            const td = document.createElement('td');
                            td.textContent = value;
                            if (index === 5) td.className = 'as-result ' + (trade.result || trade.status || '');
                            tr.appendChild(td);
                        });
                    body.appendChild(tr);
                });
            }

            function buttons() {
                const ready = !busy && price !== null && Number(price) > 0;
                highBtn.disabled = !ready;
                lowBtn.disabled = !ready;
            }

            function applyState(data) {
                serverOffset = Number(data.server_time) - Math.floor(Date.now() / 1000);
                document.getElementById('asBalance').textContent = money(data.balance);
                document.getElementById('asWins').textContent = String(data.wins || 0);
                document.getElementById('asLosses').textContent = String(data.losses || 0);
                price = data.price;
                series = Array.isArray(data.series) ? data.series.map(Number) : [];
                trades = Array.isArray(data.trades) ? data.trades : [];
                if (price) {
                    priceEl.textContent = price;
                    priceEl.classList.remove('is-wait');
                    feedEl.textContent = 'Live market price';
                } else {
                    priceEl.textContent = '—';
                    priceEl.classList.add('is-wait');
                    feedEl.textContent = 'Live price is unavailable. Trading stays closed until a price arrives.';
                }
                drawChart();
                renderTrades();
                buttons();
            }

            function refresh() {
                const url = config.stateUrl + '?asset=' + encodeURIComponent(asset.symbol);
                return fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        if (!data.ok) {
                            setMsg(data.message || 'Could not load the market', 'bad');
                            return;
                        }
                        applyState(data);
                    })
                    .catch(function () {
                        feedEl.textContent = 'Live price is unavailable. Trading stays closed until a price arrives.';
                        buttons();
                    });
            }

            function schedule() {
                if (pollTimer) clearTimeout(pollTimer);
                const soon = trades.some(function (trade) {
                    return trade.status === 'open' && (Number(trade.expire_time) - (Math.floor(Date.now() / 1000) + serverOffset)) <= 5;
                });
                pollTimer = setTimeout(function () { refresh().finally(schedule); }, soon ? 1000 : 2000);
            }

            function place(direction) {
                if (busy || highBtn.disabled) return;
                const stake = Number(stakeEl.value);
                if (!Number.isFinite(stake) || stake < Number(config.min) || stake > Number(config.max)) {
                    setMsg('Stake must be ' + config.min + ' - ' + config.max, 'bad');
                    return;
                }
                busy = true;
                buttons();
                setMsg('');
                fetch(config.openUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrf
                    },
                    body: JSON.stringify({
                        asset: asset.symbol,
                        direction: direction,
                        stake: stake,
                        duration: duration.seconds
                    })
                }).then(function (res) { return res.json().then(function (data) { return { res: res, data: data }; }); })
                    .then(function (pack) {
                        if (!pack.data.ok) {
                            setMsg(pack.data.message || 'Trade was not opened', 'bad');
                            if (pack.data.balance !== undefined) {
                                document.getElementById('asBalance').textContent = money(pack.data.balance);
                            }
                            return;
                        }
                        document.getElementById('asBalance').textContent = money(pack.data.balance);
                        setMsg(direction === 'high' ? 'Buy trade opened' : 'Sell trade opened', 'ok');
                        return refresh();
                    })
                    .catch(function () {
                        setMsg('Trade was not opened', 'bad');
                    })
                    .finally(function () {
                        busy = false;
                        buttons();
                    });
            }

            assetBtn.textContent = asset.symbol;
            symbolEl.textContent = asset.symbol;
            durationBtn.textContent = duration.label;
            renderAssetList('');
            renderDurationList();
            renderPayout();

            assetBtn.addEventListener('click', function () {
                const open = assetMenu.hidden;
                closeMenus();
                assetMenu.hidden = !open;
                if (!assetMenu.hidden) assetSearch.focus();
            });
            durationBtn.addEventListener('click', function () {
                const open = durationMenu.hidden;
                closeMenus();
                durationMenu.hidden = !open;
            });
            assetSearch.addEventListener('input', function () { renderAssetList(assetSearch.value); });
            stakeEl.addEventListener('input', renderPayout);
            highBtn.addEventListener('click', function () { place('high'); });
            lowBtn.addEventListener('click', function () { place('low'); });
            document.addEventListener('click', function (event) {
                if (!event.target.closest('.as-select')) closeMenus();
            });
            window.addEventListener('resize', drawChart);

            refresh().finally(schedule);
            setInterval(renderTrades, 1000);
        })();
    </script>
@endpush
@endif
