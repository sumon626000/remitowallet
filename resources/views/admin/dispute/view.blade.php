@extends('admin.layouts.app')
@section('page_title',__('Dispute'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Dispute")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("Dispute")</h1>
                </div>
            </div>
        </div>

        <div class="card message_section">
            <div class="card-header">
                <div class="top-bar">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <select class="form-control form-control-sm replyTo">
                                <option value="{{ optional($dispute->disputable)->receiver_id ?? '' }}" selected>
                                    @lang("Reply to Claimer ")
                                    ( {{ __(optional(optional($dispute->disputable)->receiver)->fullname) ?? __('N/A') }} )
                                </option>
                                <option value="{{ optional($dispute->disputable)->sender_id ?? '' }}">
                                    @lang("Reply to Defender ")
                                    ( {{ __(optional(optional($dispute->disputable)->sender)->fullname) ?? __('N/A') }} )
                                </option>
                            </select>
                        </div>
                        <div>
                            @if($dispute->defender_reply_yn == 1)
                                <button class="btn btn-white confirmModal" type="button"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-msg="@lang('mute this defender?')"
                                        data-route="{{ route('admin.defender.mute.unmute',[$dispute->utr,1]) }}">
                                    <i class="bi bi-mic"></i> @lang("Mute Defender")
                                    ( {{ __(optional(optional($dispute->disputable)->sender)->fullname) ?? __('N/A') }} )
                                </button>
                            @else
                                <button class="btn btn-white text-danger confirmModal" type="button"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-msg="@lang('unmute this defender?')"
                                        data-route="{{ route('admin.defender.mute.unmute',[$dispute->utr,0]) }}">
                                    <i class="bi bi-mic-mute"></i> @lang("Unmute Defender")
                                    ( {{ __(optional(optional($dispute->disputable)->sender)->fullname) ?? __('N/A') }} )
                                </button>
                            @endif

                            @if($dispute->claimer_reply_yn == 1)
                                <button class="btn btn-white confirmModal" type="button"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-msg="@lang('mute this claimer?')"
                                        data-route="{{ route('admin.claimer.mute.unmute',[$dispute->utr,1]) }}">
                                    <i class="bi bi-mic"></i> @lang("Mute Claimer")
                                    ( {{ __(optional(optional($dispute->disputable)->receiver)->fullname) ?? __('N/A') }} )
                                </button>
                            @else
                                <button class="btn btn-white text-danger confirmModal" type="button"
                                        data-bs-toggle="modal" data-bs-target="#confirmModal"
                                        data-msg="@lang('unmute this claimer?')"
                                        data-route="{{ route('admin.claimer.mute.unmute',[$dispute->utr,0]) }}">
                                    <i class="bi bi-mic-mute"></i> @lang("Unmute Claimer")
                                    ( {{ __(optional(optional($dispute->disputable)->receiver)->fullname) ?? __('N/A') }} )
                                </button>
                            @endif
                        </div>
                        <div>
                            <button class="btn btn-white confirmModal" type="button"
                                    data-bs-toggle="modal" data-bs-target="#confirmModal"
                                    data-msg="@lang('solved this dispute?')"
                                    data-route="{{ route('admin.dispute.status.change', [$dispute->utr,1]) }}" {{ $dispute->status == 1 ? 'disabled' : ''}}>
                                <i class="bi bi-check"></i> @lang("Solved")
                            </button>
                            <button class="btn btn-white confirmModal" type="button"
                                    data-bs-toggle="modal" data-bs-target="#confirmModal"
                                    data-msg="@lang('closed this dispute?')"
                                    data-route="{{ route('admin.dispute.status.change', [$dispute->utr,2]) }}" {{ $dispute->status == 2 ? 'disabled' : ''}}>
                                <i class="bi bi-x-square"></i> @lang("Close")
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            @if(isset($dispute) && $dispute->disputeDetails->count() > 0)
                <div class="message-wrapper">
                    <div class="row g-lg-0">
                        <div class="col-12">
                            <div class="inbox-wrapper">
                                <div class="chats">
                                    @foreach($dispute->disputeDetails as $dispute_details)
                                        @if(isset($dispute_details->message))
                                            @if($dispute_details->admin_id == null)
                                                <div class="chat-box this-side">
                                                    <div class="text-wrapper">
                                                        <div class="text bg-soft-dark">
                                                            <p>{{ __($dispute_details->message) }}</p>
                                                        </div>
                                                        @if(isset($dispute_details->files) && count($dispute_details->files) > 0)
                                                            <div class="text-info my-3 d-flex time">
                                                                @foreach($dispute_details->files as $k => $image)
                                                                    <a href="javascript:void(0)"
                                                                       onclick="downloadFile('{{getFile($image->driver,$image->file)}}')"
                                                                       class="file" type="button">
                                                                        <i class="fal fa-file"></i>
                                                                        @lang('File(s)') {{ ++$k}}
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        <span
                                                            class="time">{{ __($dispute_details->created_at->format('d M, Y h:i A')) }}</span>
                                                    </div>
                                                    <div class="img">
                                                        <img class="img-fluid"
                                                             src="{{ getFile(optional($dispute_details->user)->image_driver, optional($dispute_details->user)->image) }}"/>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="chat-box opposite-side">
                                                    <div class="img">
                                                        <img class="img-fluid"
                                                             src="{{ getFile(optional($dispute_details->admin)->image_driver, optional($dispute_details->admin)->image) }}"
                                                             alt="Admin Image"/>
                                                    </div>
                                                    <div class="text-wrapper">
                                                        <div class="text bg-soft-dark">
                                                            <p>{{ __($dispute_details->message) }}</p>
                                                        </div>
                                                        @if(isset($dispute_details->files) && count($dispute_details->files) > 0)
                                                            <div class="text-info my-3 d-flex time">
                                                                @foreach($dispute_details->files as $k => $image)
                                                                    <a href="javascript:void(0)"
                                                                       onclick="downloadFile('{{getFile($image->driver,$image->file)}}')"
                                                                       class="file" type="button">
                                                                        <i class="fal fa-file"></i>
                                                                        @lang('File(s)') {{ ++$k}}
                                                                    </a>
                                                                @endforeach
                                                            </div>
                                                        @endif
                                                        <span
                                                            class="time">{{ __($dispute_details->created_at->format('d M, Y h:i A')) }}</span>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <div class="answer text-center chat-action">
                                                <p class="p-0 m-0">
                                                    @if($dispute_details->action == 0)
                                                        @lang("Issue has been solved by") {{ __(optional($dispute_details->admin)->name) ?? __('Admin') }}
                                                    @elseif($dispute_details->action == 1)
                                                        @lang("Issue has been closed by") {{ __(optional($dispute_details->admin)->name) ?? __('Admin') }}
                                                    @elseif($dispute_details->action == 2)
                                                        {{ __(optional($dispute_details->user)->name) ?? __('Admin') }} @lang("has been muted by") {{ __(optional($dispute_details->admin)->name) ?? __('N/A') }}
                                                    @elseif($dispute_details->action == 3)
                                                        {{ __(optional($dispute_details->user)->name) ?? __('Admin') }} @lang("has been unmuted by") {{ __(optional($dispute_details->admin)->name) ?? __('N/A') }}
                                                    @endif
                                                </p>
                                                <span
                                                    class="text-black-50">{{ __($dispute_details->created_at->format('d M, Y h:i A')) }}</span>
                                            </div>
                                        @endif
                                    @endforeach
                                </div>

                                <div class="typing-area">
                                    <form action="{{ route('admin.dispute.view', $dispute->utr) }}"
                                          method="post" enctype="multipart/form-data">
                                        @csrf
                                        @method('put')
                                        <input type="hidden" name="user_id" value="">
                                        <div class="col-sm-3">
                                            <div class="filepond" id="myFileInput" data-maxFiles="2"
                                                 data-fileSize="5000000"
                                                 data-oldFiles=""

                                                 data-label-idle="Drop files here or <span class='filepond--label-action'>Browse</span>">
                                            </div>
                                            <input name="attachments[]" class="filepond-files" type="file" hidden>
                                        </div>
                                        <div class="input-group">
                                            <div>
                                                <button type="button" class="upload-img send-file-btn">
                                                    <i class="fal fa-paperclip"></i>
                                                </button>
                                            </div>
                                            <input type="text"
                                                   class="form-control @error('message') is-invalid @enderror"
                                                   name="message" value="{{old('message')}}" autocomplete="off"/>
                                            <button class="submit-btn" type="submit">
                                                <i class="fal fa-paper-plane"></i>
                                            </button>
                                        </div>
                                        @error('message')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                        @enderror
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Close Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="CloseTicketModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="accountAddCardModalLabel"><i
                            class="bi bi-check2-square"></i> @lang("Confirmation")</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span id="meg">@lang("Do you want to closed this ticket?")</span>
                </div>
                <form action="" method="post" id="confirmModalForm">
                    @csrf
                    @method('put')
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang("Close")</button>
                        <button type="submit" class="btn btn-primary">@lang("Confirm")</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('css-lib')
    <link rel="stylesheet" href="{{ asset('assets/admin/css/filepond.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/filepond-plugin-image-preview.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/filepond.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/filepond.jquery.js') }}"></script>
    <script src="{{ asset('assets/admin/js/filepond-plugin-image-preview.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/filepond-plugin-file-validate-size.js') }}"></script>
    <script src="{{ asset('assets/admin/js/filepond-plugin-file-validate-type.js') }}"></script>
    <script src="{{ asset('assets/admin/js/custom-filepond.js') }}" defer></script>
@endpush

@push('script')
    <script>
        'use strict';
        $('input[name="user_id"]').val($('.replyTo').find(":selected").val());
        $(document).on('change', '.replyTo', function () {
            $('input[name="user_id"]').val($(this).val());
        });

        function downloadFile(url, filename = 'dispute_file') {
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        $(document).on('click', '.confirmModal', function (e) {
            let formUrl = $(this).data('route');
            let msg = $(this).data('msg');
            $('#confirmModalForm').attr('action', formUrl)
            $('#meg').text('Do you want to ' + msg)
        });
    </script>
@endpush




