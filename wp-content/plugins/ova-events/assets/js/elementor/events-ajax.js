(function($){
    "use strict";
	
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/ova_events_ajax.default', function() {
			$('.ovapo_project_grid').each( function() {
	            const sliderEl = $(this).find('.grid');
	            if ( sliderEl.length > 0 ) {
	                eventSlider(sliderEl);
	            }

	            // Add class: active
	            $(this).find('.button-filter button:first-child').addClass('active');
	            $(this).find('.button-filter').each( function() {
	                const projectGrid = $(this).closest('.ovapo_project_grid');

	                // Get items
	                const items = projectGrid.find('.items');

	                $(this).on('click', 'button', function(e) {
	                    e.preventDefault();

	                    $(this).parent().find('.active').removeClass('active');
	                    $(this).addClass('active');

	                    // Get filter
	                    const filter = $(this).data('filter');

	                    // Get order
	                    const order = $(this).data('order');

	                    // Get orderby
	                    const orderby = $(this).data('orderby');

	                    // Get number post
	                    const numberPost = $(this).data('number_post');

	                    // Get layout
	                    const layout = $(this).data('layout');

	                    // Get first term
	                    const firstTerm = $(this).data('first_term');

	                    // Get term id
	                    const termId = $(this).data('term_id_filter_string');

	                    // Show featured
	                    const showFeatured = $(this).data('show_featured');

	                    // Slide options
	                    const slideOptions = items.data('options');

	                    projectGrid.find('.wrap_loader').fadeIn(100);
	                
	                    $.ajax({
	                        url: ajax_object.ajax_url,
	                        type: 'POST',
	                        data: ({
	                            action: 'filter_elementor_grid',
	                            filter: filter,
	                            order: order,
	                            orderby: orderby,
	                            number_post: numberPost,
	                            layout: layout,
	                            first_term: firstTerm,
	                            term_id_filter_string: termId,
	                            show_featured: showFeatured,
	                            slide_options: slideOptions
	                        }),
	                        success: function(response) {
	                            projectGrid.find('.wrap_loader').fadeOut(200);
	                            items.html(response).fadeIn(300);
	                            
	                            // Slider
	                            if ( sliderEl.length > 0 ) {
	                                eventSlider(sliderEl);
	                            }
	                        }
	                    })
	                });
	            });
	        });

	        // Event slider
	        function eventSlider( that = null ) {
	            if ( !that || !that.length ) return;
	            if ( typeof Swiper !== 'function' ) return;

	            // Slider options
	            const sliderOpts = that.data('options');

	            // Slider element
	            const sliderEl = that.find('.swiper')[0];

	            // Swiper wrap
	            const swiperWrapper = $(sliderEl).find('.swiper-wrapper');

	            // Slider
	            const slides = swiperWrapper.find('.swiper-slide');

	            // Slider data
	            let sliderData = {
	                loop: sliderOpts.loop,
	                loopAddBlankSlides: false,
	                speed: sliderOpts.speed || 500,
	                slidesPerGroup: sliderOpts.slidesPerGroup,
	                slidesPerView: sliderOpts.slidesPerView,
	                spaceBetween: sliderOpts.spaceBetween,
	                centeredSlides: sliderOpts.centeredSlides,
	                rtl: sliderOpts.rtl,
	                breakpoints: sliderOpts.breakpoints,
	                on: {
	                    beforeInit(swiper) {
	                        // Get number of items
	                        let numberOfItems = slides.length;

	                        if ( sliderOpts.loop ) {
	                            // Flag
	                            let flag = ( slides.length > 1 && numberOfItems == swiper.params.slidesPerView ) ? 1 : 0;

	                            // Loop
	                            while ( numberOfItems <= swiper.params.slidesPerView ) {
	                                if ( flag == slides.length ) flag = 0;

	                                // Clone item
	                                swiperWrapper.append(slides[flag].cloneNode(true));

	                                // Update number of items
	                                numberOfItems = swiper.el.querySelectorAll('.swiper-slide').length;

	                                flag++;
	                            }
	                        } else if ( sliderOpts.nav ) {
	                            if ( numberOfItems <= swiper.params.slidesPerView ) {
	                                that.find('.button-nav').hide();
	                            } else {
	                                that.find('.button-nav').show();
	                            }
	                        }
	                    },
	                    init(swiper) {
	                        // Remove class 'swiper-loading'
	                        $(sliderEl).removeClass('swiper-loading');
	                    }
	                }
	            };

	            // Autoplay
	            if ( sliderOpts.autoplay ) {
	                sliderData['autoplay'] = {
	                    delay: sliderOpts.delay || 3000,
	                    disableOnInteraction: false,
	                    pauseOnMouseEnter: sliderOpts.pauseOnMouseEnter
	                };
	            }

	            // Navigation
	            if ( sliderOpts.nav ) {
	                sliderData['navigation'] = {
	                    nextEl: that.find('.button-next')[0],
	                    prevEl: that.find('.button-prev')[0],
	                };
	            }

	            // Pagination
	            if ( sliderOpts.dots ) {
	                sliderData['pagination'] = {
	                    el: that.find('.button-dots')[0],
	                    clickable: true,
	                    dynamicBullets: true,
	                    dynamicMainBullets: 3
	                };
	            }

	            // New swiper
	            const swiper = new Swiper(sliderEl, sliderData);
	        } // END func
		});
	});

})(jQuery)