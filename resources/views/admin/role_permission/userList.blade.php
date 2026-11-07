@extends('admin.layouts.app')
@section('page_title',__('Staff List'))

@section('content')
    <div id="manage-user-app">
        <div class="content container-fluid">
            <x-page-header menu="Roles & Permission"/>

            <div class="row">
                <div class="col-lg-12">
                    <div class="card">

                        <div class="card-header">
                            <div class="row justify-content-between align-items-center flex-grow-1">
                                <div class="col-auto">
                                    <form>
                                        <div class="input-group input-group-merge input-group-flush">
                                            <div class="input-group-prepend input-group-text">
                                                <i class="bi-search"></i>
                                            </div>
                                            <input id="datatableWithSearchInput" type="search" class="form-control" placeholder="Search here...">
                                        </div>
                                    </form>
                                </div>
                                <div class="col-auto">
                                    <button class="btn btn-sm btn-primary" data-bs-target="#add-modal" data-bs-toggle="modal"
                                            @click="makeDataEmpty">@lang('Add New')</button>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive datatable-custom">

                            <table class="js-datatable table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                                   data-hs-datatables-options='{
                                   "order": [],
                                   "ordering": false,
                                   "search": "#datatableWithSearchInput",
                                   "isResponsive": false,
                                   "isShowPaging": false,
                                   "pageLength": 20,
                                   "pagination": "datatableWithSearchPagination"
                                 }'>

                                <thead class="thead-light">
                                <tr>
                                    <th>@lang('SL.')</th>
                                    <th>@lang('User')</th>
                                    <th>@lang('Role')</th>
                                    <th>@lang('Status')</th>
                                    <th>@lang('Action')</th>
                                </tr>
                                </thead>

                                <tbody>
                                @forelse($roleUsers as $key => $value)
                                    <tr>
                                        <td data-label="@lang('SL.')">{{++$key }}</td>
                                        <td data-label="@lang('User')">
                                            <a class="d-flex align-items-center me-2" href="javascript:;">
                                                <div class="flex-shrink-0">
                                                    {!! $value?->avatarImage() !!}
                                                </div>
                                                <div class="flex-grow-1 ms-2">
                                                    <h5 class="text-hover-primary mb-0">{{ $value->name }}</h5>
                                                    <span class="fs-6 text-body">{{'@'. $value->username }}</span>
                                                </div>
                                            </a>
                                        </td>
                                        <td data-label="@lang('Role')">
                                            <span class="badge bg-soft-info text-info">
                                                {{str_replace('_',' ',ucfirst(optional($value->role)->name))}}</span>
                                        </td>
                                        <td data-label="@lang('Status')">
                                            @if($value->status == 1)
                                                <span class="badge bg-soft-success text-success">@lang('Active')</span>
                                            @else
                                                <span class="badge bg-soft-danger text-danger">@lang('Inactive')</span>
                                            @endif
                                        </td>
                                        <td data-label="@lang('Action')">
                                            <button data-bs-target="#login_as" data-bs-toggle="modal"
                                                    data-route="{{route('admin.role.usersLogin',$value->id)}}"
                                                    class="btn btn-sm btn-white loginUser">
                                                <i class="bi-box-arrow-in-right"></i>
                                                @lang('Login As Staff')
                                            </button>
                                            @if($value->status == 0)
                                                <button data-bs-target="#status_change" data-bs-toggle="modal"
                                                        data-route="{{route('admin.role.statusChange',$value->id)}}"
                                                        class="btn btn-sm btn-white enableStatus">
                                                    <i class="bi-check-circle"></i> @lang('Active')
                                                </button>
                                            @else
                                                <button data-bs-target="#status_change" data-bs-toggle="modal"
                                                        data-route="{{route('admin.role.statusChange',$value->id)}}"
                                                        class="btn btn-sm btn-white disableStatus">
                                                    <i class="bi-x-circle"></i> @lang('Inactive')
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    @include('empty')
                                @endforelse
                                </tbody>

                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-center justify-content-sm-end">
                                <nav id="datatableWithSearchPagination" aria-label="Activity pagination"></nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{---Add Modal--}}
        <div id="add-modal" class="modal fade" tabindex="-1" role="dialog"
             aria-labelledby="primary-header-modalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="primary-header-modalLabel">@lang('Add Staffs')</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form>
                        <div class="modal-body">
                            @if($roles)
                                <label class="form-label" for="">@lang('Role') </label>
                                <div class="tom-select-custom">
                                    <select class="js-select form-select" autocomplete="off" v-model="item.role"
                                            data-hs-tom-select-options='{ "placeholder": "Select a roll" }'>
                                        <option selected disabled value="">@lang('Select a role')</option>
                                        @forelse($roles as $item)
                                            <option value="{{$item->id}}">{{$item->name}}</option>
                                        @empty
                                        @endforelse
                                    </select>
                                </div>
                            @endif
                            <div class="col-12 mt-3">
                                <label class="form-label" for="">@lang('Name')</label>
                                <input type="text" class="form-control" v-model="item.name"
                                    placeholder="@lang('Name')"/>
                                <span class="text-danger name-error"></span>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label" for="">@lang('Email')</label>
                                <input type="text" class="form-control" v-model="item.email"
                                    placeholder="@lang('Email')"/>
                                <span class="text-danger email-error"></span>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label" for="">@lang('Username')</label>
                                <input type="text" class="form-control" v-model="item.username"
                                    placeholder="@lang('Username')"/>
                                <span class="text-danger username-error"></span>
                            </div>
                            <div class="col-12 mt-3">
                                <label class="form-label" for="">@lang('Password')</label>
                                <input type="text" class="form-control" v-model="item.password"
                                    placeholder="@lang('Password')"/>
                                <span class="text-danger password-error"></span>
                            </div>
                                <div class="col-md-12 my-3">
                                    <label class="form-label">@lang('Status')</label>
                                    <div class="input-group input-group-sm-vertical w-100">
                                        <label class="form-control" for="statusOn">
                                            <span class="form-check">
                                                <input type="radio" class="form-check-input" v-model="item.status" value="1" id="statusOn">
                                                <span class="form-check-label">@lang('ON')</span>
                                            </span>
                                        </label>
                                        <label class="form-control" for="statusOff">
                                            <span class="form-check">
                                                <input type="radio" class="form-check-input" v-model="item.status" value="0" id="statusOff">
                                                <span class="form-check-label">@lang('OFF')</span>
                                            </span>
                                        </label>
                                    </div>
                                </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="button" class="btn btn-primary" @click.prevent="userSubmit">
                                @lang('Save')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

    {{--Status Change--}}
    <div id="status_change" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="primary-header-modalLabel">@lang('Status Change Confirmation')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="post" class="statusRoute">
                    @csrf
                    <div class="modal-body">
                        <div id="tag-body">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    {{-- Login as --}}
    <div id="login_as" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="primary-header-modalLabel">@lang('Login as user')</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="post" class="loginRoute">
                    @csrf
                    <div class="modal-body">
                        <p>@lang('Are you sure want to login as user')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Submit')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

<x-assets :datatable="true" :flatpickr="false" />

@push('script')

	<script>
		'use strict'

        $(document).ready(function () {
            initNormalDataTable();
        });

        $(document).on('click', '.enableStatus', function () {
			$('#tag-body').html('');
			var route = $(this).data('route');
			$('.statusRoute').attr('action', route)
			$('#tag-body').append(`<p>Are you sure active this staff<p>`)
		});

		$(document).on('click', '.disableStatus', function () {
			$('#tag-body').html('');
			var route = $(this).data('route');
			$('.statusRoute').attr('action', route)
			$('#tag-body').append(`<p>Are you sure inactive this staff<p>`)
		});

		$(document).on('click', '.loginUser', function () {
			var route = $(this).data('route');
			$('.loginRoute').attr('action', route)
		});

		var newApp = new Vue({
			el: "#manage-user-app",
			data: {
				item: {
					name: "", email: "", username: "", password: "", role: "",
					id: "",
					status: "",
				}
			},
			mounted() {
				this.item.status = 1;
			},
			methods: {
				userSubmit() {
					var $url = '{{route('admin.role.usersCreate')}}'
					axios.post($url, this.item)
						.then(function (response) {
							if (response.data.result) {
								location.reload();
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
							errors = errors.errors
							for (let err in errors) {
								let selector = document.querySelector(`.${err}-error`);
								if (selector) {
									selector.innerText = `${errors[err]}`;
								}
							}
						});
				},
				makeDataEmpty() {
					this.item.name = "";
					this.item.email = "";
					this.item.username = "";
					this.item.password = "";
					this.item.id = "";
				},
				editRole(obj) {
					this.makeDataEmpty();
					this.item.name = obj.name;
					this.item.id = obj.id;
					this.item.status = obj.status;
					this.item.permissions = obj.permission;
					if (0 < obj.permission.length) {
						obj.permission.map(function (obj, i) {
							$(`.permission-check[value="${obj}"]`).attr('checked', 'checked');
						});
					}
				},
				rolePermissionUpdate() {
					var $url = '{{route('admin.role.update')}}'
					axios.post($url, this.item)
						.then(function (response) {
							if (response.data.result) {
								location.reload();
							}
						})
						.catch(function (error) {
							let errors = error.response.data;
							errors = errors.errors
							for (let err in errors) {
								let selector = document.querySelector(`.${err}-error`);
								if (selector) {
									selector.innerText = `${errors[err]}`;
								}
							}
						});
				},
			}
		})
	</script>
@endpush

