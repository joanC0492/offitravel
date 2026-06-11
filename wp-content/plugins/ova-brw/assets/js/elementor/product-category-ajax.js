(function($){
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/ovabrw_product_category_ajax.default', function() {
            $('.ovabrw-category-ajax').each( function() {
                loadProducts($(this));
            });

            $('.ovabrw-category-ajax .ovabrw-category-list .category-item').on( 'click', function(e) {
                e.preventDefault();

                if ( ! $(this).hasClass('active') ) {
                    $(this).closest('.ovabrw-category-list').find('.category-item').removeClass('active');
                    $(this).addClass('active');
                    $(this).closest('.ovabrw-category-ajax').find('.page-numbers').removeClass('current');

                    // Category ajax
                    const categoryAjax = $(this).closest('.ovabrw-category-ajax');
                    loadProducts(categoryAjax);
                }
            });

            $(document).on( 'click', '.ovabrw-category-ajax .ovabrw-category-products .ovabrw-pagination-ajax .page-numbers', function(e) {
                e.preventDefault();
                const current = $(this).closest('.ovabrw-pagination-ajax').data('paged');
                const paged   = $(this).data('paged');

                if ( current != paged ) {
                    $('html, body').animate({
                        scrollTop: $(this).closest('.ovabrw-category-ajax').offset().top - 100
                    }, 300 );
                    $(this).closest('.ovabrw-pagination-ajax').find('.page-numbers').removeClass('current');
                    $(this).addClass('current');

                    // Category ajax
                    const categoryAjax = $(this).closest('.ovabrw-category-ajax');
                    loadProducts(categoryAjax);
                }
            });

            // Load products
            function loadProducts( that = null ) {
                if ( !that && !that.length ) return;

                // Result
                const result = that.find('.ovabrw-category-products');

                // Term id
                const termID = that.find('.category-item.active').data('term-id');

                // Icon load more
                const loading = that.find('.wrap-load-more');

                // Ajax input
                const dataInput = that.find('input[name="category-ajax-input"]');

                // Posts per page
                const postsPerPage = dataInput.data('posts-per-page');

                // Order
                const order = dataInput.data('order');

                // Orderby
                const orderBy = dataInput.data('orderby');

                // Layout
                const layout = dataInput.data('layout');

                // Grid template
                const gridTemplate = dataInput.data('grid_template');

                // Column
                const column = dataInput.data('column');

                // Thumbnail type
                const thumbnailType = dataInput.data('thumbnail-type');

                // Pagination
                const pagination = dataInput.data('pagination');

                // Paged
                const paged = that.find('.ovabrw-pagination-ajax .current').data('paged');

                loading.show();

                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'ovabrw_product_category_ajax',
                        term_id: termID,
                        posts_per_page: postsPerPage,
                        paged: paged,
                        order: order,
                        orderBy: orderBy,
                        layout: layout,
                        grid_template: gridTemplate,
                        column: column,
                        thumbnail_type: thumbnailType,
                        pagination: pagination
                    },
                    success:function(response) {
                        if ( response ) {
                            const json = JSON.parse( response );
                            result.html(json.result).fadeOut(300).fadeIn(500);
                            productGallerySlider();
                        }

                        loading.hide();
                    },
                });
            }

            // Product gallery slider
            function productGallerySlider() {
                $('.ova-gallery-slideshow').each( function() {
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

                    // Fancybox
                    that.find('.gallery-fancybox').off('click').on('click', function() {
                        const index = $(this).data('index');

                        // Gallery data
                        const galleryData = $(this).closest('.ova-gallery-popup').find('.ova-data-gallery').data('gallery');

                        Fancybox.show(galleryData, {
                            Image: {
                                Panzoom: {
                                    zoomFriction: 0.7,
                                    maxScale: function () {
                                        return 3;
                                    }
                                }
                            },
                            startIndex: index
                        });
                    });
                });
            }
        });
	});
})(jQuery);