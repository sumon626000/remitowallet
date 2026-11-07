@extends(userLayout())
@section('title', trans('Dispute Conversion'))
@section('content')
    <div class="content {{ containerClass() }}">

        <x-page-header menu="Dispute" />

        <div class="card message_section">
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
                                @if(isset($dispute) && (($escrow->sender_id == Auth::id() && $dispute->defender_reply_yn == 0) || ($escrow->receiver_id == Auth::id() && $dispute->claimer_reply_yn == 0)))
                                    <h1 class="text-danger text-center mb-3">
                                        <i class="fas fa-exclamation-triangle muted-icon-size"></i> @lang('You are muted')
                                    </h1>
                                @else
                                    <div class="typing-area">
                                        <form action="{{ route('user.dispute.view', $dispute->utr) }}"
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
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';

        function downloadFile(url, filename = 'dispute_file') {
            const link = document.createElement('a');
            link.href = url;
            link.download = filename;

            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }
    </script>
@endpush
