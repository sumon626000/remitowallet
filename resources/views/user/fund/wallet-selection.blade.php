@extends(userLayout())
@section('title',__('Add Fund'))

@section('content')

    <div class="content {{ containerClass() }}">

        <x-page-header
            :menu="'Wallet Selection'"
            :pagetitle="'Choose the wallet where your amount should be added.' "
        />

        <form action="{{ route('fund.initialize') }}" method="get">
            <div class="payment-grid">
                @forelse($currencies as $key => $currency)
                    <label>
                        <input type="radio" name="receive_currency"
                               value="{{$currency->code}}" {{$key == 0 ? 'checked':''}}>
                        <div class="payment-card">
                            <span class="method-name">{{$currency->name}}</span>
                            <span class="fee">{{currencyPosition($currency->balance(),$currency->id)}}</span>
                            <img src="{{getFile($currency->driver,$currency->logo)}}" class="brand-logo"
                                 alt="card">
                        </div>
                    </label>
                @empty
                    <div class="col-12 text-center">
                        <p>No currencies found</p>
                    </div>
                @endforelse
            </div>
            <div class="text-end mt-3">
                <button type="submit" class="btn btn-primary">@lang('Continue') <i class="bi-chevron-right"></i>
                </button>
            </div>
        </form>
    </div>
@endsection
@push('style')

    <style>
        :root {
            /* Light Mode Variables */
            --bg-card: #f4f5f7;
            --bg-selected: #ffffff;
            --border-selected: #007bff;
            --text-title: #8e8e8e;
            --text-main: #1a1a1a;
            --text-fee: #444444;
            --shadow: rgba(0, 0, 0, 0.05);
        }

        /* Dark Mode Styles */
        .dark-mode {
            --bg-card: #1e1e1e;
            --bg-selected: #252525;
            --border-selected: #3399ff;
            --text-title: #aaaaaa;
            --text-main: #ffffff;
            --text-fee: #cccccc;
            --shadow: rgba(0, 0, 0, 0.4);
        }

        .payment-grid {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        /* Hide the actual radio button */
        .payment-grid input[type="radio"] {
            display: none;
        }

        /* The Label acts as the Card */

        .payment-card {
            position: relative;
            width: 150px;
            height: 70px;
            background-color: var(--bg-card);
            border: 2px solid transparent;
            border-radius: 12px;
            padding: 12px;
            cursor: pointer;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.2s ease;
            user-select: none;
        }


        /* Hover state */
        .payment-card:hover {
            transform: translateY(-2px);
            background-color: var(--bg-selected);
            box-shadow: 0 4px 12px var(--shadow);
        }

        /* Radio Checked State (Active) */
        input[type="radio"]:checked + .payment-card {
            background-color: var(--bg-selected);
            border-color: var(--border-selected);
            box-shadow: 0 6px 16px var(--shadow);
        }

        .method-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
        }

        .fee {
            font-size: 12px;
            font-weight: 700;
            color: var(--text-fee);
        }

        .brand-logo {
            position: absolute;
            right: 12px;
            bottom: 12px;
            height: 18px;
            filter: grayscale(0.2);
        }

    </style>
@endpush
