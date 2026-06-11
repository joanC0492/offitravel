(function($){
	"use strict";

	$(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction('frontend/element_ready/tripgo_elementor_testimonial.default', function() {
			$('.ova-testimonial .slide-testimonial-version1').each( function() {
		        const slk = $(this) ;
		        const slkOps = slk.data('options') ? slk.data('options') : {};

		        // RTL
		        const isRTL = $('body').hasClass('rtl');
		        if ( isRTL ) {
        			slk.parent().attr('dir','rtl');
        		}
		        
		        slk.slick({
		            dots: false,
		            autoplay : slkOps.autoplay,
		            autoplaySpeed : slkOps.autoplay_speed, 
		            speed: slkOps.smartSpeed,
				    centerPadding: 0,
				    slidesToShow: 1,
				    infinite: slkOps.loop,
				    arrows: false,
				    dots: slkOps.dots,
				    pauseOnHover: slkOps.pause_on_hover,
		            variableWidth: false,
				    centerMode: true,
				    asNavFor: '.slide-for',
				    rtl: isRTL,
				    responsive: [
					    {
					      	breakpoint: 768,
					      	settings: {
					        	arrows: true,
					        	centerMode: true,
					        	variableWidth: false
					      	}
					    }
				  	]
				});

		      	// Fixed WCAG
				slk.find('.slick-prev').attr('title', 'Previous');
				slk.find('.slick-next').attr('title', 'Next');
				slk.find('.slick-dots button').attr('title', 'Dots');
		    });

		    //slide syncing
		    $('.slide-for').each( function() {
		        const slk2 = $(this);
		        
		        // Slider syncing
			    slk2.slick({ 
				    slidesToShow: 3,
				    slidesToScroll: 1,
				    arrows: false,
				    dots: false,
				    variableWidth: true,
				    fade: true,
                    focusOnSelect: true,
                    centerMode: true,
				    asNavFor: '.slide-testimonial-version1'
				});
		    });
		});
   	});

})(jQuery);