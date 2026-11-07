@extends('admin.layouts.app')
@section('page_title', __('Dashboard'))
@section('content')
    <div class="content container-fluid dashboard-height">

        @if($firebaseNotify)
            @include('admin.partials.dashboard.firebase_notification')
        @endif

        <div class="row">
            @include('admin.partials.dashboard.recentTran')
            @include('admin.partials.dashboard.record')
        </div>

        @if($wallets && $wallets->isNotEmpty())
            @include('admin.partials.dashboard.user_wallets')
        @endif


        <div class="row g-3 mt-2">
            @if($basic->deposit)
                <div class="col-md-6">
                    @include('admin.partials.dashboard.deposit_chart')
                </div>
            @endif
            <div class="col-md-6">
                @include('admin.partials.dashboard.send_money_summary')
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                @include('admin.partials.dashboard.request_money_summary')
            </div>
            @if($basic->payout)
                <div class="col-md-6">
                    @include('admin.partials.dashboard.payout_chart')
                </div>
            @endif
        </div>

        <div class="row">
            <div class="col-md-6">
                @include('admin.partials.dashboard.latest_users')
            </div>
            <div class="col-md-6">
                @include('admin.partials.dashboard.user_overview')
            </div>
        </div>


        @include('admin.partials.dashboard.browserHistory')


    </div>

    @if($basicControl->is_active_cron_notification)
        <!-- Modal -->
        <div class="modal fade" id="cron-info" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
             aria-labelledby="staticBackdropLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="staticBackdropLabel"><i class="fal fa-info-circle"></i>
                            @lang('Cron Job Set Up Instruction')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-12">
                                <p class="bg-orange text-white p-2">
                                    <i>@lang('**To sending emails and manage records automatically you need to setup cron job in your server. Make sure your job is running properly. We insist to set the cron job time as minimum as possible.**')</i>
                                </p>
                            </div>
                            <div class="col-md-12 form-group">
                                <label><strong>@lang('Command for Email')</strong></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control copyText"
                                           value="curl -s {{ route('queue.work') }}" disabled>
                                    <button class="input-group-text bg-primary btn btn-primary text-white copy-btn"
                                            id="button-addon2">
                                        <i class="fas fa-copy"></i></button>

                                </div>
                            </div>
                            <div class="col-md-12 form-group">
                                <label><strong>@lang('Command for Cron Job')</strong></label>
                                <div class="input-group mb-3">
                                    <input type="text" class="form-control copyText"
                                           value="curl -s {{ route('schedule:run') }}"
                                           disabled>
                                    <button class="input-group-text bg-primary btn btn-primary text-white copy-btn"
                                            id="button-addon2">
                                        <i class="fas fa-copy"></i></button>
                                </div>
                            </div>
                            <div class="col-md-12 text-center">
                                <p class="bg-dark text-white p-2">
                                    @lang('*To turn off this pop up go to ')
                                    <a href="{{route('admin.basic.control')}}"
                                       class="text-danger">@lang('Basic control')</a>
                                    @lang('and disable `Cron Set Up Pop Up`.*')
                                </p>
                            </div>

                            <div class="col-md-12">
                                <p class="text-muted"><span class="text-secondary font-weight-bold">@lang('N.B'):</span>
                                    @lang('If you are unable to set up cron job, Here is a video tutorial for you')
                                    <a href="https://www.youtube.com/watch?v=wuvTRT2ety0" target="_blank"><i
                                            class="fab fa-youtube"></i> @lang('Click Here') </a>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @include('admin.user_management.components.login_as_user')
    @include('admin.user_management.components.update_balance_modal')

@endsection

@push('js-lib')
    <script src="{{ asset('assets/admin/js/chart.min.js') }}"></script>
    <script src="{{ asset('assets/global/js/apexcharts.min.js') }}"></script>
@endpush

@push("script")
    <script>

        $(document).on('click', '.loginAccount', function () {
            let route = $(this).data('route');
            $('.loginAccountAction').attr('action', route)
        });
        $(document).on('click', '.addBalance', function (){
            $('.setBalanceRoute').attr('action', $(this).data('route'));
            $('.user-balance').text($(this).data('balance'));
        });
        document.querySelectorAll('.js-chart').forEach(item => {
            HSCore.components.HSChartJS.init(item)
        });
        $(document).ready(function () {
            let isActiveCronNotification = '{{ $basicControl->is_active_cron_notification }}';
            if (isActiveCronNotification == 1)
                $('#cron-info').modal('show');
            $(document).on('click', '.copy-btn', function () {
                var _this = $(this)[0];
                var copyText = $(this).siblings('input');
                $(copyText).prop('disabled', false);
                copyText.select();
                document.execCommand("copy");
                $(copyText).prop('disabled', true);
                $(this).text('Coppied');
                setTimeout(function () {
                    $(_this).text('');
                    $(_this).html('<i class="fas fa-copy"></i>');
                }, 500)
            });
        });
    </script>
@endpush













