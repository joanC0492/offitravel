(function($) {
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/tripgo_elementor_gallery_filter.default', function() {
	        $(document).ready(function() {
		        $('.ova-gallery-filter').each( function() {
		        	// that
					const that = $(this);

					// Gallery
					const gallery = that.find('.gallery-column')

					// Button
					const filterBtn = that.find('.filter-btn-wrapper .filter-btn')

					// Isotope
					that.imagesLoaded( function() {
		                gallery.isotope({ 
		             		itemSelector : '.gallery-item',
		                  	animationOptions: { 
		                      	duration: 750, 
		                      	easing: 'linear', 
		                      	queue: false, 
		                	},
		                	layoutMode: 'masonry',
		                    percentPosition: true,
		                    masonry: {
		                        columnWidth: '.gallery-item',
		                        gutter: 30
		                    }
		                });
		            });

					// Filter
					filterBtn.click( function() {
			            $('.filter-btn-wrapper .filter-btn').removeClass('active-category');
			            $(this).addClass('active-category');      

			            // Get selector
		                const selector = $(this).attr('data-slug'); 
		                gallery.isotope({ 
	                     	filter: selector, 
	                      	animationOptions: { 
	                          	duration: 750, 
	                          	easing: 'linear', 
	                          	queue: false, 
	                    	},
	                    	layoutMode: 'masonry',
	                        percentPosition: true,
	                        masonry: {
	                            columnWidth: '.gallery-item',
	                            gutter: 30
	                        }
		                });  

			            return false;
	        		}); 

					// Fancybox
	        		Fancybox.bind('[data-fancybox="gallery-filter"]', {
					   	Image: {
					    	zoom: false,
					  	},
					});
			    });
		    });
        });
   });

})(jQuery);