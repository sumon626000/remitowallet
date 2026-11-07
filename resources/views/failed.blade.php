@extends('layouts.payment-status')
@section('title', __('Payment Failed'))

@section('content')
    <div class="status-icon text-danger">
        <i class="bi bi-x-circle"></i>
    </div>
    <div class="status-title">@lang('Payment Failed')</div>
    <div class="status-message">
        @lang('We really appreciate you giving us a moment of your time today but unfortunately the payment was unsuccessful due to')
        {{ session('error') ?? __('it seems some issue in server to server communication. Kindly connect with administrator') }}
    </div>
    <a href="{{ url('/') }}" class="btn btn-danger btn-home"> <i class="bi-house"></i> @lang('Go to home')</a>
@endsection
