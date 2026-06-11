(function($){
	"use strict";

	// Dump die
    function dd(...args) {
        args.forEach( arg => {
            console.log(arg);
        });
    } // END func

	// Login & register forms
	$('.ova-my-account-button a').on( 'click', function() {
		const type = $(this).data('type');

		// Remove active class
		$('.ova-login-register-woo').find('li').removeClass('active');

		// Add active class
		$('.ova-login-register-woo').find('a[data-type="'+type+'"]').closest('li').addClass('active');

		// Show form
		if ( 'login' === type ) {
			$('.woocommerce #customer_login .woocommerce-form.woocommerce-form-login').css('display', 'block');
			$('.woocommerce #customer_login .woocommerce-form.woocommerce-form-register').css('display', 'none');
		} else if( type === 'register' ){
			$('.woocommerce #customer_login .woocommerce-form.woocommerce-form-register').css('display', 'block');
			$('.woocommerce #customer_login .woocommerce-form.woocommerce-form-login').css('display', 'none');
		}
	});

	$('.ova-login-register-woo').each( function() {
		// Switch forms
		$(this).find('li a').on( 'click', function() {
			const type = $(this).data('type');

			// Remove active class
			$(this).closest('.ova-login-register-woo').find('li').removeClass('active');

			// Add active class
			$(this).closest('li').addClass('active');

			// Show form
			if ( 'login' === type ) {
				$('.woocommerce #customer_login .woocommerce-form.woocommerce-form-login').css('display', 'block');
				$('.woocommerce #customer_login .woocommerce-form.woocommerce-form-register').css('display', 'none');
			} else if( type === 'register' ){
				$('.woocommerce #customer_login .woocommerce-form.woocommerce-form-register').css('display', 'block');
				$('.woocommerce #customer_login .woocommerce-form.woocommerce-form-login').css('display', 'none');
			}
		}); // END click

		// Get current URL
		if ( window.location.href.indexOf('#register') !== -1 ) {
			$(this).find('a[data-type="register"]').click();
		} else if ( window.location.href.indexOf('#login') !== -1 ) {
			$(this).find('a[data-type="login"]').click();
		}
	}); // END login & register form

	// URL change
	$(window).on('hashchange', function() {
	    if ( window.location.href.indexOf('#register') !== -1 ) {
			$('.ova-login-register-woo a[data-type="register"]').click();
		} else if ( window.location.href.indexOf('#login') !== -1 ) {
			$('.ova-login-register-woo a[data-type="login"]').click();
		}
	});

	/* Video & Gallery */
	$('.ova-video-gallery').each( function() {
    	var that = $(this);

    	// Video
    	var btn_video 		= that.find('.btn-video');
    	var video_container = that.find('.video-container');
    	var modal_close 	= that.find('.ovaicon-cancel');
    	var modal_video 	= that.find('.modal-video');

    	// btn video click
    	btn_video.on( 'click', function() {
    		var url 		= get_url( $(this).data('src') );
    		var controls 	= $(this).data('controls');
    		var option		= '?';
    		option += ( 'yes' == controls.autoplay ) ? 'autoplay=1' 	: 'autoplay=0';
    		option += ( 'yes' == controls.mute ) 	? '&mute=1' 	: '&mute=0';
    		option += ( 'yes' == controls.loop ) 	? '&loop=1' 	: '&loop=0';
    		option += ( 'yes' == controls.controls ) ? '&controls=1' : '&controls=0';
    		option += ( 'yes' == controls.rel ) 		? '&rel=1' 		: '&rel=0';
    		option += ( 'yes' == controls.modest ) 	? '&modestbranding=1' : '&modestbranding=0';

    		if ( url != 'error' ) {
    			option += '&playlist='+url;
    			modal_video.attr('src', "https://www.youtube.com/embed/" + url + option );
    			video_container.css('display', 'flex');
    		}
    	});

    	// close video
    	modal_close.on('click', function() {
    		video_container.hide();
    		modal_video.removeAttr('src');
    	});

    	// window click
    	$(window).click( function(e) {
    		if ( e.target.className == 'video-container' ) {
    			video_container.hide();
    			modal_video.removeAttr('src');
    		}
		});

		// Gallery
		var btn_gallery = that.find('.btn-gallery');

        btn_gallery.on('click', function(){
        	var gallery_data = $(this).data('gallery');
            Fancybox.show(gallery_data, {
            	Image: {
				    Panzoom: {
				      	zoomFriction: 0.7,
				      	maxScale: function () {
				        	return 3;
				      	},
				    },
			  	},
			});
        });
    });

	function get_url( url ) {
	    var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
	    var match = url.match(regExp);

	    if (match && match[2].length == 11) {
	        return match[2];
	    } else {
	        return 'error';
	    }
	}

	/* Gallery Slideshow */
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

			// Get gallery data
			const galleryData = $(this).closest('.ova-gallery-popup').find('.ova-data-gallery').data('gallery');

			Fancybox.show(galleryData, {
            	Image: {
				    Panzoom: {
				      	zoomFriction: 0.7,
				      	maxScale: function () {
				        	return 3;
				      	},
				    },
			  	},
			  	startIndex: index,
			});
		});
	});

	/* Forms */
	$('.ova-forms-product').each( function() {
        const that = $(this);
        const item = that.find('.tabs .item');

        if ( item.length > 0 ) {
            item.each( function( index ) {
                if ( index == 0 ) {
                    $(this).addClass('active');
                    const id = $(this).data('id');
                    that.find(id).show();
                }
            });
        }

        // item click
        item.on('click', function() {
            item.removeClass('active');
            $(this).addClass('active');

            // Get id
            const id = $(this).data('id');
            if ( id == '#booking-form' ) {
                that.find('#request-form').hide();
            } else if ( id == '#request-form' ) {
                that.find('#booking-form').hide();
            }
            
            // Show form
            that.find(id).show();
        });
    });

	/* Tabs */
	$('.ova-tabs-product').each( function() {
		var that = $(this);
		var item = that.find('.tabs .item');

		if ( item.length > 0 ) {
			item.each( function( index ) {
			  	if ( index == 0 ) {
			  		$(this).addClass('active');
			  		var id = $(this).data('id');
			  		$(id).show();
			  	}
			});
		}

		item.on('click', function() {
			item.removeClass('active');
			$(this).addClass('active');
			var id = $(this).data('id');

			if ( id == '#tour-description' ) {
				that.find('#tour-included-excluded, #tour-plan, #ova-tour-map, #ova-tour-review ').hide();
			}

			if ( id == '#tour-included-excluded' ) {
				that.find('#tour-description, #tour-plan, #ova-tour-map, #ova-tour-review ').hide();
			}

			if ( id == '#tour-plan' ) {
				that.find('#tour-included-excluded, #tour-description, #ova-tour-map, #ova-tour-review ').hide();
			}

			if ( id == '#ova-tour-map' ) {
				that.find('#tour-included-excluded, #tour-plan, #tour-description, #ova-tour-review ').hide();
			}

			if ( id == '#ova-tour-review' ) {
				that.find('#tour-included-excluded, #tour-plan, #ova-tour-map, #tour-description ').hide();
			}
			
			$(id).show();
		});
	});

	/* Tour Plan Toggled */
	$('.ova-content-single-product .item-tour-plan').each( function() {

		var that = $(this);
		var item = that.find('.tour-plan-title');

		item.on('click', function() {
			$(this).closest('.item-tour-plan').toggleClass('active');
			// change icon
        	if ( that.hasClass('active') ) {
        		$(this).find('i').removeClass('icomoon-chevron-down');
        		$(this).find('i').addClass('icomoon-chevron-up');
        	} else {
        		$(this).find('i').removeClass('icomoon-chevron-up');
        		$(this).find('i').addClass('icomoon-chevron-down');
        	}
		});

	});

	// Tour Location
	$('.tripgo-tour-map').each( async function() {
        // Get google loaded
        const googleLoaded = await ovabrwGoogleLoaded();

        // That
        var that = $(this);

        // Map data
        var mapData = that.find('input[name="ovabrw-map-data"]');
        
        // Address
        var address = mapData.val();

        // Latitude
        var latitude = mapData.data('latitude');

        // Longitude
        var longitude = mapData.data('longitude');

        // Zoom
        var zoom = mapData.data('zoom');
        if ( !zoom ) zoom = 17;
        
        if ( googleLoaded && latitude && longitude ) {
            var map = new google.maps.Map( $('#tour-show-map')[0], {
                center: {
                    lat: parseFloat(latitude),
                    lng: parseFloat(longitude)
                },
                zoom: zoom,
                gestureHandling: 'cooperative',
            });

            // infowindow
            var infowindow = new google.maps.InfoWindow({
               content: address,
            });

            // Marker
            var marker = new google.maps.Marker({
               map: map,
               position: map.getCenter()
            }); // END

            // Open
            infowindow.open(map, marker);

            // Click
            marker.addListener( 'click', function() {
                infowindow.close();
                infowindow.open(map, marker);
            }); // END
        }
    });

    // Google loaded
    function ovabrwGoogleLoaded() {
        return new Promise((resolve) => {
            const check = setInterval(() => {
                try {
                    if ( typeof google == 'object' && typeof google.maps == 'object' && typeof google.maps.places == 'object' ) {
                        clearInterval(check);
                        resolve(true);
                    }
                } catch (e) {
                    // do something
                }
            }, 100 );

            // Stop check google map after 5s
            setTimeout(() => {
                clearInterval(check);
                resolve(false);
            }, 1500 ); // END
        });
    } // END google loaded

	$('.ova-content-single-product .elementor-ralated-slide .elementor-ralated').each( function() {
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
 
})(jQuery);