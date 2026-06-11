(function( $ ) {
    'use strict';
    
    // For View Map in destination detail
    function initMap( $el ) {
        // Find marker elements within map.
        var $markers = $el.find('.marker');

        // Create gerenic map.
        var mapArgs = {
            zoom        : $el.data('zoom') || 16,
            mapTypeId   : google.maps.MapTypeId.ROADMAP
        };
        var map = new google.maps.Map( $el[0], mapArgs );

        // Add markers.
        map.markers = [];
        $markers.each(function(){
            initMarker( $(this), map );
        });

        // Center map based on markers.
        centerMap( map );

        // Return map instance.
        return map;
    }

    function initMarker( $marker, map ) {
        // Get position from marker.
        var lat = $marker.data('lat');
        var lng = $marker.data('lng');
        var latLng = {
            lat: parseFloat( lat ),
            lng: parseFloat( lng )
        };

        // Create marker instance.
        var marker = new google.maps.Marker({
            position : latLng,
            map: map
        });

        // Append to reference for later use.
        map.markers.push( marker );

        // If marker contains HTML, add it to an infoWindow.
        if ( $marker.html() ) {

            // Create info window.
            var infowindow = new google.maps.InfoWindow({
                content: $marker.html()
            });

            // Show info window when marker is clicked.
            google.maps.event.addListener(marker, 'click', function() {
                infowindow.open( map, marker );
            });
        }
    }

    function centerMap( map ) {
        // Create map boundaries from all map markers.
        var bounds = new google.maps.LatLngBounds();
        map.markers.forEach(function( marker ){
            bounds.extend({
                lat: marker.position.lat(),
                lng: marker.position.lng()
            });
        });

        // Case: Single marker.
        if ( map.markers.length == 1 ) {
            map.setCenter( bounds.getCenter() );
        } else {
            map.fitBounds( bounds );
        }
    }

    // Render on page load.
    $(document).ready(function() {
        // Render map
        if ( typeof google == 'object' && typeof google.maps == 'object' ) {
            $('#ova_destination_admin_show_map').each( function() {
                var map = initMap($(this));
            });
        }

        // Mansory Destination Archive
        $('.content-archive-destination').each( function() {
            const grid = $(this);
            const run  = grid.masonry({
                itemSelector: '.item-destination',
                columnWidth: '.grid-sizer',
                gutter: 0,
                percentPosition: true,
                transitionDuration: 0,
            });

            run.imagesLoaded().progress( function() {
                run.masonry();
            });
        });

        // Related Tour Destination
        $('.ova-destination-related-wrapper .ova-product-slider').each( function() {
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
                        if ( sliderOpts.nav ) {
                            // Get number of items
                            let numberOfItems = slides.length;
                            if ( numberOfItems <= swiper.params.slidesPerView ) {;
                                that.find('.button-nav').hide();
                            } else {
                                that.find('.button-nav').css('display', 'flex');
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

})( jQuery );