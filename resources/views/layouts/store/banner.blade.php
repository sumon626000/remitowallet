<!-- banner section -->
@php
	$link = \Illuminate\Support\Facades\Session::get('link')
@endphp
<section class="banner-section">
	<div class="container">
		<div class="row">
			<div class="col-12">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb justify-content-start">
						<li class="breadcrumb-item"><a href="{{route('public.view',@$link)}}">@lang('Home')</a></li>
						<li class="breadcrumb-item active" aria-current="page">@yield('page_title')</li>
					</ol>
				</nav>
				@if(Route::currentRouteName() != 'order.success')
					<h3 class="text-center">@yield('page_title')</h3>
				@endif
			</div>
		</div>
	</div>
</section>
