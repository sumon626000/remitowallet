@extends('admin.layouts.app')
@section('page_title',__('Subscribers'))
@section('content')
    <div class="content container-fluid">
        <div class="page-header">
            <div class="row align-items-end">
                <div class="col-sm mb-2 mb-sm-0">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb breadcrumb-no-gutter">
                            <li class="breadcrumb-item">
                                <a class="breadcrumb-link" href="{{route('admin.dashboard')}}">@lang('Dashboard')</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">@lang('Subscriber')</li>
                        </ol>
                    </nav>
                    <h1 class="page-header-title">@yield('page_title')</h1>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2 class="card-title h4 mt-2">@lang('Subscriber List')</h2>
                <a href="{{route('admin.subscriber.mail')}}" class="btn btn-sm btn-info">
                     {{ trans('Send Email') }}
                </a>
            </div>
            <table class="table table-borderless table-thead-bordered">
                <thead class="thead-light">
                <tr>
                    <th scope="col">@lang('Serial No.')</th>
                    <th scope="col">@lang('Subscriber Email')</th>
                    <th scope="col">@lang('Subscriber Join Date')</th>
                    <th scope="col">@lang('Action')</th>
                </tr>
                </thead>
                <tbody>
                @forelse($subscriber as $key=>$item)
                    <tr>
                        <td>{{ $key+1 }}</td>
                        <td>{{ $item->email }}</td>
                        <td>{{ dateTime($item->created_at) }}</td>
                        <td>
                            <button
                                class="btn btn-white btn-sm notiflix-confirm" title="@lang('Delete')"
                                data-bs-toggle="modal" data-bs-target="#delete"
                                data-route="{{route('admin.subscriber.destroy',$item->id)}}">
                                <i class="bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                @empty
                    @include('empty')
                @endforelse
                </tbody>

            </table>
            <!-- End Table -->
            <div class="card-footer">
                <div class="row d-flex justify-content-end">
                    {{ $subscriber->appends($_GET)->links($theme.'partials.pagination') }}
                </div>
            </div>

        </div>
    </div>
@endsection

@push('loadModal')

    <!-- Delete Modal -->
    <div class="modal fade" id="delete" data-bs-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="delete-modal">{{ trans('Delete Subscriber') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>@lang('Are you sure to remove this subscriber?')</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                    <form action="" method="post" class="deleteRoute">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-soft-danger">@lang('Yes')</button>
                    </form>
                </div>
            </div>

        </div>
    </div>
    <!-- End Modal -->
@endpush


@push('script')

    <script>
        $(document).ready(function () {
            $('.notiflix-confirm').on('click', function () {
                let route = $(this).data('route');
                $('.deleteRoute').attr('action', route)
            })
        });
    </script>

@endpush
