
@section('message')
    @if($exception?->getMessage())
        {{ __($exception->getMessage()) }}
    @else
        {{ __("You don't have permission to access this page.") }}
    @endif
@endsection

@include(template().'errors.403')

