// add bg to nav
window.addEventListener("scroll", function () {
	let scrollpos = window.scrollY;
	const header = document.querySelector("nav");
	const headerHeight = header.offsetHeight;

	if (scrollpos >= headerHeight) {
		header.classList.add("active");
	} else {
		header.classList.remove("active");
	}
});

// active nav item
const navItem = document.getElementsByClassName("nav-link");
for (const element of navItem) {
	element.addEventListener("click", () => {
		for (const ele of navItem) {
			ele.classList.remove("active");
		}
		element.classList.add("active");
	});
}

// tab
const tabs = document.getElementsByClassName("tab");
const contents = document.getElementsByClassName("content");
for (const element of tabs) {
	const tabId = element.getAttribute("tab-id");
	const content = document.getElementById(tabId);
	element.addEventListener("click", () => {
		for (const t of tabs) {
			t.classList.remove("active");
		}
		for (const c of contents) {
			c.classList.remove("active");
		}
		element.classList.add("active");
		content.classList.add("active");
	});
}

// input file preview
const previewImage = (id) => {
	document.getElementById(id).src = URL.createObjectURL(event.target.files[0]);
};

// toggle sidebar
const toggleSidebar = (id) => {
	const element = document.getElementById(id);
	element.classList.toggle("active");
};
const removeClass = (id) => {
	const element = document.getElementById(id);
	element.classList.remove("active");
};

$(document).ready(function () {
	// owl carousel
	$(".popular-products-carousel").owlCarousel({
		loop: true,
		margin: 15,
		rtl: false,
		nav: false,
		dots: true,
		autoplay: true,
		autoplayTimeout: 3000,
		responsive: {
			0: {
				items: 1,
			},
			768: {
				items: 2,
			},
			992: {
				items: 4,
			},
		},
	});

	$("#shareBlock").socialSharingPlugin({
		urlShare: window.location.href,
		description: $("meta[name=description]").attr("content"),
		title: $("title").text(),
	});



	// SCROLL TOP
	$(".scroll-up").fadeOut();
	$(window).scroll(function () {
		if ($(this).scrollTop() > 100) {
			$(".scroll-up").fadeIn();
		} else {
			$(".scroll-up").fadeOut();
		}
	});
});


