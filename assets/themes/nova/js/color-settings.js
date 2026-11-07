(function ($) {
    "use strict";

	// color change
    $('.color-trigger').on('click', function () {
        $(this).parent().toggleClass('visible-palate');
    });


	// rtl-version
	var directionChanger = $(".color-palate .rtl-version li");
	var wrapper = $(".page-wrapper");
	directionChanger.on("click", function(e) {
        var $this = $(this);
        if ( $this.hasClass("rtl") ) {
            wrapper.addClass("rtl");
        } else {
        	wrapper.removeClass("rtl");
    	};
	});


}(jQuery));