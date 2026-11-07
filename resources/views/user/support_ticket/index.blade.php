@extends(userLayout())
@section('title',__('Tickets Log'))

@section('content')

    <div class="content {{ containerClass() }}">

        <x-page-header menu="Support Ticket"/>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <div class="mb-2 mb-md-0">
                                <h4 class="card-header-title">@lang('Tickets')</h4>
                            </div>

                            <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                                <a href="{{ route('user.ticket.create') }}" class="btn btn-sm btn-white me-1">
                                    <i class="bi bi-plus-circle me-1"></i> @lang('New Ticket')</a>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th scope="col">@lang('Subject')</th>
                                    <th scope="col">@lang('Status')</th>
                                    <th scope="col">@lang('Last Reply')</th>
                                    <th scope="col">@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody class="js-sortable">
                                @forelse($tickets as $ticket)
                                    <tr>
                                        <td data-label="@lang('Subject')">
                                            <span>[{{ trans('Ticket# ').__($ticket->ticket) }}] {{ __($ticket->subject) }}</span>
                                        </td>
                                        <td data-label="@lang('Status')">
                                            {!! $ticket->getStatus() !!}
                                        </td>
                                        <td data-label="@lang('Last Reply')">
                                            <span>{{ dateTime($ticket->last_reply,basicControl()->date_time_format) }}</span>
                                        </td>
                                        <td data-label="@lang('Action')">
                                            <a href="{{ route('user.ticket.view', $ticket->ticket) }}"
                                               class="btn btn-sm btn-soft-info">
                                                <i class="bi bi-eye"></i> @lang('View')
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    @include('empty')
                                @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">
                            {{ $tickets->appends($_GET)->links('user.partials.pagination') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection
