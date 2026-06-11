(function($){
	"use strict";

	$(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/tripgo_elementor_progress.default', function() {
	        $('.ova-percent').appear( function() {
   				const that = $(this);

   				// Get percent
   				const percent = that.data('percent');

   				// Get percentage
   				const percentage = that.closest('.ova-percent-view').find('.percentage')

   				that.animate({
			        width: percent + "%"
			        },1000, function() {
			        	if ( percentage.data('show-percent') == 'yes' ) {
			        		percentage.show();
			        	}
			        }
		        );
   			});
        });
   });

})(jQuery);