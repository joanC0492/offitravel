(function($) {
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/tripgo_elementor_menu_cart.default', function(){
	        $('.ova-menu-cart').each( function() {
	        	const left 	= $(this).offset().left - $(window).scrollLeft();
				const right = $(window).width() - (left + $(this).outerWidth());

				if ( left > right ) {
					$(this).find('.minicart').css('right', 0);
				} else {
					$(this).find('.minicart').css('left', 0);
				}
	        });
        });
   });

})(jQuery);