(function($){
	"use strict";

	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/ova_destination.default', function() {
            $('.ova-destination .content-destination').each( function() {
                const grid = $(this);
                const run  = grid.masonry({
                    itemSelector: '.item-destination',
                    columnWidth: '.grid-sizer',
                    gutter: 0,
                    percentPosition: true,
                    transitionDuration: 0
                });

                run.imagesLoaded().progress( function() {
                    run.masonry();
                });
            });
        });
   });
  
})(jQuery);