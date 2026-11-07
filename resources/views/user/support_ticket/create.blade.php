@extends(userLayout())
@section('title',__('New Ticket'))

@section('content')

    <div class="content {{ containerClass() }}">
        <div class="page-header border-0 mb-0">
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
                    <h1 class="page-header-title">@lang("New Ticket")</h1>
                </div>
            </div>
        </div>

        <div class="row d-flex justify-content-center">
            <div class="col-md-8">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">

                        <div class="card-header card-header-content-md-between">
                            <div class="mb-2 mb-md-0">
                                <h4 class="card-header-title">@lang('Create New Ticket')</h4>
                            </div>
                        </div>

                        <div class="card-body">
                            <div class="profile-form-section">
                                <div class="row g-3">
                                    <form action="{{userRoute('ticket.store')}}" method="post" enctype="multipart/form-data">
                                        @csrf
                                        <div class="col-md-12">
                                            <label for="subject" class="form-label">@lang('Subject')</label>
                                            <input type="text" name="subject" placeholder="@lang('Subject')"
                                                   value="{{ old('subject') }}"
                                                   class="form-control @error('subject') is-invalid @enderror">
                                            <div class="invalid-feedback">
                                                @error('subject') @lang($message) @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-4">
                                            <label for="message" class="form-label">@lang('Message')</label>
                                            <textarea name="message" rows="5"
                                                      class="form-control @error('note') is-invalid @enderror">{{ old('message') }}</textarea>
                                            <div class="invalid-feedback">
                                                @error('message') @lang($message) @enderror
                                            </div>
                                        </div>
                                        <div class="col-md-12 mt-4">
                                            <label class="avatar avatar-xl avatar-square" for="avatarUploader">
                                                <img id="avatarImg" class="avatar-img img-profile-view" src="{{getFile('local','dummy')}}"
                                                     alt="...">
                                            </label>

                                            <div class="d-flex align-items-center mt-2">
                                                <div class="d-flex gap-3 col-md-4">
                                                    <input class="form-control file-upload-input" name="attachments[]"
                                                           type="file" id="formFile" multiple>
                                                    <button type="button" class="btn btn-white btn-sm reset">@lang('Reset')</button>
                                                </div>
                                            </div>

                                            <p class="text-danger select-files-count"></p>
                                            @error('attachments')
                                            <div class="error text-danger"> @lang($message) </div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary mt-3">
                                            @lang('Submit Ticket')
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script>
        'use strict';
        $(document).on('change', '.file-upload-input', function () {
            let _this = $(this);
            let reader = new FileReader();
            reader.readAsDataURL(this.files[0]);
            reader.onload = function (e) {
                $('.img-profile-view').attr('src', e.target.result);
            }
            var fileCount = $(this)[0].files.length;
            $('.select-files-count').text(fileCount + ' file(s) selected');
        });
        $(document).on('click', '.reset', function () {
            let img = "{{asset(config('location.default'))}}"
            $('.img-profile-view').attr('src', img);
            $('.select-files-count').text("");
        });
    </script>
@endpush
