@extends(userLayout())
@section('title',trans('KYC Verification Center'))
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
                    <h1 class="page-header-title">@lang("Verification Center")</h1>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="d-grid gap-3 gap-lg-5">
                    <div class="card">
                        <div class="card-header card-header-content-md-between">
                            <div class="mb-2 mb-md-0">
                                <h4 class="card-header-title">@lang('Kyc Verification List')</h4>
                            </div>
                            <div class="btn-area">
                                <a class="btn btn-white btn-sm" href="{{ route('user.kyc.history') }}">
                                    <i class="bi bi-arrow-clockwise"></i> @lang('History')
                                </a>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table">
                                <thead class="thead-light">
                                <tr>
                                    <th class="text-center w-10" scope="col">@lang('SL')</th>
                                    <th class="text-center w-50" scope="col">@lang('Type')</th>
                                    <th class="text-center w-40" scope="col">@lang('Action')</th>
                                </tr>
                                </thead>
                                <tbody class="js-sortable">
                                @forelse($kyc as $key => $item)
                                    <tr>
                                        <td class="text-center" data-label="@lang('SL')">
                                            {{ $loop->iteration }}
                                        </td>
                                        <td class="text-center" data-label="@lang('Kyc Type')">
                                            <div class="font-weight-bold">
                                                {{ $item->name }}
                                            </div>
                                        </td>
                                        <td class="text-center" data-label="@lang('Action')">
                                            <a class="btn btn-white btn-sm"
                                               href="{{ route('user.verification.kyc.form', $item->id) }}">
                                                <i class="bi bi-pencil-square"></i> @lang('Verify')
                                            </a>
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

@endsection



