(function($){
	"use strict";

	if ( typeof order_time != 'undefined' ) {
	 	document.addEventListener('DOMContentLoaded', function() {
			$('.ovabrw__product_calendar').each( function(e) {

			    var id = $(this).data('id');

			    var srcCalendarEl = document.getElementById(id);
			    if ( srcCalendarEl === null ) return;

			    var nav = srcCalendarEl.getAttribute('data-nav');
			    var default_view = srcCalendarEl.getAttribute('data-default_view');
			    var cal_lang = srcCalendarEl.getAttribute( 'data-lang' ).replace(/\s/g, '');
			    var data_event_number = parseInt( srcCalendarEl.getAttribute('data_event_number') );
			    var events = order_time;
			    
			    var srcCalendar = new FullCalendar.Calendar(srcCalendarEl, {
			        editable: true,
			        events: events,
			        eventDisplay: 'block',
			        height: '100%',
			        headerToolbar: {
			            left: 'prev,next,today,' + nav,
			            right: 'title',
			        },
			        initialView: default_view,
			        locale: cal_lang,
			        firstDay: 1,
			        dayMaxEventRows: true, // for all non-TimeGrid views
		          	views: {
			           	dayGrid: {
			                dayMaxEventRows: data_event_number
			              // options apply to dayGridMonth, dayGridWeek, and dayGridDay views
			            },
			            timeGrid: {
			                dayMaxEventRows: data_event_number
			              // options apply to timeGridWeek and timeGridDay views
			            },
			            week: {
			                dayMaxEventRows: data_event_number
			              // options apply to dayGridWeek and timeGridWeek views
			            },
			            day: {
			                dayMaxEventRows: data_event_number
			              // options apply to dayGridDay and timeGridDay views
			            }
			        },
			    });

			    srcCalendar.render();
			});
		}); 
	}
}) (jQuery);