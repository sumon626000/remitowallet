@extends(userLayout())
@section('title', __('Shipping Charge'))
@section('content')
    <div id="category" v-cloak>
        <div class="content {{ containerClass() }}">
            <x-page-header menu="Store"/>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">
                        <!-- Header -->
                        <div class="card-header">
                            <div class="row justify-content-between align-items-center flex-grow-1">
                                <div class="col-12 col-md">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h5 class="card-header-title">@lang('Shipping Charge')</h5>
                                        <a href="javascript:void(0)" class="btn btn-sm btn-primary"
                                           data-bs-target="#addShipping" @click="makeEmpty" data-bs-toggle="modal">
                                            @lang('Add Charge')
                                        </a>
                                    </div>
                                </div>

                                <div class="col-auto">
                                    <form>
                                        <div class="input-group input-group-merge input-group-flush">
                                            <div class="input-group-prepend input-group-text">
                                                <i class="bi-search"></i>
                                            </div>
                                            <input id="datatableWithSearchInput" type="search" class="form-control"
                                                   placeholder="@lang('Search here')">
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <!-- End Header -->

                        <!-- Table -->
                        <div class="table-responsive datatable-custom">
                            @if(count($shipping) > 0)
                                <table
                                    class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                    data-hs-datatables-options='{
                                       "order": [],
                                       "search": "#datatableWithSearchInput",
                                       "isResponsive": false,
                                       "isShowPaging": false,
                                       "pagination": "datatableWithSearchPagination"
                                     }'>
                                    <thead class="thead-light">
                                    <tr>
                                        <th>@lang('SL.')</th>
                                        <th>@lang('Store')</th>
                                        <th>@lang('Address')</th>
                                        <th>@lang('Cost')</th>
                                        <th>@lang('Place Orders')</th>
                                        <th>@lang('Status')</th>
                                        <th>@lang('Action')</th>
                                    </tr>
                                    </thead>

                                    <tbody>
                                    @foreach($shipping as $key => $item)
                                        <tr>
                                            <td data-label="@lang('SL.')">{{++$key}}</td>
                                            <td class="table-column-ps-0">
                                                <a class="d-flex align-items-center"
                                                   href="{{route('user.store.edit',$item->store_id)}}">

                                                    <div class="flex-grow-1 ms-3">
                                                        <h5 class="text-inherit mb-0">{{$item->store?->name}}</h5>
                                                    </div>
                                                </a>
                                            </td>
                                            <td>{{ $item->address }}</td>
                                            <td class="fw-semibold text-danger">
                                                {{ currencyPosition($item->charge,auth()->user()?->storeCurrency?->id) }}
                                            </td>
                                            <td>
                                                <span class="badge bg-soft-dark text-dark">
                                                    {{$item->orders_count}}
                                                </span>
                                            </td>
                                            <td>
                                                @if($item->status == 1)
                                                    <span class="badge bg-soft-success text-success">
                                                        <span class="legend-indicator bg-success"></span>@lang('Active')
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">
                                                        <span class="legend-indicator bg-danger"></span>@lang('In-Active')
                                                    </span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group " role="group">
                                                    <a href="javascript:void(0)"
                                                       class="btn btn-white btn-sm" @click="edit({{$item}})"
                                                       data-bs-target="#editCategory" data-bs-toggle="modal"
                                                    >
                                                        <i class="bi-pencil-square me-1"></i> @lang('Edit')
                                                    </a>
                                                    <div class="btn-group ">
                                                        <button type="button" class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty" id="userEditDropdown" data-bs-toggle="dropdown" aria-expanded="false"></button>
                                                        <div class="dropdown-menu dropdown-menu-end mt-1" aria-labelledby="userEditDropdown" style="">
                                                            <a class="dropdown-item deleteShipping" href="#"
                                                               data-bs-target='#categoryDelete' data-bs-toggle='modal'
                                                               data-route='{{route('user.shipping.delete',$item->id)}}'
                                                            >
                                                                <i class="bi-trash dropdown-item-icon"></i> @lang('Delete')
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    </tbody>
                                </table>
                            @else
                                <div class="text-center p-4">
                                    <img class="dataTables-image mb-3"
                                         src="{{ asset('assets/admin/img/oc-error.svg') }}" alt="Image Description"
                                         data-hs-theme-appearance="default">
                                    <img class="dataTables-image mb-3"
                                         src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                                         alt="Image Description" data-hs-theme-appearance="dark">
                                    <p class="mb-0">@lang('No data to show')</p>
                                </div>
                            @endif
                        </div>
                        <!-- End Table -->

                        <div class="card-footer">
                            <div class="d-flex justify-content-center justify-content-sm-end">
                                <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        @include('user.store.shipping.modal')
    </div>
@endsection

@push('css-lib')
    <script src="{{ asset("assets/admin/js/hs-file-attach.min.js") }}"></script>
    <link rel="stylesheet" href="{{ asset('assets/admin/css/tom-select.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/admin/css/flatpickr.min.css') }}">
@endpush

@push('js-lib')
    <script src="{{ asset('assets/admin/js/tom-select.complete.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/flatpickr.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/select.min.js') }}"></script>
@endpush

@push('script')
    <script>
        'use strict'

        var newApp = new Vue({
            el: "#category",
            data: {
                item: {
                    address: "", status: "", id: "", store: "", charge: ""
                },
                stores: [],
                addressError: "", storeError: "", chargeError: ""
            },
            mounted() {
                this.item.status = 1;
                this.stores = @json($stores);
            },
            methods: {
                save() {
                    this.makeError();
                    axios.post("{{ route('user.shipping.save') }}", this.item)
                        .then(function (response) {
                            console.log(response)
                            if (response.data.status == 'success') {
                                window.location.href = response.data.url;
                            }
                        })
                        .catch(function (error) {
                            let errors = error.response.data;
                        });
                },
                edit(obj) {
                    this.item.address = obj.address;
                    this.item.id = obj.id;
                    this.item.status = obj.status;
                    this.item.store = obj.store.id;
                    this.item.charge = obj.charge;
                },
                update() {
                    this.makeError();
                    axios.post("{{ route('user.shipping.update') }}", this.item)
                        .then(function (response) {
                            if (response.data.status == 'success') {
                                window.location.href = response.data.url;
                            }
                        })
                        .catch(function (error) {
                            let errors = error.response.data;
                        });
                },
                makeError() {
                    if (!this.item.address) {
                        this.addressError = "Address Field is required"
                    }
                    if (!this.item.store) {
                        this.storeError = "Store Field is required"
                    }

                    if (!this.item.charge) {
                        this.chargeError = "Charge Field is required"
                    }
                },
                makeEmpty() {
                    this.item.address = "";
                    this.item.store = "";
                    this.item.charge = "";
                    this.item.status = 1;
                    this.addressError = "";
                    this.storeError = "";
                    this.chargeError = "";
                }
            },
        })

        $(document).on('click', '.deleteShipping', function () {
            var route = $(this).data('route');
            $('.deleteShippingForm').attr('action', route);
        })

        HSCore.components.HSDatatables.init('.js-datatable')

    </script>
@endpush
