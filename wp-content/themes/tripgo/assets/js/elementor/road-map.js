(function($){
	"use strict";
	
	$(window).on('elementor/frontend/init', function() {
        elementorFrontend.hooks.addAction('frontend/element_ready/tripgo_elementor_road_map.default', function() {
	    	$('.ova-invisible').each( function() {
	      		const that = $(this);

	      		if ( $(window).width() <= 1024 ) {
	      			that.removeClass('ova-invisible')
	      		} else {
	      			that.appear( function() {
		   				const animationData = that.data('animation');
		   				if ( animationData ) {
		   					const animation = animationData['animation'];
		   					const duration 	= animationData['duration'];
		   					const delay 	= animationData['delay'] ? animationData['delay'] : 0;

		   					setTimeout( function () {
		   						that.removeClass('ova-invisible').addClass('animated').addClass(animation);
		   						if ( duration ) {
		   							that.addClass(  'animated-' + duration );
		   						}
						    }, delay );
		   				}
		   			});
	      		}
	      	});
        });
   	});

})(jQuery);