<!-- navbar -->
<nav class="navbar navbar-expand-lg fixed-top">
	<div class="container">
		<a class="navbar-brand" href="javascript:void(0)"> <img src="{{shopImage($link)}}"
																alt="..."/></a>
		<!-- navbar text -->
		<span class="navbar-text">
			<div class="shopping-cart mt-2">
				<a href="{{route('public.seller.details',$link)}}" class="dropdown-toggle"
				   title="@lang('Seller Profile')">
                     <i class="fas fa-user-tie"></i>
                  </a>
			</div>
			<!-- shopping cart -->
               <div class="shopping-cart">
                  <button class="dropdown-toggle">
                     <i class="fal fa-shopping-cart"></i>
                     <span class="count"></span>
                  </button>
                  <ul class="cart-dropdown">
                     <div class="dropdown-box show-cart">

                     </div>
                     <div class="cart-bottom">
                        <p>@lang('Cart subtotal:')<span>{{currencyCode($link)}}</span><span
								class="total-cart me-1"></span></p>
                        <div class="d-flex justify-content-between mt-3">
                           <a href="{{route('public.cart',$link)}}" class="btn-custom">@lang('cart')</a>
                           <a href="{{route('public.checkout',$link)}}" class="btn-custom">@lang('checkout')</a>
                        </div>
                     </div>
                  </ul>
               </div>
               <a href="{{route('public.product.track',$link)}}" class="btn-custom">@lang('Track Order')</a>
            </span>
	</div>
</nav>
