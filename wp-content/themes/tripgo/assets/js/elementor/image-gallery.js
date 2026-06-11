(function($){
	"use strict";

	$(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/tripgo_elementor_ova_image_gallery.default', function() {
	    	$('.ova-image-gallery-ft').each( function() {
	    		const that = $(this);
	    		const item = that.find('.item-fancybox-ft');
	    		const opts = that.data('options') ? that.data('options') : {};

	    		item.on( 'click', function() {
	    			Fancybox.bind('[data-fancybox="image-gallery-ft"]', {
					 	infinite: opts.loop
					});
	    		});
	    	});
        });
   });

})(jQuery);