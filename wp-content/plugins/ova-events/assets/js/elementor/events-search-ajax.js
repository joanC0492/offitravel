(function($){
    "use strict";
	
	$(window).on('elementor/frontend/init', function () {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/ova_events_search_ajax.default', function() {
			$('.ovaev-wrapper-search-ajax').each( function(e) {
		        var that = $(this);
		        var search_ajax = that.find('.search-ajax-content');
		        var data_events = that.find('.data-events');
		        var pagination  = that.find('.search-ajax-pagination-wrapper');
		        var search_form = that.find('.ovaev-search-ajax-form');
		        var select      = that.find('.ovaev_type');
		        if ( select.length > 0 ) {
		            select.select2();
		        };

		        // When form change
		        search_form.on('change', function(e) {
		            e.preventDefault();

		            var form 		= $(this);
		            var start_date  = form.find('input[name="ovaev_start_date_search"]').val();
		            var end_date    = form.find('input[name="ovaev_end_date_search"]').val();
		            var category    = form.find('select[name="ovaev_type"]').val();
		            var layout      = data_events.data('layout');
		            var column      = data_events.data('column');
		            var per_page    = data_events.data('per-page');
		            var order       = data_events.data('order');
		            var orderby     = data_events.data('orderby');
		            var cat_slug    = data_events.data('category-slug');
		            var time_event  = data_events.data('time-event');

		            that.find('.wrap_loader').fadeIn(100);

		            $.ajax({
		              	url: ajax_object.ajax_url,
		              	type: 'POST',
		              	data: ({
		                	action: 'search_ajax_events',
			                start_date: start_date,
			                end_date  : end_date,
			                category  : category,
			                layout    : layout,
			                column    : column,
			                per_page  : per_page,
			                order     : order,
			                orderby   : orderby,
			                cat_slug  : cat_slug,
			                time_event: time_event,
		              	}),
		              	success: function(response){
		                	var data = JSON.parse(response);
		                	that.find('.wrap_loader').fadeOut(200);
		                	search_ajax.html('').append(data['result']).fadeIn(300);
		                	pagination.html('').append(data['pagination']).fadeIn(300);
		              	},
		            });
		        });

		         // When click pagination
		         $(document).on( 'click', '.ovaev-wrapper-search-ajax .search-ajax-pagination-wrapper .search-ajax-pagination .page-numbers', function(e) {
		            e.preventDefault();

		            var page = $(this);
		            var that_page     = page.closest('.ovaev-wrapper-search-ajax');
		            var current       = page.closest('.search-ajax-pagination').find('.current').data('paged');
		            var current_page  = page.closest('.search-ajax-pagination').find('.current');
		            var offset        = page.attr('data-paged');
		            var total_page    = page.closest('.search-ajax-pagination').data('total-page');

		            if ( offset != current ) {
		              	var start_date  = page.closest('.ovaev-wrapper-search-ajax').find('input[name="ovaev_start_date_search"]').val();
		              	var end_date    = page.closest('.ovaev-wrapper-search-ajax').find('input[name="ovaev_end_date_search"]').val();
		              	var category    = page.closest('.ovaev-wrapper-search-ajax').find('select[name="ovaev_type"]').val();
		              	var layout      = page.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('layout');
		              	var column      = page.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('column');
		              	var per_page    = page.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('per-page');
		              	var order       = page.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('order');
		              	var orderby     = page.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('orderby');
		              	var cat_slug    = page.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('category-slug');
		              	var time_event  = page.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('time-event');

		              	that_page.find('.wrap_loader').fadeIn(100);

		              	$.ajax({
		                	url: ajax_object.ajax_url,
		                	type: 'POST',
		                	data: ({
				                action: 'search_ajax_events_pagination',
				                start_date: start_date,
				                end_date: end_date,
				                category: category,
				                layout: layout,
				                column: column,
				                per_page: per_page,
				                order: order,
				                orderby: orderby,
				                cat_slug: cat_slug,
				                time_event: time_event,
				                offset: offset,
		                	}),
			                success: function(response){
			                  	var data = JSON.parse(response);

			                  	that_page.find('.wrap_loader').fadeOut(200);
			                  	that_page.find('.search-ajax-content').html('').append(data['result']).fadeIn(300);
			                  	page.closest('.search-ajax-pagination').find('.page-numbers').removeClass('current');

			                  	if ( page.hasClass('next') ) {
			                    	current_page.closest('li').next().children('.page-numbers').addClass('current');
			                  	} else if ( page.hasClass('prev') ) {
			                    	current_page.closest('li').prev().children('.page-numbers').addClass('current');
			                  	} else {
			                    	page.addClass('current');
			                  	}

			                  	if ( parseInt(offset) > 1 ) {
			                    	page.closest('.search-ajax-pagination').find('.prev').attr('data-paged', parseInt(offset)-1);
			                    	page.closest('.search-ajax-pagination').find('.prev').css('display', 'inline-flex');
			                  	} else {
			                    	page.closest('.search-ajax-pagination').find('.prev').attr('data-paged', 0);
			                    	page.closest('.search-ajax-pagination').find('.prev').css('display', 'none');
			                  	}
			                  
			                  	if ( parseInt(offset) == parseInt(total_page) ) {
			                    	page.closest('.search-ajax-pagination').find('.next').attr('data-paged', parseInt(offset));
			                    	page.closest('.search-ajax-pagination').find('.next').css('display', 'none');
			                  	} else {
			                    	page.closest('.search-ajax-pagination').find('.next').attr('data-paged', parseInt(offset)+1);
			                    	page.closest('.search-ajax-pagination').find('.next').css('display', 'inline-flex');
			                  	}
			               	},
		              	});
		            }
		        });
		    });
		});
	});

})(jQuery)