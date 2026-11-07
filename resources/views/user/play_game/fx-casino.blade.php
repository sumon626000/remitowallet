@extends(userLayout())
@section('title', trans('Trade Binary'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="FX Trade" submenu="Trade Binary"/>

        <div class="lc-fx-shell">
            <div class="lc-fx-bar">
                <a href="{{ route('user.play.game') }}"><i class="bi-chevron-left"></i> @lang('Exit')</a>
                <span>FX CASINO</span>
                <span></span>
            </div>
            <iframe
                id="fxCasinoFrame"
                src="{{ asset('games/fx-casino/index.html') }}"
                title="Trade Binary"
                allow="autoplay"
            ></iframe>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/user/css/play-game.css') }}">
@endpush

@push('script')
    <script>
        (function () {
            const iframe = document.getElementById('fxCasinoFrame');
            const currency = @json($currency);
            const csrf = @json(csrf_token());
            const betUrl = @json(route('user.play.game.fx.bet'));
            let chips = Number(@json($balance)) || 0;
            const minBet = Number(@json($fxMinBet));
            const maxBet = Number(@json($fxMaxBet));
            const presetStakes = @json($fxPresetStakes);

            function post(msg) {
                iframe.contentWindow && iframe.contentWindow.postMessage(msg, '*');
            }

            function pushConfig() {
                post({
                    type: 'fx:setConfig',
                    min_bet: minBet,
                    max_bet: maxBet,
                    preset_stakes: presetStakes,
                    currency: currency
                });
                post({ type: 'fx:setBalance', amount: chips });
            }

            iframe.addEventListener('load', pushConfig);

            window.addEventListener('message', async function (e) {
                const d = e.data || {};
                if (d.type !== 'fx:placeBet') return;
                const stake = Number(d.stake);
                if (!stake || stake < minBet || stake > maxBet) {
                    post({ type: 'fx:betDenied', error: 'Bet must be ' + minBet + '-' + maxBet });
                    return;
                }
                try {
                    const res = await fetch(betUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ stake: stake })
                    });
                    const data = await res.json();
                    if (!res.ok || !data.ok) {
                        if (typeof data.balance === 'number') {
                            chips = data.balance;
                            post({ type: 'fx:setBalance', amount: chips });
                        }
                        post({ type: 'fx:betDenied', error: data.message || 'Insufficient wallet balance' });
                        return;
                    }
                    chips = Number(data.balance);
                    post({
                        type: 'fx:betResult',
                        ok: true,
                        won: data.won,
                        stake: data.stake,
                        payout: data.payout,
                        profit: data.profit
                    });
                    post({ type: 'fx:setBalance', amount: chips });
                } catch (err) {
                    post({ type: 'fx:betDenied', error: 'Unable to reach wallet' });
                }
            });
        })();
    </script>
@endpush
