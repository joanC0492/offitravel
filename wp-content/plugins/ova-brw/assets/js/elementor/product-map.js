(function($){
	"use strict";

	$(window).on('elementor/frontend/init', function () {
        // Product Map
        elementorFrontend.hooks.addAction( 'frontend/element_ready/ovabrw_product_map.default', function() {
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
        }); // END product map
	});
})(jQuery);