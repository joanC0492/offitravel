(function($){
	"use strict";
	
	$(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/tripgo_elementor_countdown.default', function() {
        	// Get date data
        	const dateData = JSON.parse($('.ova-countdown').attr('data-date'));

        	// Get year
        	const year = parseInt(dateData.year) || 0;

        	// Get month
        	const month = parseInt(dateData.month) || 0;

        	// Get day
        	const day = parseInt(dateData.day) || 0;

        	// Get hours
        	const hours = parseInt(dateData.hours) || 0;

        	// Get minutes
        	const minutes = parseInt(dateData.minutes) || 0;

        	// Get timezone
        	const timezone = dateData.timezone;

        	// Get text day
        	const textDay = dateData.textDay;

        	// Get text hour
        	const textHour = dateData.textHour;

        	// Get text min
        	const textMin = dateData.textMin;

        	// Get text second
        	const textSec = dateData.textSec;

        	// Today
			let austDay = new Date(); 
			austDay 	= new Date(year, month - 1, day, hours, minutes); 

			// init countdown
			$('.ova-countdown').countdown({
				until: austDay,
				timezone: timezone,
				layout:`<div class="item">
					<div class="number">{dnn}</div>
					<div class="text">${textDay}</div>
				</div>
				<div class="item">
					<div class="number">{hnn}</div>
					<div class="text">${textHour}</div>
				</div>
				<div class="item">
					<div class="number">{mnn}</div>
					<div class="text">${textMin}</div>
				</div>
				<div class="item">
					<div class="number">{snn}</div>
					<div class="text">${textSec}</div>
				</div>`
			});
	    });
   	});

})(jQuery);
