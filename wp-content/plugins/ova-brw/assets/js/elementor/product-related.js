(function($){
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/ovabrw_product_related.default', function() {
            $('.elementor-ralated-slide .elementor-ralated').each( function() {
                if ( typeof Swiper !== 'function' ) return;

                const that = $(this);

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
                            if ( sliderOpts.loop ) {
                                // Get number of items
                                let numberOfItems = slides.length;

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
            });
        });
	});

})(jQuery);