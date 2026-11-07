


<script src="{{ asset('assets/global/js/jquery.min.js') }}"></script>
<script src="{{ asset('assets/global/js/notiflix-aio-3.2.6.min.js') }}"></script>
<script src="{{ asset(template(true) . 'js/bootstrap.min.js') }}"></script>

{{--hero--}}
<script src="{{ asset(template(true) . 'js/swiper.min.js') }}"></script>

<!--preloader-->
<script src="{{ asset(template(true) . 'js/TweenMax.min.js') }}"></script>

<!--video popup-->
<script src="{{ asset(template(true) . 'js/jquery.magnific-popup.min.js') }}"></script>

<!--counter-->
<script src="{{ asset(template(true) . 'js/odometer.min.js') }}"></script>
<script src="{{ asset(template(true) . 'js/appear.js') }}"></script>



<script src="{{ asset(template(true) . 'js/select2.min.js') }}"></script>
<script src="{{ asset(template(true) . 'js/parallax-scroll.js') }}"></script>
<script src="{{ asset(template(true) . 'js/jarallax.min.js') }}"></script>


<script src="{{ asset(template(true) . 'js/color-settings.js') }}"></script>

<script src="{{ asset(template(true) . 'js/flatpickr-min.js') }}"></script>
<script src="{{ asset(template(true) . 'js/script.js') }}"></script>

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

@stack('js-lib')


@stack('script')

@stack('extra_scripts')
