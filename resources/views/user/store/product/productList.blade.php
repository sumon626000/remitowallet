@extends(userLayout())
@section('title', __('Store Product List'))
@section('content')
    <div class="content {{ containerClass() }}">
        <x-page-header menu="Store" :statBtn="true"/>

        <div class="row mb-3 d-none" id="statsSection">
            <x-stats-card
                title="Active Products"
                :value="$products['activeProduct'] ?? 0"
                :total="$products['totalProduct'] ?? 0"
                :percentage="$products['activeProductPercentage'] ?? 0"
                icon="bi-check2-circle"
                color="success"
            />
            <x-stats-card
                title="In-Active Products"
                :value="$products['inactiveProduct'] ?? 0"
                :total="$products['totalProduct'] ?? 0"
                :percentage="$products['inactiveProductPercentage'] ?? 0"
                icon="bi-slash-circle"
                color="danger"
            />
            <x-stats-card
                title="Today Created Products"
                :value="$products['todayProduct'] ?? 0"
                :total="$products['totalProduct'] ?? 0"
                :percentage="$products['todayProductPercentage'] ?? 0"
                icon="bi-calendar-day"
                color="info"
            />
            <x-stats-card
                title="This Month Created Products"
                :value="$products['thisMonthProduct'] ?? 0"
                :total="$products['totalProduct'] ?? 0"
                :percentage="$products['thisMonthProductPercentage'] ?? 0"
                icon="bi-calendar-month"
                color="primary"
            />
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header card-header-content-md-between">
                        <div class="mb-2 mb-md-0">
                            <form method="GET" action="{{ route('user.product.list') }}" id="productSearchForm">
                                <div class="input-group input-group-merge navbar-input-group">
                                    <div class="input-group-prepend input-group-text">
                                        <i class="bi-search"></i>
                                    </div>
                                    <input type="search" name="search" class="form-control form-control-sm"
                                           value="{{ request('search') }}"
                                           placeholder="@lang('Search Product')" aria-label="@lang('Search Product')"
                                           autocomplete="off">
                                    @if(request('search'))
                                        <a class="input-group-append input-group-text"
                                           href="{{ route('user.product.list', collect(request()->query())->except('search')->toArray()) }}">
                                            <i class="bi-x"></i>
                                        </a>
                                    @endif
                                </div>
                                @foreach(['filter_name', 'filter_category', 'filter_status', 'filter_date', 'per_page'] as $param)
                                    @if(request()->filled($param))
                                        <input type="hidden" name="{{ $param }}" value="{{ request($param) }}">
                                    @endif
                                @endforeach
                            </form>
                        </div>

                        <div class="d-grid d-sm-flex justify-content-md-end align-items-sm-center gap-2">
                            <a href="{{route('user.product.create')}}" class="btn btn-sm btn-primary">
                                @lang('Add Product')
                            </a>

                            <x-offcanvas.filter-btn/>
                            <x-offcanvas.layout id="filterOffcanvas">
                                <x-offcanvas.filter-field label="Name" name="filter_name" type="text"
                                                          id="filter_name" placeholder="Gaming Pc"/>
                                <x-offcanvas.filter-field label="Category" name="filter_category" type="select"
                                                          id="filter_category"
                                                          :options="$categories->pluck('name', 'id')->toArray()"/>
                                <x-offcanvas.filter-field label="Status" name="filter_status" type="select"
                                                          :options="[ '1' => 'Active', '0' => 'Inactive' ]"
                                                          :indicators="[ '0'=>'bg-warning', '1'=>'bg-success']"
                                />
                                <x-offcanvas.filter-field label="Date" name="filter_date" type="date" mode="range"/>
                                @if(request('search'))
                                    <input type="hidden" name="search" value="{{ request('search') }}">
                                @endif
                                @if(request('per_page'))
                                    <input type="hidden" name="per_page" value="{{ request('per_page') }}">
                                @endif
                            </x-offcanvas.layout>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">
                            @forelse($productItems as $product)
                                @php
                                    $thumb = getFile($product->driver, $product->thumbnail);
                                    $price = currencyPosition($product->price, optional($product->user)->sotre_currency_id);
                                @endphp
                                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6">
                                    <div class="card card-bordered shadow-none h-100 product-grid-card">
                                        <div class="position-relative">
                                            <a href="{{ route('user.product.view', $product->id) }}"
                                               class="d-block product-grid-thumb">
                                                <img src="{{ $thumb }}" alt="{{ $product->name }}"
                                                     class="card-img-top product-grid-img">
                                            </a>
                                            <span class="position-absolute top-0 end-0 m-2">
                                                @if($product->status == 1)
                                                    <span class="badge bg-soft-success text-success">
                                                        <span class="legend-indicator bg-success"></span>@lang('Active')
                                                    </span>
                                                @else
                                                    <span class="badge bg-soft-danger text-danger">
                                                        <span class="legend-indicator bg-danger"></span>@lang('In-Active')
                                                    </span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="card-body d-flex flex-column">
                                            <small class="text-muted text-uppercase mb-1">
                                                {{ optional($product->category)->name ?? '—' }}
                                            </small>
                                            <h5 class="card-title mb-2 text-truncate" title="{{ $product->name }}">
                                                <a href="{{ route('user.product.view', $product->id) }}"
                                                   class="text-inherit">{{ $product->name }}</a>
                                            </h5>
                                            <div class="d-flex justify-content-between align-items-center mt-auto pt-2">
                                                <span class="amount-highlight fw-semibold">{{ $price }}</span>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ route('user.product.view', $product->id) }}"
                                                       class="btn btn-white btn-sm">
                                                        <i class="bi-eye me-1"></i> @lang('View')
                                                    </a>
                                                    <div class="btn-group">
                                                        <button type="button"
                                                                class="btn btn-white btn-icon btn-sm dropdown-toggle dropdown-toggle-empty"
                                                                data-bs-toggle="dropdown" aria-expanded="false"></button>
                                                        <div class="dropdown-menu dropdown-menu-end mt-1">
                                                            <a class="dropdown-item"
                                                               href="{{ route('user.product.edit', $product->id) }}">
                                                                <i class="bi-pencil-square dropdown-item-icon"></i> @lang('Edit')
                                                            </a>
                                                            <a class="dropdown-item deleteProduct" href="#"
                                                               data-route="{{ route('user.product.delete', $product->id) }}"
                                                               data-bs-target="#productDelete" data-bs-toggle="modal">
                                                                <i class="bi-trash dropdown-item-icon"></i> @lang('Delete')
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="col-12">
                                    <div class="text-center p-5">
                                        <img class="dataTables-image mb-3"
                                             src="{{ asset('assets/admin/img/oc-error.svg') }}"
                                             alt="@lang('No data')" data-hs-theme-appearance="default">
                                        <img class="dataTables-image mb-3"
                                             src="{{ asset('assets/admin/img/oc-error-light.svg') }}"
                                             alt="@lang('No data')" data-hs-theme-appearance="dark">
                                        <p class="mb-0">@lang('No data to show')</p>
                                    </div>
                                </div>
                            @endforelse
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="row justify-content-center justify-content-sm-between align-items-sm-center">
                            <div class="col-sm mb-2 mb-sm-0">
                                <div class="d-flex justify-content-center justify-content-sm-start align-items-center">
                                    <span class="me-2">@lang('Showing:')</span>
                                    <div class="tom-select-custom">
                                        <select id="perPageSelect"
                                                class="js-select form-select form-select-borderless w-auto"
                                                autocomplete="off"
                                                data-hs-tom-select-options='{
                                                        "searchInDropdown": false,
                                                        "hideSearch": true
                                                      }'>
                                            <option value="12" {{ ($perPage ?? 12) == 12 ? 'selected' : '' }}>12</option>
                                            <option value="24" {{ ($perPage ?? 12) == 24 ? 'selected' : '' }}>24</option>
                                            <option value="48" {{ ($perPage ?? 12) == 48 ? 'selected' : '' }}>48</option>
                                            <option value="96" {{ ($perPage ?? 12) == 96 ? 'selected' : '' }}>96</option>
                                        </select>
                                    </div>
                                    <span class="text-secondary me-2">@lang('of')</span>
                                    <span>{{ $productItems->total() }}</span>
                                </div>
                            </div>
                            <div class="col-sm-auto">
                                <div class="d-flex justify-content-center justify-content-sm-end">
                                    {{ $productItems->appends(request()->query())->links('user.partials.pagination') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('user.store.product.modal')
@endsection

@push('style')
    <style>
        .product-grid-card {
            transition: transform .15s ease-in-out, box-shadow .15s ease-in-out;
        }
        .product-grid-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 .5rem 1.25rem rgba(140, 152, 164, .15);
        }
        .product-grid-thumb {
            display: block;
            aspect-ratio: 1 / 1;
            overflow: hidden;
            background-color: #f8f9fa;
        }
        .product-grid-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
    </style>
@endpush

<x-assets :counter="true"/>

@push('script')
    <script>
        $(document).on('change', '#perPageSelect', function () {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', $(this).val());
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });

        $(document).on('click', '.deleteProduct', function () {
            var route = $(this).data('route');
            $('.deleteProductForm').attr('action', route);
        });
    </script>
@endpush