(function($){
    "use strict";
	
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/ova_events_simple_calendar.default', function() {
			let calendars = {};

            $('.ovaev_simple_calendar').each( function(e) {
                const thisMonth 	= moment().format('YYYY-MM');
                const daysOfTheWeek = $(this).data('days-of-the-week');
                let events 			= $(this).attr('events');
                if ( events && events.length > 0 ) {
                    events = JSON.parse( events );
                }

                // Events to load into calendar
                calendars.clndr1 = $(this).find('.ovaev_events_simple_calendar').clndr({
                    events: events,
                    daysOfTheWeek: daysOfTheWeek,
                    clickEvents: {
                        click: function (target) {
                            const eve = target.events;
                            location.assign(eve[0].url);
                        }
                    },
                  	multiDayEvents: {
                      	singleDay: 'date',
                      	endDate: 'endDate',
                      	startDate: 'startDate'
                  	},
                  	showAdjacentMonths: true,
                  	adjacentDaysChangeMonth: false
              	});

               	$(document).keydown( function(e) {
                  	// Left arrow
                  	if (e.keyCode == 37) {
                    	calendars.clndr1.back();
                  	}

                  	// Right arrow
                  	if (e.keyCode == 39) {
                    	calendars.clndr1.forward();
                  	}
              	});
            });
		});
	});

})(jQuery)