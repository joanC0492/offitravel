(function($){
    "use strict";

    if ( typeof( OVA_MegaMenu ) == "undefined") {
        var OVA_MegaMenu = {}; 
    }

    // init
    OVA_MegaMenu.init = function(){
        this.FrontEnd.init();
    }
    
    // Metabox
    OVA_MegaMenu.FrontEnd = {
        init: function() {
            this.megamenu();  
        },
        megamenu: function() {
            // Get window width
            const windowWidth = $(window).width();

            // Get container widget
            const containerWidth = $('.ovamegamenu_container_default').width();

            $('ul.ova-mega-menu.sub-menu').each(function() {
                const offsetLeft    = $(this).offset().left;
                const offsetRight   = windowWidth - ( offsetLeft + $(this).outerWidth() );   

                $(this).css('max-width', containerWidth);
                if ( $('body').hasClass('rtl') ) {
                    $(this).css({ right: '0', left: '100%' });
                    $(this).css('width', windowWidth - offsetRight - 30);
                } else {
                    $(this).css('width', windowWidth - offsetLeft - 30); 
                }
            });
        }   
    };

    $(document).ready(function(){
        OVA_MegaMenu.init();
    });

    $(window).resize(function(){
        OVA_MegaMenu.FrontEnd.megamenu(); 
    });

})(jQuery);