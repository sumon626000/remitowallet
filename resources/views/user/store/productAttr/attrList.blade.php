@extends(userLayout())
@section('title', __('Product Attributes'))

@section('content')
    <div class="content {{ containerClass() }}">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('user.dashboard')}}">@lang("Dashboard")</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Product Attributes')</li>
                        </ol>
                    </nav>
                    <div class="d-flex justify-content-between">
                        <h1 class="page-header-title">@lang('Product Attributes')</h1>
                        <a href="{{route('user.attr.create')}}" class="btn btn-primary">@lang('Add Attribute')</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <!-- Header -->
            <div class="card-header">
                <div class="row justify-content-between align-items-center flex-grow-1">
                    <div class="col-12 col-md">
                        <h3 class="page-header-title">@lang('Product Attributes')</h3>
                    </div>
                    <div class="col-auto">
                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <!-- Filter -->
                            <form>
                                <div class="input-group input-group-merge input-group-flush">
                                    <div class="input-group-prepend input-group-text">
                                        <i class="bi-search"></i>
                                    </div>
                                    <input id="datatableWithSearchInput" type="search" class="form-control"
                                           placeholder="@lang('Search Attributes')" >
                                </div>
                            </form>
                            <!-- End Filter -->
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Header -->

            <!-- Table -->
            <div class="table-responsive datatable-custom">
                <table
                    class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                    data-hs-datatables-options='{
                    "columnDefs": [{
                      "targets": [0],
                      "orderable": false
                   }],
                   "order": [],
                   "search": "#datatableWithSearchInput",
                   "isResponsive": false,
                   "isShowPaging": false,
                   "pagination": "datatableWithSearchPagination"
                 }'>
                    <thead class="thead-light">
                    <tr>
                        <th scope="col">@lang('SL No.')</th>
                        <th scope="col">@lang('Name')</th>
                        <th scope="col">@lang('Status')</th>
                        <th scope="col">@lang('Action')</th>
                    </tr>
                    </thead>

                    <tbody>
                    @forelse($storeProductAttrs as $item)
                        <tr>
                            <td data-label="@lang('SL No.')">{{ $loop->index + 1 }}</td>
                            <td data-label="@lang('Name')">
                                {{ $item->name }}
                            </td>
                            <td data-label="@lang('Status')">
                                @if($item->status)
                                    <span class="badge bg-soft-success text-success">
                                        <span class="legend-indicator bg-success"></span>@lang('Active')
                                    </span>
                                @else
                                    <span class="badge bg-soft-success text-danger">
                                        <span class="legend-indicator bg-danger"></span>@lang('Inactive')
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="btn-group " role="group">
                                    <a href="{{route('user.attr.edit',$item->id)}}"
                                       class="btn btn-white btn-sm edit_user_btn" title="view">
                                        <i class="bi-pencil-square me-1"></i> @lang('Edit')
                                    </a>
                                    <div class="btn-group ">
                                        <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown" style="">
                                            <a class="dropdown-item deleteAttr" href="#"
                                               data-bs-toggle="modal" data-bs-target="#attrDelete"
                                               data-route="{{route('user.attr.delete',$item->id)}}"
                                            >
                                                <i class="bi-trash dropdown-item-icon"></i> @lang('Delete')
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="100%">
                                <div class="text-center p-4">
                                    <img class="dataTables-image mb-3"
                                         src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description"
                                         data-hs-theme-appearance="default">
                                    <img class="dataTables-image mb-3"
                                         src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                                         alt="Image Description" data-hs-theme-appearance="dark">
                                    <p class="mb-0">@lang('No data to show')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            <!-- End Table -->

            <!-- Footer -->
            <div class="card-footer">
                <!-- Pagination -->
                <div class="d-flex justify-content-center justify-content-sm-end">
                    <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                </div>
                <!-- End Pagination -->
            </div>
            <!-- End Footer -->
        </div>
    </div>

    <div id="attrDelete" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title text-danger">@lang('Attribute Delete')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                @csrf
                <div class="modal-body">
                    <p>@lang('Are you want to delete this Attribute?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <form action="" method="post" class="deleteAttrForm">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('js-lib')
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
@endpush
@push('script')
    <script>
        HSCore.components.HSDatatables.init('.js-datatable', {})
        $(document).on('click', '.deleteAttr', function () {
            var route = $(this).data('route');
            $('.deleteAttrForm').attr('action', route);
        })

    </script>
@endpush
