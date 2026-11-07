@extends(userLayout())
@section('title', trans('Preview Escrow'))
@section('content')
    <div class="content {{ containerClass() }}">

        <x-page-header menu="Escrow"/>

        <div class="row d-flex justify-content-center">
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header card-header-content-between">
                        <h4 class="card-header-title">@lang('Preview Escrow') </h4>
                    </div>
                    <form action="{{ route('user.escrow.confirmInit',$utr) }}" method="post">
                        @csrf
                        <div class="card-body">

                            <div class="list-group list-group-flush list-group-no-gutters">
                                <x-list-item label="Request Amount" value="{{ getAmount($escrow->transfer_amount) }} {{ $escrow->currency?->code }}" class="fw-semibold"/>
                                <x-list-item label="Total charge" value="{{ getAmount($escrow->charge) }} {{ $escrow->currency?->code }}" class=" fw-semibold text-danger"/>
                                <x-list-item label="Receivable Amount" value="{{ getAmount($escrow->received_amount) }} {{ $escrow->currency?->code }}" class="fw-semibold text-success"/>
                                <x-list-item label="Note" value="{{ __($escrow->note) }}" class=""/>

                                @if($escrow->file)
                                    <li class="list-group-item d-flex justify-content-between align-items-start">
                                        <div class="ms-2 me-auto">
                                            <div class="fw-semibold">@lang('Attachment')</div>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-secondary"
                                                onclick="downloadURI('{{getFile($escrow->driver,$escrow->file)}}')">
                                            <i class="bi-download"></i> @lang('Download')</button>
                                    </li>
                                @endif
                            </div>

                            @if(in_array('escrow',$enable_for))
                                <div class="row mt-4">
                                    <div>
                                        <label for="security_pin" class="form-label">@lang('Security Pin')</label>
                                        <input type="password" class="form-control" name="security_pin"
                                               id="security_pin" placeholder="@lang('Please enter your security PIN')"
                                               value="{{ old('security_pin') }}">
                                    </div>
                                    @error('security_pin')<span class="text-danger">{{$message}}</span>@enderror
                                </div>
                            @endif
                            <button id="submit" type="submit" class="btn btn-primary mt-4">@lang('Confirm')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        'use strict';

        function downloadURI(uri, name = 'escrow') {
            var link = document.createElement("a");
            link.setAttribute('download', name);
            link.href = uri;
            document.body.appendChild(link);
            link.click();
            link.remove();
        }
    </script>
@endpush
