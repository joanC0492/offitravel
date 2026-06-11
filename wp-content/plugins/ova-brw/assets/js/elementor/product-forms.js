(function($){
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/ovabrw_product_forms.default', function() {
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
        });
	});

})(jQuery);