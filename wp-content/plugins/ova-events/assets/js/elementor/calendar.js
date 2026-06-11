(function($){
    "use strict";
	
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/ova_events_calendar.default', function() {
			$('.ovaev_fullcalendar').each( function( e) {
	          	var events = $(this).attr('full_events');
	          	var fullCalendar = $(this).find('.ovaev_events_fullcalendar')[0];
	          	var lang  = $(this).data('lang');
	          	var button_text = $(this).data('button-text');
	          	var no_events_text = $(this).data('no-events-text');
	          	var all_day_text = $(this).data('all-day-text');
	          	var first_day = $(this).data('first-day');

	          	if ( events && events.length > 0 ) {
	            	events = JSON.parse( events );
	          	}

	          	// Filter event
	          	var srcCalendar = new FullCalendar.Calendar(fullCalendar, {
	            	eventDidMount: function(info) {
		              	var tooltip = new Tooltip(info.el, {
		                	title: info.event.extendedProps.desc,
		                	placement: 'top',
		                	trigger: 'hover',
		                	container: 'body',
		                	html:true
		              	});
		            },
		            buttonText: button_text,
		            noEventsText: no_events_text,
		            allDayText: all_day_text,
		            firstDay: first_day,
		            locale: lang,
		            timeZone: 'local',
		            editable: true,
		            navLinks: true,
		            dayMaxEvents: true,
		            events: events,
		            eventColor: '#ff3514',
		            contentHeight: 'auto',
		            headerToolbar: {
		               	left: 'prev,next today',
		               	center: 'title',
		               	right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek',
		            }
	          	});
	          	srcCalendar.render();

	          	// filter event
	          	var datetime = Date.now();
	          	var calendar_filter_event = $(this).find("#calendar_filter_event").val();

	          	var events_filter = [];
	          	$(this).find('#calendar_filter_event').on('change',function () {
		            calendar_filter_event = $(this).val();
		            srcCalendar.getEvents().forEach( event => event.remove() );

		            if ( calendar_filter_event == 'all' ) {
		              	$.each( events, function( key, value ) {
		                	srcCalendar.addEvent(value);
		              	});
		            } else if ( calendar_filter_event == 'past_event' ) {
		              	$.each( events, function( key, value ) {
		                	var end_date = new Date(value['end']).getTime();
		                	if ( end_date < datetime ) {
		                  		srcCalendar.addEvent(value);
		                	}
		              	});
		            } else if ( calendar_filter_event == 'upcoming_event' ) {
		              	$.each( events, function( key, value ) {
		                	var start_date = new Date(value['start']).getTime();
		                	if ( start_date > datetime ) {
		                  		srcCalendar.addEvent(value);
		                	}
		              	});
		            } else {
		              	$.each( events, function( key, value ) {
		                	var special = value['special'];
		                	if ( special == 'checked' ) {
		                  		srcCalendar.addEvent(value);
		                	}
		              	});
		            }
		        });
	        });
		});
	});

})(jQuery)