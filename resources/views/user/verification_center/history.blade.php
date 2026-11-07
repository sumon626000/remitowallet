@extends(userLayout())
@section('title',__('Verification History'))
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
                            <li class="breadcrumb-item active" aria-current="page">@lang("Settings")</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@lang("KYC Verification History")</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <div class="mb-2 mb-md-0">
                                <h4 class="card-header-title">@lang('Verification History')</h4>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th scope="col">@lang('SL')</th>
                                    <th scope="col">@lang('Type')</th>
                                    <th scope="col">@lang('Status')</th>
                                    <th scope="col">@lang('Submitted At')</th>
                                    <th scope="col">@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody class="js-sortable">
                                @forelse($userKyc as $key => $item)
                                    <tr>
                                        <td data-label="@lang('SL')"><span>{{++$key}}</span></td>
                                        <td data-label="@lang('Type')">
                                            <span>{{$item->kyc_type}}</span>
                                        </td>
                                        <td data-label="@lang('Status')">
                                            {!! $item->getStatus() !!}
                                        </td>
                                        <td data-label="@lang('Submitted At')">
                                            <span>{{dateTime($item->created_at,basicControl()->date_time_format)}}</span>
                                        </td>

                                        <td data-label="action">
                                            <button type="button" class="btn btn-white btn-icon btn-sm"
                                                    id="actionDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi-three-dots-vertical"></i>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="actionDropdown"
                                                 data-popper-placement="bottom-end">
                                                <a class="dropdown-item showDetails" href="javascript:void(0)"
                                                   data-bs-target="#modalShow" data-bs-toggle="modal"
                                                   data-res="{{json_encode($item->kycInfoShow())}}"
                                                   data-type="{{$item->kyc_type}}"
                                                >
                                                    <i class="bi bi-eye dropdown-item-icon"></i> @lang('View')
                                                </a>
                                                @if($item->status == 2)
                                                    <a class="dropdown-item showReason" href="javascript:void(0)"
                                                       data-bs-target="#modalReject" data-bs-toggle="modal"
                                                       data-reason="{{$item->reason}}"
                                                    ><i class="bi bi-recycle dropdown-item-icon"></i> @lang('Reason')
                                                    </a>
                                                @endif

                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    @include('empty')
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>


    {{--    Information show modal--}}
    <div class="modal fade" id="modalShow" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title modalTitle" id="staticBackdropLabel"></h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="list-group list-group-flush list-group-no-gutters listShow">

                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

    {{--    Rejected Reason show modal--}}
    <div class="modal fade" id="modalReject" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
         aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title" id="staticBackdropLabel">@lang('Rejected Reason')</h1>
                    <button type="button" class="btn btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="rejectedReason"></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@push('script')
    <script>
        'use strict'
        $(document).on("click", ".showReason", function () {
            $('.rejectedReason').text($(this).data('reason'));
        });

        $(document).on("click", ".showDetails", function () {
            $('.listShow').html('');
            var show = "";
            let res = $(this).data('res');
            $('.modalTitle').text($(this).data('type'));
            for (let key in res) {
                if (res[key].type == 'file') {
                    show += `<li class="list-group-item">
                                 <div class="d-flex justify-content-between align-items-center">
                                    <h5>${res[key].name}</h5>
                                       <ul class="list-unstyled list-py-2 text-body text-end">
                                          <li>
                                             <a href="${res[key].value}"
                                                 target="_blank">
                                                   <img
                                                   src="${res[key].value}"
                                                   class="w-50">
                                             </a>
                                          </li>
                                       </ul>
                                 </div>
                             </li>`;
                } else {
                    show += `<li class="list-group-item">
                            <div class="d-flex justify-content-between align-items-center">
                                <h6>${res[key].name}</h6>
                                <ul class="list-unstyled list-py-2 text-body">
                                    <li>${res[key].value}</li>
                                </ul>
                            </div>
                        </li>
                `;
                }
            }

            $('.listShow').html(show);
        });
    </script>
@endpush
