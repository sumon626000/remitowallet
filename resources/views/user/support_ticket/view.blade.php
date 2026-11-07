@extends(userLayout())
@section('title',__('Support Ticket'))
@section('content')
    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="javascript:void(0)">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang("Support Ticket")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("Support Ticket")</h1>
                </div>
            </div>
        </div>

        <div class="card message_section">
            <div class="card-header">
                <div class="top-bar">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            {!! $ticket->getStatus() !!}
                            <span>[{{trans('Ticket#'). __($ticket->ticket) }}] {{ __($ticket->subject) }}</span>
                        </div>
                        <div>
                            <button class="btn btn-sm btn-soft-danger set" type="button"
                                    data-bs-toggle="modal" data-bs-target="#CloseTicketModal">
                                <i class="bi bi-x-square"></i> @lang("Close")
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="message-wrapper">
                <div class="row g-lg-0">
                    <div class="col-12">
                        <div class="inbox-wrapper">
                            <div class="chats">
                                @forelse($ticket->messages as $message)
                                    @if($message->admin_id == null)
                                        <div class="chat-box this-side">
                                            <div class="text-wrapper">
                                                <div class="text bg-soft-dark">
                                                    <p class="text-body">@lang($message->message)</p>
                                                </div>
                                                @if(count($message->attachments) > 0)
                                                    <div class="text-info my-1 d-flex time justify-content-end">
                                                        @forelse($message->attachments as $k => $file)
                                                            <a href="{{ route('admin.ticket.download',encrypt($file->id)) }}"
                                                               class="file" type="button">
                                                                <i class="fal fa-file"></i> @lang('File')({{ ++$k}})
                                                            </a>
                                                        @empty
                                                        @endforelse
                                                    </div>
                                                @endif
                                                <span class="time">
                                                    {{ __($message->created_at->format('d M, Y h:i A')) }}</span>
                                            </div>
                                            <div class="img">
                                                <img class="img-fluid" src="{{ $user->getImage() }}" alt="..."/>
                                            </div>
                                        </div>
                                    @endif
                                    @if($message->admin_id)
                                        <div class="chat-box opposite-side">
                                            <div class="img">
                                                <img class="img-fluid" src="{{ $admin->profilePicture() }}"
                                                     alt="Admin Image"/>
                                            </div>
                                            <div class="text-wrapper ">
                                                <div class="text bg-soft-dark">
                                                    <p class="text-body">@lang($message->message)</p>
                                                </div>
                                                @if(count($message->attachments) > 0)
                                                    <div class="text-info my-1 d-flex time ">
                                                        @forelse($message->attachments as $k => $file)
                                                            <a href="{{ route('admin.ticket.download',encrypt($file->id)) }}"
                                                               class="file" type="button">
                                                                <i class="fal fa-file"></i>
                                                                @lang('File')({{ ++$k}})
                                                            </a>
                                                        @empty
                                                        @endforelse
                                                    </div>
                                                @endif
                                                <span class="time">
                                                    {{ __($message->created_at->format('d M, Y h:i A')) }}</span>
                                            </div>
                                        </div>
                                    @endif
                                @empty
                                @endforelse
                            </div>

                            <div class="typing-area">
                                <form action="{{ userRoute('ticket.reply', $ticket->id) }}"
                                      method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('put')
                                    <div class="col-sm-3">
                                        <div class="filepond" id="myFileInput" data-maxFiles="2"
                                             data-fileSize="5000000"
                                             data-oldFiles=""
                                             data-label-idle="Drop files here or <span class='filepond--label-action'>Browse</span>">
                                        </div>
                                        <input name="attachments[]" class="filepond-files" type="file" hidden>
                                    </div>
                                    <div class="input-group">
                                        <input type="text" class="form-control
                                                @error('reply_ticket') is-invalid @enderror"
                                               name="message" value="{{old('message')}}" autocomplete="off"/>
                                        <button class="submit-btn" type="submit" name="reply_ticket" value="1">
                                            <i class="bi-send"></i>
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
        </div>
    </div>

    <!-- Close Modal -->
    <div class="modal fade" id="CloseTicketModal" tabindex="-1" role="dialog" aria-labelledby="CloseTicketModalLabel"
         aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title" id="accountAddCardModalLabel">
                        <i class="bi bi-check2-square"></i> @lang("Confirmation")</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <span>@lang("Do you want to closed this ticket?")</span>
                </div>
                <form action="{{ userRoute('ticket.reply', $ticket->id) }}" method="post" class="setRoute">
                    @csrf
                    @method('PUT')
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang("Close")</button>
                        <button type="submit" name="reply_ticket"
                                value="2" class="btn btn-primary">@lang("Confirm")</button>
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
    <script src="{{ asset('assets/admin/js/filepond-plugin-file-validate-size.js') }}"></script>
    <script src="{{ asset('assets/admin/js/filepond-plugin-file-validate-type.js') }}"></script>
    <script src="{{ asset('assets/admin/js/custom-filepond.js') }}" defer></script>
@endpush



@push('script')
    <script>
        'use strict';
        $('.filepond').filepond();
        $.fn.filepond.registerPlugin();
        $(document).ready(function () {
            let maxFiles = 1, filesize = '', fileType = '', oldRawFiles = '', oldFiles = [];

            maxFiles = $('.filepond').data('maxfiles');
            filesize = $('.filepond').data('filesize');
            fileType = $('.filepond').data('filetype');

            $('.filepond').filepond({
                allowFileTypeValidation: true,
                acceptedFileTypes: fileType,
                maxFiles: maxFiles,
                allowFileSizeValidation: filesize ? true : false,
                maxFileSize: filesize,
                labelMaxFileSizeExceeded: 'File is too large',
            });

            let fileSelectors = document.querySelectorAll('.filepond ~ .filepond-files');
            for (let i = 0; i < fileSelectors.length; i++) {
                $('.filepond').on('FilePond:updatefiles', function (e) {
                    $('.filepond--data').remove();
                    const files = $(this).filepond('getFiles');
                    let dt = new DataTransfer();
                    $(files).each(function (index) {
                        if (files[index].file instanceof File) {
                            dt.items.add(files[index].file);
                        } else if (files[index].file instanceof Blob) {
                            dt.items.add(new File([files[index].file], files[index].file.name, {type: files[index].file.type}));
                        }
                    });
                    fileSelectors[i].files = dt.files;
                });
            }
        });
    </script>
@endpush



