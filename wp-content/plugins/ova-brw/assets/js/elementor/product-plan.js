(function($) {
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/ovabrw_product_plan.default', function() {
            $('.item-tour-plan').each( function() {
                const that = $(this);
                const item = that.find('.tour-plan-title');

                item.on('click', function() {
                    $(this).closest('.item-tour-plan').toggleClass('active');

                    // change icon
                    if ( that.hasClass('active') ) {
                        $(this).find('i').removeClass('icomoon-chevron-down');
                        $(this).find('i').addClass('icomoon-chevron-up');
                    } else {
                        $(this).find('i').removeClass('icomoon-chevron-up');
                        $(this).find('i').addClass('icomoon-chevron-down');
                    }
                });
            });
        });
	});

})(jQuery);