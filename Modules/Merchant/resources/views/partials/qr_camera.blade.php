@push('loadModal')
    <div id="qrScanModal" class="modal" tabindex="-1" style="display:none;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">@lang('Scan Agent QR Code')</h5>
                    <button type="button" class="btn-close" onclick="closeQrModal()"></button>
                </div>
                <div class="modal-body">
                    <div id="qr-reader" style="width: 100%;"></div>
                </div>
                <div class="modal-footer d-flex justify-content-center">
                    <button type="button" class="btn btn-sm btn-white" onclick="closeQrModal()">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>
@endpush

@push('js-lib')
    <script src="https://unpkg.com/html5-qrcode"></script>
@endpush

@push('script')
    <script>
        let html5QrCode;
        function startQrScan() {
            html5QrCode = new Html5Qrcode("qr-reader");
            navigator.mediaDevices.getUserMedia({video: true})
                .then(() => {
                    showQrModal();

                    Html5Qrcode.getCameras().then(devices => {
                        if (devices.length) {
                            html5QrCode.start(
                                devices[1].id,
                                {fps: 10, qrbox: 250},
                                qrCodeMessage => {
                                    document.getElementById("recipient").value = qrCodeMessage.trim();
                                    stopScan();
                                    checkRecipient()
                                },
                                errorMessage => {
                                    console.warn(errorMessage);
                                }
                            );
                        } else {
                            alert("No camera found.");
                            closeQrModal();
                        }
                    });
                })
                .catch(() => {
                    alert("No camera found.");
                });
        }

        function stopScan() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    closeQrModal();
                });
            }
        }

        function showQrModal() {
            document.getElementById("qrScanModal").style.display = "flex";
        }

        function closeQrModal() {
            document.getElementById("qrScanModal").style.display = "none";
            if (html5QrCode?.isScanning) {
                html5QrCode.stop();
            }
        }
    </script>
@endpush

@push('style')
    <style>
        .modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1050;
        }

        .modal-dialog {
            width: 90% !important;
            max-width: 500px !important;
        }
    </style>

@endpush

