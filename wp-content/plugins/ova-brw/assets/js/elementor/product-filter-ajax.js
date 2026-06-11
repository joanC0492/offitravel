(function($) {
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/ovabrw_product_filter_ajax.default', function() {
            $('.ova-product-filter-ajax').each( function() {
                const that = $(this);

                // Button
                const btn = that.find('.product-filter-button');

                // Tern active
                const termActive = that.find('.active-category').data('slug');
                
                // Show on salse
                const showOnSale = that.data('show_on_sale');

                // Arguments show
                const argsShow = JSON.parse(that.attr('data-args_show'));

                // Posts per page
                const postsPerPage = that.data('posts_per_page');

                // Orderby
                const orderBy = that.data('orderby');

                // Order
                const order = that.data('order');

                // Load init
                productFilterLoadAjax(termActive, showOnSale, argsShow, postsPerPage, orderBy, order);
                
                // Button click
                btn.each( function() {
                    $(this).on('click', function() {
                        // Remove class active
                        that.find('.product-filter-button').removeClass('active-category');

                        // Add class active
                        $(this).addClass('active-category');

                        // Get term
                        const term = $(this).attr('data-slug');

                        // Loading
                        productFilterLoadAjax(term, showOnSale, argsShow, postsPerPage, orderBy, order);
                    });
                });  
            });
        });
        
        /**
         * Product filter ajax slide
         */
        function productFilterAjaxSlide() {
            $('.ova-product-filter-ajax .slide-product').each( function() {
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
                            // Get number of items
                            let numberOfItems = slides.length;

                            // loop = true
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
                                    that.find('.swiper-nav').hide();
                                } else {
                                    that.find('.swiper-nav').show();
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
        } // END

        /**
         * Product filter load ajax
         */
        function productFilterLoadAjax( term, showOnSale, argsShow, postsPerPage, orderBy, order ) {
            // Slide option
            const slideOpts = $('.ova-product-filter-ajax .content-item').data('options');

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: ({
                    action: 'ovabrw_load_product_filter',
                    term: term,
                    show_on_sale: showOnSale,
                    args_show: argsShow,
                    posts_per_page: postsPerPage,
                    orderby: orderBy,
                    order: order,
                    slide_options: slideOpts
                }),
                success: function( data ) {
                    if ( '' != data ) {
                        $('.ova-product-filter-ajax .content-item').empty();
                        $('.ova-product-filter-ajax .content-item').append(data).fadeIn(300);
                        
                        // Ajax slide
                        productFilterAjaxSlide();
                    }
                }
            });
        } // END
	}); // END
})(jQuery);