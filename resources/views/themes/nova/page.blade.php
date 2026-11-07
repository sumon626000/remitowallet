@extends(template() . 'layouts.app')

@section('title', trans('Home'))

@section('content')

    {{-- Reloadly Widget (Airtime + Gift Cards + Recharge) --}}

    {{-- CMS Sections --}}
    {!! $sectionsData !!}

@endsection