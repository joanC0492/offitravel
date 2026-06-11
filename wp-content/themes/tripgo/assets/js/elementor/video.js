(function($){
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/tripgo_elementor_video.default', function() {
	        $('.ova-video').each( function() {
	        	const that = $(this);

	        	// Video active
	        	const videoActive = that.find('.video_active');

	        	// Modal
	        	const modalContainer 	= that.find('.modal-container');
	        	const modalClose 		= that.find('.ovaicon-cancel');
	        	const modalVideo 		= that.find('.modal-video');

	        	// Btn video click
	        	videoActive.on( 'click', function() {
	        		const btnVideo 	= $(this).find('.video-btn')
	        		const url 		= get_url(btnVideo.data('src'));
	        		const autoplay 	= btnVideo.data('autoplay');
	        		const mute 		= btnVideo.data('mute');
	        		const loop 		= btnVideo.data('loop');
	        		const controls 	= btnVideo.data('controls');
	        		const modest 	= btnVideo.data('modest');
	        		const showinfo 	= btnVideo.data('show_info');
	        		let option 		= '?';
	        		option += ( 'yes' == autoplay ) ? 'autoplay=1' 	: 'autoplay=0';
	        		option += ( 'yes' == mute ) 	? '&mute=1' 	: '&mute=0';
	        		option += ( 'yes' == loop ) 	? '&loop=1' 	: '&loop=0';
	        		option += ( 'yes' == controls ) ? '&controls=1' : '&controls=0';
	        		option += ( 'yes' == showinfo ) ? '&showinfo=1' : '&showinfo=0';
	        		option += ( 'yes' == modest ) 	? '&modestbranding=1' : '&modestbranding=0';

	        		if ( url != 'error' ) {
	        			modalVideo.attr('src', "https://www.youtube.com/embed/" + url + option );
	        			modalContainer.css('display', 'flex');
	        		}
	        	});

	        	// close video
	        	modalClose.on('click', function() {
	        		modalContainer.hide();
	        		modalVideo.removeAttr('src');
	        	});

	        	// window click
	        	$(window).click( function(e) {
	        		if ( e.target.className == 'modal-container' ) {
	        			modalContainer.hide();
	        			modalVideo.removeAttr('src');
	        		}
				});
	        });

    		function get_url( url ) {
			    const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
			    const match = url.match(regExp);

			    if ( match && match[2].length == 11 ) {
			        return match[2];
			    } else {
			        return 'error';
			    }
			}
        });
   });

})(jQuery);