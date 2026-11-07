@extends('layouts.payment-status')
@section('title', __('Payment Success'))

@section('content')
    <div class="status-icon text-success">
        <i class="bi bi-check-circle"></i>
    </div>
    <div class="status-title">@lang('Payment Success')</div>
    <div class="status-message">
        @lang('Thanks a bunch for filling that out. It means a lot to us, just like you do! We really appreciate you giving us a moment of your time today.')<br>
        @lang('Thanks for being with us.')
    </div>
    <a href="{{ url('/') }}" class="btn btn-success btn-home"> <i class="bi-house"></i> @lang('Go to home')</a>
@endsection
