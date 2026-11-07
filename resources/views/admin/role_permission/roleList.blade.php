@extends('admin.layouts.app')
@section('page_title',__('Role List'))


@section('content')
<div id="role-permission-app">
    <div class="content container-fluid">
        <x-page-header menu="Roles & Permission"/>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <h2 class="card-title h4 mt-3">@lang("Role List")</h2>
                        <button class="btn btn-sm btn-primary" data-bs-target="#add-modal" data-bs-toggle="modal"
                                @click="makeDataEmpty">@lang('Add New')</button>
                    </div>
                    <div class="table-responsive position-relative">
                        <table
                            class="table table-borderless table-thead-bordered table-nowrap table-align-middle card-table"
                            id="supported_currency_table">
                            <thead class="thead-light">
                            <tr>
                                <th>@lang('SL.')</th>
                                <th>@lang('Name')</th>
                                <th>@lang('Status')</th>
                                <th>@lang('Action')</th>
                            </tr>
                            </thead>

                            <tbody>
                            @forelse($roles as $key => $value)
                                <tr>
                                    <td data-label="@lang('SL.')">{{++$key }}</td>
                                    <td data-label="@lang('Name')">{{$value->name }}</td>
                                    <td data-label="@lang('Status')">
                                        {!! $value->getStatus() !!}
                                    </td>
                                    <td data-label="@lang('Action')">
                                        <button class="btn btn-sm btn-white editRole"
                                            data-bs-target="#editModal" data-bs-toggle="modal" data-resource="{{$value}}"
                                            @click="editRole({{ $value }})"><i class="bi-pencil-square"></i> @lang('Edit')
                                        </button>
                                        <button
                                            class="btn btn-sm btn-white notiflix-confirm"
                                            data-bs-target="#delete-modal" data-bs-toggle="modal"
                                            data-route="{{route('admin.role.delete',$value->id)}}">
                                            <i class="bi-trash"></i> @lang('Delete')
                                        </button>
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

    {{--	Add Modal--}}
    <div id="add-modal" class="modal fade" data-backdrop="static" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Add Roles')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <div class="col-12">
                            <label class="form-label" for="">@lang('Name')</label>
                            <input type="text" class="form-control" v-model="item.name"
                                placeholder="@lang('Name')"/>
                            <span class="text-danger name-error"></span>
                        </div>

                        <div class="col-md-12 my-3">
                            <label class="form-label" for="">@lang('Status')</label>
                            <div class="input-group input-group-sm-vertical w-100">
                                <label class="form-control" for="statusOff">
                                    <span class="form-check">
                                        <input type="radio" class="form-check-input" v-model="item.status" value="0" id="statusOff">
                                        <span class="form-check-label">@lang('OFF')</span>
                                    </span>
                                </label>
                                <label class="form-control" for="statusOn">
                                    <span class="form-check">
                                        <input type="radio" class="form-check-input" v-model="item.status" value="1" id="statusOn">
                                        <span class="form-check-label">@lang('ON')</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="card mb-4 card-primary shadow">
                            <div class="card-header">
                                <div class="title">
                                    <h5>@lang('Accessibility')</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(config('permissionList'))
                                    <div class="row mt-3">
                                        @foreach(config('permissionList') as $key => $item)
                                            <div class="input-box col-md-4 mt-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                           v-model="item.permissions" value="{{$item}}" role="switch"
                                                           id="pAdd-{{$key}}"/>
                                                    <label class="form-check-label" for="pAdd-{{$key}}">
                                                        {{str_replace('_',' ',ucfirst($key))}}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <span class="text-danger mb-2 permissions-error"></span>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="button" class="btn btn-primary"
                                @click.prevent="rolePermissionSubmit">@lang('save')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{--	Edit Modal--}}
    <div id="editModal" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Update Roles')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form>
                    <div class="modal-body">
                        <input type="hidden" v-model="item.id" class="editRoleId" value="">
                        <div class="col-12">
                            <label class="form-label" for="">@lang('Name')</label>
                            <input type="text" class="form-control editRoleName" v-model="item.name"
                                placeholder="@lang('Name')"/>
                            <span class="text-danger name-error"></span>
                        </div>

                        <div class="col-md-12 my-3">
                            <label class="form-label" for="">@lang('Status')</label>
                            <div class="input-group input-group-sm-vertical w-100">
                                <label class="form-control" for="statusOff">
                                    <span class="form-check">
                                        <input type="radio" class="form-check-input" v-model="item.status" value="0"
                                               id="statusOff" :checked="item.status == 0">
                                        <span class="form-check-label">@lang('OFF')</span>
                                    </span>
                                </label>
                                <label class="form-control" for="statusOn">
                                    <span class="form-check">
                                        <input type="radio" class="form-check-input" v-model="item.status" value="1"
                                               id="statusOn" :checked="item.status == 1">
                                        <span class="form-check-label">@lang('ON')</span>
                                    </span>
                                </label>
                            </div>
                        </div>

                        <div class="card mb-4 card-primary shadow">
                            <div class="card-header">
                                <div class="title">
                                    <h5>@lang('Accessibility')</h5>
                                </div>
                            </div>
                            <div class="card-body">
                                @if(config('permissionList'))
                                    <div class="row">
                                        @foreach(config('permissionList') as $key => $item)
                                            <div class="input-box col-md-4 mt-3">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox"
                                                           v-model="item.permissions" value="{{$item}}" role="switch"
                                                           id="pEdit-{{$key}}"/>
                                                    <label class="form-check-label" for="pEdit-{{$key}}">
                                                        {{str_replace('_',' ',ucfirst($key))}}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                            <span class="text-danger mb-2 permissions-error"></span>
                        </div>
                        <div class="modal-footer border-0 pt-0 pb-0">
                            <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                            <button type="button" @click.prevent="rolePermissionUpdate"
                                    class="btn btn-primary">@lang('Update')</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{--	Delete Modal--}}
    <div id="delete-modal" class="modal fade" tabindex="-1" role="dialog"
         aria-labelledby="primary-header-modalLabel"
         aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title text-dark font-weight-bold"
                        id="primary-header-modalLabel">@lang('Delete Confirmation')</h4>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="" method="post" class="deleteRoute">
                    @csrf
                    @method('delete')
                    <div class="modal-body">
                        <p>@lang('Are you sure want to delete this roles')</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-white" data-bs-dismiss="modal">@lang('Close')</button>
                        <button type="submit" class="btn btn-primary">@lang('Confirm')</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@push('script')
	<script>
		'use strict'

		$(document).on('click', '.notiflix-confirm', function () {
			var route = $(this).data('route');
			$('.deleteRoute').attr('action', route)
		});

		var newApp = new Vue({
			el: "#role-permission-app",
			data: {
				item: {
					name: "",
					id: "",
					status: "",
					permissions: [],
				}
			},
			mounted() {
				this.item.status = 1;
			},
			methods: {
				rolePermissionSubmit() {
					var $url = '{{route('admin.role.create')}}'
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
					this.item.id = "";
					this.item.permissions = [];
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
                                errors[err].forEach(function (message) {
                                    Notiflix.Notify.failure(message);
                                });
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

