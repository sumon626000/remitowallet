(function($) {

	"use strict";

    /*Handle Loading Box (Preloader)*/
    function handlePreloader() {
        if($('.loader-wrap').length){
            $('.loader-wrap').delay(1000).fadeOut(500);
        }
        TweenMax.to($(".loader-wrap .overlay"), 1.2, {
            force3D: true,
            left: "100%",
            ease: Expo.easeInOut,
        });
    }
    handlePreloader();

    if ($(".preloader-close").length) {
        $(".preloader-close").on("click", function(){
            $('.loader-wrap').delay(200).fadeOut(500);
        })
    }
    /*End Loading Box (Preloader)*/



	// button animation
	$(function() {
		$('.btn-1, .btn-2')
		.on('mouseenter', function(e) {
				var parentOffset = $(this).offset(),
				relX = e.pageX - parentOffset.left,
				relY = e.pageY - parentOffset.top;
				$(this).find('span').css({top:relY, left:relX})
		})
		.on('mouseout', function(e) {
				var parentOffset = $(this).offset(),
				relX = e.pageX - parentOffset.left,
				relY = e.pageY - parentOffset.top;
			$(this).find('span').css({top:relY, left:relX})
		});
	});
	// button animation


	//Search Popup 2
	if($('#search-popupt-two').length){
		//Show Popup
		$('.search-btn').on('click', function() {
			$('#search-popupt-two').addClass('popup-visible');
		});
		$(document).keydown(function(e){
	        if(e.keyCode === 27) {
	            $('#search-popupt-two').removeClass('popup-visible');
	        }
	    });
		//Hide Popup
		$('.close-search,.search-popupt-two .overlay-layer').on('click', function() {
			$('#search-popupt-two').removeClass('popup-visible');
		});
	}
	//Search Popup 2



	// count down start
	function updateCountdown() {
		var now = new Date();
		var eventDate = new Date("2024-07-01"); // Change this to your event date
		var currentTime = now.getTime();
		var eventTime = eventDate.getTime();
		var remTime = eventTime - currentTime;

		var seconds = Math.floor(remTime / 1000);
		var minutes = Math.floor(seconds / 60);
		var hours = Math.floor(minutes / 60);
		var days = Math.floor(hours / 24);

		hours %= 24;
		minutes %= 60;
		seconds %= 60;

		$("#days").text(days);
		$("#hours").text(hours);
		$("#minutes").text(minutes);
		$("#seconds").text(seconds);
	}
	setInterval(updateCountdown, 1000);
	updateCountdown();
	// count down end

	//--- OFFCANVAS TAGGLE ---//
	$(document).ready(function() {
		$('.offcanvas-taggle-btn').click(function() {
			$('.shop-offcanvas-left').toggleClass('collapsed');
			$('.shop-offcanvas-right').toggleClass('expanded');
		});
	});
	//--- OFFCANVAS TAGGLE ---//

	//--- CLOSE BUTTON ---//
	$(document).ready(function(){
		$('.clear-all').on('click', function() {
			if ($('.clear-btn-group').is(':visible')) {
				$('.clear-btn-group').hide();
				$(this).html('Add <i class="fas fa-plus"></i>');
			} else {
				$('.clear-btn-group').show();
				$(this).html('Clear <i class="fas fa-times"></i>');
			}
		});

		$('.clear-icon').click(function(e) {
			e.preventDefault();
			$(this).closest('.clear-btn').parent('li').remove();
		});
	});
	//--- CLOSE BUTTON ---//

	// INCREMENT DECREMENT COUNT START
	$(document).ready(function(){
        var count = 0;

        function updateCount() {
            $('.number').text(count);
        }

        $('.increment').click(function(){
            count++;
            updateCount();
        });

        $('.decrement').click(function(){
            if (count > 0) {
                count--;
                updateCount();
            }
        });
    });
	// INCREMENT DECREMENT COUNT END


	// LOAD MORE STARTS
	$('.item-list').slice(0,2).show();

	$('.load-more').click(function(){
		$('.item-list:hidden').slice(0,1).slideDown(300);

		// hide btn after fully loaded
		if($('.item-list:hidden').length==0){
			$(this).fadeOut(300);
		}
	});
	// LOAD MORE ENDS

	// Tab box
	if ($('.quote-tab').length) {
		$('.quote-tab .tabs-button-box .tab-btn-item').on('click', function (e) {
			e.preventDefault();
			var target = $($(this).attr('data-tab'));

			if ($(target).hasClass('actve-tab')) {
			return false;
			} else {
			$('.quote-tab .tabs-button-box .tab-btn-item').removeClass('active-btn-item');
			$(this).addClass('active-btn-item');
			$('.quote-tab .tabs-content-box .tab-content-box-item').removeClass('tab-content-box-item-active');
			$(target).addClass('tab-content-box-item-active');
			}
		});
	}
	// Tab box

	// flatpickr date and time
	$(".flatpickr").flatpickr({
		altInput: true,
		altFormat: "d/m/y",
		dateFormat: "Y-m-d",
		mode: "multiple" ,
		minDate: "today" ,
		maxDate: "2024-05-15"
	});
	// flatpickr date and time

	// odommeter
	if ($(".odometer").length) {
		var odo = $(".odometer");
		odo.each(function () {
		  $(this).appear(function () {
			var countNumber = $(this).attr("data-count");
			$(this).html(countNumber);
		  });

		});
	}
	// odommeter

	// magnifipopup video
	$(document).ready(function() {
		$('.video-popup').magnificPopup({
			disableOn: 700,
			type: 'iframe',
			mainClass: 'mfp-fade',
			removalDelay: 160,
			preloader: false,

			fixedContentPos: false
		});
	});
	// magnifipopup video


	//Price Range Slider
	if ($('.price-range-slider').length) {
		$(".price-range-slider").slider({
			range: true,
			min: 0,
			max: 500,
			values: [0, 500],
			slide: function (event, ui) {
				$("input.property-amount").val("$" + ui.values[0] + " - $" + ui.values[1]);
			}
		});

		$("input.property-amount").val("$" + $(".price-range-slider").slider("values", 0) + " - $" + $(".price-range-slider").slider("values", 1));
	}
	//Price Range Slider


    if ($('.password-box').length) {
        const passwordBoxes = document.querySelectorAll('.password-box');

        passwordBoxes.forEach(box => {
            const password = box.querySelector('.password');
            const passwordIcon = box.querySelector('.password-icon');

            passwordIcon.addEventListener("click", function () {
                if (password.type == 'password') {
                    password.type = 'text';
                    passwordIcon.classList.add('fa-eye-slash');
                } else {
                    password.type = 'password';
                    passwordIcon.classList.remove('fa-eye-slash');
                }
            });
        });
    }

	// Menu style
    function dynamicCurrentMenuClass(selector) {
        let FileName = window.location.href.split('/').reverse()[0];

        selector.find('li').each(function () {
            let anchor = $(this).find('a');
            if ($(anchor).attr('href') == FileName) {
                $(this).addClass('current');
            }
        });
        // if any li has .current elmnt add class
        selector.children('li').each(function () {
            if ($(this).find('.current').length) {
                $(this).addClass('current');
            }
        });
        // if no file name return
        if ('' == FileName) {
            selector.find('li').eq(0).addClass('current');
        }
    }
	// Menu style

    // dynamic current class
    let mainNavUL = $('.main-menu').find('.navigation');
    dynamicCurrentMenuClass(mainNavUL);

	//Sticky Header Style and Scroll to Top
	function headerStyle() {
		if($('.main-header').length){
			var windowpos = $(window).scrollTop();
			var siteHeader = $('.main-header');
			var scrollLink = $('.scroll-to-top');
			var sticky_header = $('.main-header .sticky-header');
			if (windowpos > 100) {
				siteHeader.addClass('fixed-header');
				sticky_header.addClass("animated slideInDown");
				scrollLink.fadeIn(300);
			} else {
				siteHeader.removeClass('fixed-header');
				sticky_header.removeClass("animated slideInDown");
				scrollLink.fadeOut(300);
			}
		}
	}
	headerStyle();
	//Sticky Header Style and Scroll to Top

	//Submenu Dropdown Toggle
	if($('.main-header li.dropdown ul').length){
		$('.main-header .navigation li.dropdown').append('<div class="dropdown-btn"><span class="fa fa-angle-right"></span></div>');
	}

	//Mobile Nav Hide Show
	if($('.mobile-menu').length){
		var mobileMenuContent = $('.main-header .nav-outer .main-menu').html();
		$('.mobile-menu .menu-box .menu-outer').append(mobileMenuContent);
		$('.sticky-header .main-menu').append(mobileMenuContent);
		//Dropdown Button
		$('.mobile-menu li.dropdown .dropdown-btn').on('click', function() {
			$(this).toggleClass('open');
			$(this).prev('ul').slideToggle(500);
			$(this).prev('.mega_menu').slideToggle(500);
		});
		//Menu Toggle Btn
		$('.mobile-nav-toggler').on('click', function() {
			$('body').addClass('mobile-menu-visible');
		});
		//Menu Toggle Btn
		$('.mobile-menu .menu-backdrop,.mobile-menu .close-btn,.scroll-nav li a').on('click', function() {
			$('body').removeClass('mobile-menu-visible');
		});
	}

	//Search Popup
	if($('#search-popup').length){
		//Show Popup
		$('.search-toggler').on('click', function() {
			$('#search-popup').addClass('popup-visible');
		});
		$(document).keydown(function(e){
	        if(e.keyCode === 27) {
	            $('#search-popup').removeClass('popup-visible');
	        }
	    });
		//Hide Popup
		$('.close-search,.search-popup .overlay-layer').on('click', function() {
			$('#search-popup').removeClass('popup-visible');
		});
	}
	//Search Popup

	// banner slide
	function bannerSlider() {
		// banner slide 01
		if ($(".banner-slider-1").length > 0) {
		    // Banner Slider
			var bannerSlider1 = new Swiper('.banner-slider-1', {
				preloadImages: false,
                loop: true,
                centeredSlides: false,
                resistance: true,
                resistanceRatio: 0.6,
                speed: 2400,
                spaceBetween: 0,
                parallax: false,
                effect: "fade",
				autoplay: {
				    delay: 8000,
                    disableOnInteraction: false
				},
				pagination: {
				el: '.slider__pagination',
				clickable: true,
			  	},
	            navigation: {
	                nextEl: '.banner-slider-button-next',
	                prevEl: '.banner-slider-button-prev',
	            },
			});
		}
	}
	bannerSlider();
	// banner slide


	if ($('.theme_carousel').length) {
		$(".theme_carousel").each(function (index) {
			var $owlAttr = {},
			$extraAttr = $(this).data("options");
			$.extend($owlAttr, $extraAttr);
			$(this).owlCarousel($owlAttr);
		});
	}

    // three item carousel
    if ($('.three-item-carousel').length) {
        var twoItemCarousel = new Swiper('.three-item-carousel', {
            preloadImages: false,
            loop: true,
            centeredSlides: false,
            resistance: true,
            resistanceRatio: 0.6,
            slidesPerView: 3,
            speed: 1400,
            spaceBetween: 30,
            parallax: false,
            effect: "slide",
            active: 'active',
            autoplay: {
                delay: 5000,
                disableOnInteraction: false
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            navigation: {
                nextEl: '.slider-button-next4',
                prevEl: '.slider-button-prev4',
            },
            breakpoints: {
                1400: {
                    slidesPerView: 3,
                },
                991: {
                    slidesPerView: 2,
                },
                640: {
                    slidesPerView: 1,
                },
            }
        });
    }
    // four item carousel
	if ($('.four-item-carousel').length) {
		var twoItemCarousel = new Swiper('.four-item-carousel', {
			preloadImages: false,
			loop: true,
			grabCursor: false,
			centeredSlides: false,
			resistance: true,
			resistanceRatio: 0.6,
			slidesPerView: 4,
			speed: 1400,
			spaceBetween: 30,
			parallax: false,
			effect: "slide",
			active: 'active',
			autoplay: {
				delay: 5000,
				disableOnInteraction: false
			},
			pagination: {
				el: '.slider__pagination3',
				clickable: true,
			},
			navigation: {
				nextEl: '.swiper-button-next',
				prevEl: '.swiper-button-prev',
			},
			breakpoints: {
				1400: {
					slidesPerView: 3,
				},
                991: {
                  slidesPerView: 2,
                },
                640: {
                  slidesPerView: 1,
                },
            }
		});
	}
	// four item carousel
    if ($('.single-item-carousel').length) {
        var singleItemCarousel = new Swiper('.single-item-carousel', {
            preloadImages: false,
            loop: true,
            grabCursor: true,
            centeredSlides: false,
            resistance: true,
            resistanceRatio: 0.6,
            speed: 1400,
            spaceBetween: 0,
            parallax: false,
            effect: "slide",
            autoplay: {
                delay: 8000,
                disableOnInteraction: false
            },
            pagination: {
                el: '.slider__pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.slider-button-next',
                prevEl: '.slider-button-prev',
            },
        });
    }


	// Scroll to a Specific Div
	if($('.scroll-to-target').length){
		$(".scroll-to-target").on('click', function() {
			var target = $(this).attr('data-target');
		   // animate
		   $('html, body').animate({
			   scrollTop: $(target).offset().top
			 }, 1500);
		});
	}
	// Scroll to a Specific Div

	//Accordion Box
	if($('.accordion-box').length){
		$(".accordion-box").on('click', '.acc-btn', function() {

			var outerBox = $(this).parents('.accordion-box');
			var target = $(this).parents('.accordion');

			if($(this).hasClass('active')!==true){
				$(outerBox).find('.accordion .acc-btn').removeClass('active');
			}

			if ($(this).next('.acc-content').is(':visible')){
				return false;
			}else{
				$(this).addClass('active');
				$(outerBox).children('.accordion').removeClass('active-block');
				$(outerBox).find('.accordion').children('.acc-content').slideUp(300);
				target.addClass('active-block');
				$(this).next('.acc-content').slideDown(300);
			}
		});
	}
	//Accordion Box

	// progress (scroll to top)
	if($('.prgoress_indicator path').length){
		var progressPath = document.querySelector('.prgoress_indicator path');
		var pathLength = progressPath.getTotalLength();

		progressPath.style.transition = progressPath.style.WebkitTransition = 'none';
		progressPath.style.strokeDasharray = pathLength + ' ' + pathLength;
		progressPath.style.strokeDashoffset = pathLength;
		progressPath.getBoundingClientRect();
		progressPath.style.transition = progressPath.style.WebkitTransition = 'stroke-dashoffset 10ms linear';

		var updateProgress = function () {
		  var scroll = $(window).scrollTop();
		  var height = $(document).height() - $(window).height();
		  var progress = pathLength - (scroll * pathLength / height);
		  progressPath.style.strokeDashoffset = progress;
		}
		updateProgress();

		$(window).on('scroll', updateProgress);
		var offset = 250;
		var duration = 550;
		jQuery(window).on('scroll', function () {
		  if (jQuery(this).scrollTop() > offset) {
			jQuery('.prgoress_indicator').addClass('active-progress');
		  } else {
			jQuery('.prgoress_indicator').removeClass('active-progress');
		  }
		});
		jQuery('.prgoress_indicator').on('click', function (event) {
		  event.preventDefault();
		  jQuery('html, body').animate({ scrollTop: 0 }, duration);
		  return false;
		});

	}
	// progress (scroll to top)

	// Elements Animation
	if($('.wow').length){
		var wow = new WOW(
		  {
			boxClass:     'wow',
			animateClass: 'animated',
			offset:       0,
			mobile:       true,
			live:         true
		  }
		);
		wow.init();
	}
	// Elements Animation

/* ==========================================================================
   When document is Scrollig, do
   ========================================================================== */

	$(window).on('scroll', function() {
		headerStyle();

	});

/* ==========================================================================
   When document is loading, do
   ========================================================================== */

   // curved-circle start
	$(window).scroll(function() {
		var theta = $(window).scrollTop() / 15;
		$(".about-two__left-content .round-box-content .curved-circle").css({ transform: "rotate(" + theta + "deg)" });
	});
	$(window).on('load', function() {
		if ($('.curved-circle').length) {
			$('.curved-circle').circleType({
			position: 'absolute',
			dir: 1,
			radius: 85,
			forceHeight: true,
			forceWidth: true
			});
		}
		if ($('.curved-circle-2').length) {
			$('.curved-circle-2').circleType({
			position: 'absolute',
			dir: 1,
			radius: 170,
			forceHeight: true,
			forceWidth: true
			});
		}
	});
	// curved-circle end



})(window.jQuery);
