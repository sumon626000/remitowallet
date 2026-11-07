@extends('layouts.storeMaster')
@section('page_title', __('Seller Profile'))
@section('content')
	<!-- seller profile -->
	<section class="seller-profile">
		<div class="container">
			<div class="row">
				<div class="col">
					<div class="cover-wrapper">
						<div class="row gy-4">
							<div class="col-lg-6">
								<div class="about d-md-flex">
									<img src="{{ $store->user?->getImage() }}"
										 class="img-fluid profile" alt="{{optional($store->user)->name}}"/>
									<div>
										<h4 class="name">
											{{optional($store->user)->name}}
											@if(optional($store->user)->kyc_verified == 2)
												<i class="fas fa-check-circle" aria-hidden="true"></i>
											@endif
										</h4>
										<p class="bio">
											{{ $store->user?->profile?->about_me }}
										</p>
										<div class="links">
											<a href="javascript:void(0)">
                                                <i class="fal fa-globe"></i>{{optional($store->user)->email}}
											</a>
											<a href="javascript:void(0)">
                                                <i class="fal fa-location-arrow"></i>{{ $store->user?->address_one}}
											</a>
											<a href="javascript:void(0)">
                                                <i class="fal fa-calendar-alt"></i>@lang('Joined')
                                                {{\Carbon\Carbon::parse(optional($store->user)->created_at)->format('d M, Y')}}
											</a>
										</div>
									</div>
								</div>
							</div>
							<div class="col-lg-6">
								<div class="right-wrapper">
									<div class="button-group">
										<button class="btn-custom" data-bs-target="#formModal" data-bs-toggle="modal"><i
												class="fal fa-envelope"></i> @lang('Contact Seller')
										</button>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-lg-12">
					<div class="product-description">
						<div class="navigator">
							<button class="tab active">@lang('description')</button>
						</div>
						<!-- review -->
						<div class="content active">{{$store->short_description}}</div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<div class="modal fade" id="formModal" tabindex="-1" aria-labelledby="formModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-md">
			<div class="modal-content">
				<div class="modal-header">
					<h4 class="modal-title" id="formModalLabel">@lang('Contact Form')</h4>
					<button type="button" class="close-btn" data-bs-dismiss="modal" aria-label="Close">
						<i class="fal fa-times"></i>
					</button>
				</div>
				<form action="{{route('public.seller.contact',$link)}}" method="post">
					@csrf
					<div class="modal-body">
						<div class="row g-4">
							<div class="input-box col-12">
								<input class="form-control" type="text" name="name" placeholder="@lang('Full Name')"
									   required/>
								@error('name')
								<span class="text-danger">{{$message}}</span>
								@enderror
							</div>
							<div class="input-box col-12">
                                <textarea class="form-control" name="message" id="" cols="30" rows="10"
                                   placeholder="@lang('Your Message')" required></textarea>
                                @error('message')<span class="text-danger">{{$message}}</span>@enderror
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn-custom btn2" data-bs-dismiss="modal">@lang('Close')</button>
						<button type="submit" class="btn-custom">@lang('Send')</button>
					</div>
				</form>
			</div>
		</div>
	</div>
@endsection
@push('extra_scripts')
	@if ($errors->any())
		@php
			$collection = collect($errors->all());
			$errors = $collection->unique();
		@endphp
		<script>
			"use strict";
			@foreach ($errors as $error)
			Notiflix.Notify.failure("{{ trans($error) }}");
			@endforeach
		</script>
	@endif
@endpush
