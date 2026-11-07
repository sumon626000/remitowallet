@extends('layouts.payment-status')
@section('title', __('Invalid Payment'))

@section('content')
    <div class="status-icon text-danger">
        <i class="bi bi-exclamation-circle"></i>
    </div>
    <div class="status-title">@lang('Invalid or Expired!')</div>
    <div class="status-message">
        @lang('The payment link you followed is either invalid, has already been used, or has expired due to time limits.')<br>
        @lang('Please check the link or generate a new one.')
    </div>
    <a href="{{ url('/') }}" class="btn btn-danger btn-home"> <i class="bi-house"></i> @lang('Go to home')</a>
@endsection
