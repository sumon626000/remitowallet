@extends(userLayout())
@section('title', trans('FX Trade'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="FX Trade"/>

        <div class="lc-arena">
            <h1 class="lc-title">@lang('All Stocks')</h1>

            <div class="lc-balance">
                <span><i class="bi-wallet2 me-1"></i> @lang('Balance')</span>
                <b>{{ $currency }}{{ number_format($balance, 2) }} <small class="fw-normal">{{ $currencyCode }}</small></b>
            </div>

            <div class="lc-home-grid">
                @forelse($games as $game)
                    <a class="lc-home-card {{ $game['tile'] }}" href="{{ route('user.play.game.show', $game['slug']) }}">
                        @if(!empty($game['badge']))
                            <span class="lc-badge-new">{{ $game['badge'] }}</span>
                        @endif
                        <span class="lc-home-glow {{ $game['tile'] }}"></span>
                        <span class="lc-home-orb {{ $game['tile'] }}"><i class="{{ $game['icon'] }}"></i></span>
                        <span class="lc-home-name">@lang($game['name'])</span>
                    </a>
                @empty
                    <p class="text-muted mb-0">@lang('No games are enabled right now.')</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection

@push('style')
    <link rel="stylesheet" href="{{ asset('assets/user/css/play-game.css') }}">
@endpush
