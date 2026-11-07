@extends(userLayout())
@section('title', trans('Escrow Payment'))
@section('content')
    <div class="content {{ containerClass() }}">

        <x-page-header menu="Escrow"/>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Escrow Payment') </h4>
                    </div>

                    <div class="card-body">

                        <div class="list-group list-group-flush list-group-no-gutters">

                            <li class="list-group-item d-flex justify-content-between align-items-start">
                                <div class="ms-2 me-auto">
                                    <div class="">@lang('Status')</div>
                                </div>
                                {!! $escrow->getStatusBadge() !!}
                            </li>

                            <x-list-item label="Payable Amount" value="{{ (getAmount($escrow->transfer_amount)) .' '. __(optional($escrow->currency)->code) }}" class=" fw-semibold"/>
                            <x-list-item label="Total charge" value="{{ (getAmount($escrow->charge)) .' '. __(optional($escrow->currency)->code) }}" class=" fw-semibold text-danger"/>
                            <x-list-item label="Receivable Amount" value="{{ (getAmount($escrow->received_amount)) .' '. __(optional($escrow->currency)->code) }}" class=" fw-semibold"/>
                            <x-list-item label="Note" value="{{ __($escrow->note) }}" class=""/>
                            @if($escrow->file)
                                <li class="list-group-item d-flex justify-content-between align-items-start">
                                    <div class="ms-2 me-auto">
                                        <div class="fw-semibold">@lang('Attachment')</div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-secondary"
                                            onclick="downloadURI('{{getFile($escrow->driver,$escrow->file)}}')">
                                        <i class="fa-light fa-download"></i> @lang('Download')</button>
                                </li>
                            @endif
                        </div>

                        @if($escrow->receiver_id == Auth::id() && $escrow->status == 1)
                            <button type="button"
                                    class="btn btn-primary mt-4 confirmButton"
                                    name="status" value="2" data-bs-toggle="modal"
                                    data-bs-target="#confirmModal"
                                    data-text="@lang('accept this request?')">@lang('Accept')</button>
                            <button type="button"
                                    class="btn btn-danger mt-4 confirmButton" name="status" value="5"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmModal"
                                    data-text="@lang('cancel this request?')">@lang('Cancel')</button>
                        @elseif($escrow->sender_id == Auth::id() && $escrow->status == 2)
                            <button type="button"
                                    class="btn btn-primary mt-4 confirmButton" name="status" value="3"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmModal"
                                    data-text="@lang('request for payment?')">@lang('Request Payment')</button>
                        @elseif($escrow->receiver_id == Auth::id() && $escrow->status == 3)
                            <button type="button"
                                    class="btn btn-primary mt-4 confirmButton"
                                    name="status" value="4" data-bs-toggle="modal"
                                    data-bs-target="#confirmModal"
                                    data-text="@lang('request for payment disbursed?')">@lang('Payment disbursed')</button>
                            <a href="{{ route('user.dispute.view', $escrow->utr) }}"
                               class="btn btn-danger mt-4">@lang('Dispute')</a>
                        @elseif($escrow->receiver_id == Auth::id() && $escrow->status == 6)
                            <a href="{{ route('user.dispute.view', $escrow->utr) }}"
                               class="btn btn-info mt-4"><i class="bi-chat-dots"></i> @lang('View Conversation')</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="confirmModal" data-bs-backdrop="static" tabindex="-1" role="dialog"
         aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalHeader">@lang('Confirmation!')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="confirmModalBody"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <form action="{{ route('user.escrow.paymentView', $escrow->utr) }}" method="post">
                        @csrf
                        <button type="submit" class="btn btn-soft-primary" id="confirmButton"
                                name="status">@lang('Confirm')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';

        $(document).on('click', '.confirmButton', function (e) {
            let statusValue = $(this).val();
            let text = $(this).data('text');
            $('#confirmButton').val(statusValue);
            $('#confirmModalBody').text(`Are you sure you want to ${text}`);
        })

        function downloadURI(uri, name = 'escrow') {
            console.log("ok");
            var link = document.createElement("a");
            link.setAttribute('download', name);
            link.href = uri;
            document.body.appendChild(link);
            link.click();
            link.remove();
        }
    </script>
@endpush
