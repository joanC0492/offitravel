(function($){
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {

        /* Product Tabs */
        elementorFrontend.hooks.addAction('frontend/element_ready/ovabrw_product_tabs.default', function(){

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

            $('.item-tour-plan').each( function() {

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

        });

	});

})(jQuery);