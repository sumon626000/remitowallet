@extends(userLayout())
@section('title',__('QR Code'))

@section('content')
    <div class="content {{ containerClass() }}">
        <div class="row d-flex justify-content-center">
            <div class="col-lg-6 col-md-6 mb-3">
                <div class="qr-wrapper">
                    <div class="qr-box">
                        <div class="bg-layer" style="background: url('{{ asset('assets/user/img/qr_bg.png') }}');"></div>

                        <h5>@lang('QR Code')</h5>

                        <input type="hidden" id="qrUrl" value="{{route('public.qr.Payment', auth()->user()?->qr_link ?? 'S6AMU8A41RUHGS6TWGMS')}}">

                        <div id="qrcode" class="mx-auto mb-3 "></div>

                        <a href="" id="download-qr" class="btn btn-qr" download="{{ auth()->user()->username . '_qr.png' }}">
                            <i class="bi-download"></i> {{ __('Download') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@push('script')
    <script src="{{ asset('assets/user/js/qrjs2.min.js') }}"></script>
    <script>
        var qr = QRCode.generatePNG(document.getElementById('qrUrl').value, {
            ecclevel: "M",
            format: "html",
            margin: 4,
            modulesize: 8
        });

        var img = document.createElement("img");
        img.src = qr;
        document.getElementById('qrcode').appendChild(img);

        //For download
        var download = document.getElementById('download-qr').href = qr;
    </script>
@endpush

