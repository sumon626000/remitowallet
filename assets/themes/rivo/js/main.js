(function ($) {
	"user strict";
	$(window).on("load", function () {
		$(".loader").fadeOut(500);
		var img = $(".bg_img");
		img.css("background-image", function () {
			var bg = "url(" + $(this).data("img") + ")";
			var bg = `url(${$(this).data("img")})`;
			return bg;
		});
	});
	$(document).ready(function () {
		if (localStorage.getItem('cookie-value') == 1 || sessionStorage.getItem('cookie-value') == 1){
			$('.cookie-content').remove();
		} else{
			$('.cookie-content').removeClass('d-none');
		}

		$('#cookie-accept').on("click", function (){
			localStorage.setItem('cookie-value', 1);
			sessionStorage.removeItem('cookie-value');
			$('.cookie-content').remove();
		});

		$('#cookie-deny').on("click", function (){
			sessionStorage.setItem('cookie-value', 1);
			localStorage.removeItem('cookie-value');
			$('.cookie-content').remove();
		});

		$(".faq-block-title").on("click", function (e) {
			var element = $(this).parent(".faq-block-item");
			if (element.hasClass("open")) {
				element.removeClass("open");
				element.find(".faq-block-content").removeClass("open");
				element.find(".faq-block-content").slideUp(200, "swing");
			} else {
				element.addClass("open");
				element.children(".faq-block-content").slideDown(200, "swing");
				element
					.siblings(".faq-block-item")
					.children(".faq-block-content")
					.slideUp(200, "swing");
				element.siblings(".faq-block-item").removeClass("open");
				element
					.siblings(".faq-block-item")
					.find(".faq-block-title")
					.removeClass("open");
				element
					.siblings(".faq-block-item")
					.find(".faq-block-content")
					.slideUp(200, "swing");
			}
		});

		$(".counter-item").each(function () {
			$(this).isInViewport(function (e) {
				if ("entered" === e)
					for (
						var i = 0;
						i < document.querySelectorAll(".counterup").length;
						i++
					) {
						var n = document.querySelectorAll(".counterup")[i];
						n.innerHTML = n.getAttribute("data-odometer-final");
					}
			});
		});
		$("ul>li>.sub-nav").parent("li").addClass("parent-menu");
		$("ul")
			.parent("li")
			.hover(function () {
				var menu = $(this).find("ul");
				var menupos = $(menu).offset();
				if (menupos.left + menu.width() > $(window).width()) {
					var newpos = -$(menu).width();
					menu.css({
						left: newpos,
					});
				}
			});
		$(".nav-menu li a").on("click", function (e) {
			var element = $(this).parent("li");
			if (element.hasClass("open")) {
				element.removeClass("open");
				element.find("li").removeClass("open");
				element.find("ul").slideUp(300, "swing");
			} else {
				element.addClass("open");
				element.children("ul").slideDown(300, "swing");
				element.siblings("li").children("ul").slideUp(300, "swing");
				element.siblings("li").removeClass("open");
				element.siblings("li").find("li").removeClass("open");
				element.siblings("li").find("ul").slideUp(300, "swing");
			}
		});
		var scrollTop = $(".scrollTop");
		$(window).on("scroll", function () {
			if ($(this).scrollTop() < 500) {
				scrollTop.removeClass("active");
			} else {
				scrollTop.addClass("active");
			}
		});
		$(".scrollTop").on("click", function () {
			$("html, body").animate(
				{
					scrollTop: 0,
				},
				500
			);
			return false;
		});
		$(".nav-toggle").on("click", function () {
			$(this).toggleClass("active");
			$(".overlay").toggleClass("active");
			$(".nav-menu-area").toggleClass("active");
		});
		$(".overlay, .menu-close").on("click", function () {
			$(".overlay, .menu-close").removeClass("active");
			$(".nav-toggle").removeClass("active");
			$(".nav-menu-area").removeClass("active");
		});

		//Sticky Header
		var header = $("header");
		var fixed_top = $(".navbar-section");
		$(window).on("scroll", function () {
			if ($(this).scrollTop() > header.height() + fixed_top.height()) {
				fixed_top.addClass("active");
			} else {
				fixed_top.removeClass("active");
			}
		});
		function spaceBottom() {
			return fixed_top.height();
		}
		$(window).on("resize", spaceBottom);
		header.css("padding-bottom", spaceBottom);
		$(".hero-item").css("min-height", function () {
			return `calc(100vh - ${spaceBottom()}px)`;
		});

		var clientSlider = $(".hero-slider").owlCarousel({
			items: 1,
			autoplay: true,
			margin: 0,
			dots: true,
			loop: true,
			dotsContainer: ".hero-dots",
			animateIn: "fadeIn",
			animateOut: "fadeOut",
			mouseDrag: false,
			touchDrag: false,
		});
		$(".owl-prev").on("click", function () {
			clientSlider.trigger("prev.owl.carousel");
			$(this).siblings().removeClass("active");
			$(this).addClass("active");
		});
		$(".owl-next").on("click", function () {
			clientSlider.trigger("next.owl.carousel");
			$(this).siblings().removeClass("active");
			$(this).addClass("active");
		});
		$(".partner-slider").owlCarousel({
			items: 2,
			autoplay: true,
			margin: 14,
			nav: false,
			responsive: {
				576: {
					items: 3,
				},
				768: {
					items: 4,
				},
				992: {
					items: 5,
				},
				1200: {
					items: 6,
				},
			},
		});
		$(".client-slider").owlCarousel({
			items: 1,
			autoplay: true,
			margin: 14,
			nav: false,
			responsive: {
				768: {
					items: 2,
				},
				992: {
					items: 3,
				},
			},
		});
		$(".owl-prev").html('<i class="fas fa-angle-left">');
		$(".owl-next").html('<i class="fas fa-angle-right">');
	});
})(jQuery);




function updateTooltip(selector, tooltipContent, isTooltip = false) {
    $('[data-bs-toggle="tooltip"]').tooltip();
    let tooltipElement = $(selector);
    if (isTooltip) {
        tooltipElement.attr('data-bs-original-title', tooltipContent).removeClass('d-none').addClass('ms-2');
        let tooltip = bootstrap.Tooltip.getInstance(tooltipElement[0]);
        if (tooltip) {
            tooltip.dispose();
        }
        new bootstrap.Tooltip(tooltipElement[0]);
    } else {
        tooltipElement.html(tooltipContent).removeClass('d-none').addClass('text-muted');
    }
}


function formatAmount(value = 0, currencyLimit = 2) {
    const isCrypto = currencyLimit === 8;
    if (!isCrypto) {
        let formattedValue = parseFloat(value).toFixed(currencyLimit);
        if (currencyLimit === 2 && formattedValue.endsWith('.00')) {
            formattedValue = formattedValue.split('.')[0];
        }
        return formattedValue;
    }
    return parseFloat(value).toFixed(currencyLimit);
}
