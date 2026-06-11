jQuery(function( $ ){

	'use strict';

	/***** Menu Tab *****/
	$( function() {
		$( "#tabs" ).tabs();
	} );
	/***** End Menu Tab *****/

	$( function() {
		jQuery(document).ready( function( $ ) {

		    $('#archive_event_bg_button').on( 'click', function() {

		        formfield = $('#archive_event_bg_upload').attr('name');
		        tb_show( '', 'media-upload.php?type=image&amp;TB_iframe=true' );
		        window.send_to_editor = function(html) {
		           imgurl = $(html).attr('src');
		           $('#archive_event_bg_upload').val(imgurl);
		           tb_remove();
		        }

		        return false;
		    });

		});
	})

	/***** Color Picker *****/
	$( function() {
		jQuery(document).ready(function($){
			$('.colorpick').wpColorPicker();
		});
	});
	/***** End Color Picker *****/

	$( function() {
		jQuery(document).ready( function( $ ) {

		    $('#single_event_bg_button').on( 'click', function() {

		        formfield = $('#single_event_bg_upload').attr('name');
		        tb_show( '', 'media-upload.php?type=image&amp;TB_iframe=true' );
		        window.send_to_editor = function(html) {
		           imgurl = $(html).attr('src');
		           $('#single_event_bg_upload').val(imgurl);
		           tb_remove();
		        }

		        return false;
		    });

		});
	})

	/***** Date Time Picker *****/
	$( function($) {
      if($().datetimepicker) {
         $('.ovaev_start_date, .ovaev_end_date').each(function(){
            var lang = $(this).data('lang');

            if ( lang ) {
            	$.datetimepicker.setLocale(lang);
            }

            var date_format = 'd-m-Y';

            if ( $(this).data('date') ) {
            	date_format = $(this).data('date');
            }

            switch( date_format ) {
	            case 'd-m-Y':
	                date_format = 'DD-MM-Y';
	                break;
	            case 'm/d/Y':
	                date_format = 'MM/DD/Y';
	                break;
	            case 'Y/m/d':
	                date_format = 'Y/MM/DD';
	                break;
	            case 'Y-m-d':
	                date_format = 'Y-MM-DD';
	                break;
	            default:
	                date_format = 'DD-MM-Y';
	        }

            var firstDay = $(this).data('first-day');

            $(this).datetimepicker({
            	timepicker: false,
               	format: date_format,
               	formatDate: date_format,
               	dayOfWeekStart: firstDay,
               	scrollInput: false,
               	disabledWeekDays: [],
               	disabledDates: [],
               	scrollInput: false
            });
         });
      } 
   });
	/***** End Date Time Picker *****/

	/***** Date Time Picker *****/
	$( function($) {
      	if($().datetimepicker) {

         	$('.ovaev_time_picker').each(function(){
            	var lang = $(this).data('lang');

            	if ( lang ) {
            		$.datetimepicker.setLocale(lang);
            	}

            	var step_time;

	            if ( typeof brw_step_time !== 'undefined' ) {
	                step_time = parseInt(brw_step_time);
	            } else {
	                step_time = 30;
	            }

	            if ( step_time == '' ) step_time = 30;

            	$.datetimepicker.setDateFormatter({
	                parseDate: function (date, format) {
	                    var d = moment(date, format);
	                    return d.isValid() ? d.toDate() : false;
	                },
	                
	                formatDate: function (date, format) {
	                    return moment(date).format(format);
	                },
	            });

            	var time_format = $(this).data('time');

            	if ( ! time_format ) time_format = 'H:i';

	            switch( time_format ){
	                case 'H:i':
	                    time_format = 'HH:mm';
	                    break;
	                case 'h:i':
	                    time_format = 'hh:mm';
	                    break;
	                case 'h:i a':
	                    time_format = 'hh:mm a';
	                    break;
	                case 'h:i A':
	                    time_format = 'hh:mm A';
	                    break;
	                case 'G:i':
	                    time_format = 'H:mm';
	                    break;
	                case 'g:i':
	                    time_format = 'h:mm';
	                    break;
	                case 'g:i a':
	                    time_format = 'h:mm a';
	                    break;
	                case 'g:i A':
	                    time_format = 'h:mm A';
	                    break;
	                default:
	                    time_format = 'H:mm';
	            }

            	$(this).datetimepicker({
	            	datepicker: false,
                    format: time_format,
                    formatTime: time_format,
					scrollInput: false,
               		step: step_time,
            	});
         });
      	} 
   	});
	/***** End Date Time Picker *****/


	/***** Show Hiden Link *****/
	$( function($) {
		$('#ovaev_book').each(function(){
			var valueSelected = this.value;
			(valueSelected == 'extra_link' ) ? $('#ovaev_book_link').css('display', 'inline-block') : $('#ovaev_book_link').css('display', 'none');
			(valueSelected == 'extra_link' ) ? $('#ovaev_target_book').css('display', 'inline-block') : $('#ovaev_target_book').css('display', 'none');
		});
		$('#ovaev_book').on('change', function (e) {
			var valueSelected = this.value;
			(valueSelected == 'extra_link' ) ? $('#ovaev_book_link').css('display', 'inline-block') : $('#ovaev_book_link').css('display', 'none');
			(valueSelected == 'extra_link' ) ? $('#ovaev_target_book').css('display', 'inline-block') : $('#ovaev_target_book').css('display', 'none');
		});
	} );
	/***** End Show Hiden Link *****/

	/***** Upload Image *****/
	$( function() {
		var file_frame;
		$(document).on('click', '#metabox-event-gallery a.gallery-add', function(e) {

			e.preventDefault();

			if (file_frame) file_frame.close();

			file_frame = wp.media.frames.file_frame = wp.media({
				title: $(this).data('uploader-title'),
				button: {
					text: $(this).data('uploader-button-text'),
				},
				multiple: true
			});

			file_frame.on('select', function() {
				var listIndex = $('#gallery-metabox-list li').index($('#gallery-metabox-list li:last')),
				selection = file_frame.state().get('selection');

				selection.map(function(attachment, i) {
					attachment = attachment.toJSON();
					var index      = listIndex + (i + 1);
					var url 	   = attachment.sizes.full.url;
					if ( $(attachment.sizes.thumbnail).length > 0 ) {
						url = attachment.sizes.thumbnail.url;
					}

					$('#gallery-metabox-list').append('<li><input type="hidden" name="ovaev_gallery_id[' + index + ']" value="' + attachment.id + '"><img class="image-preview" src="' + url + '"><a class="change-image button button-small" href="#" data-uploader-title="Change image" data-uploader-button-text="Change image">Change image</a><small><a class="remove-image" href="#">Remove image</a></small></li>');
				});
			});

			makeSortable();

			file_frame.open();
		});

		$(document).on('click', '#metabox-event-gallery a.change-image', function(e) {

			e.preventDefault();

			var that = $(this);

			if (file_frame) file_frame.close();

         file_frame = wp.media.frames.file_frame = wp.media({
            title: $(this).data('uploader-title'),
            button: {
               text: $(this).data('uploader-button-text'),
            },
            multiple: false
         });

         file_frame.on( 'select', function() {
            attachment = file_frame.state().get('selection').first().toJSON();

            that.parent().find('input:hidden').attr('value', attachment.id);
            that.parent().find('img.image-preview').attr('src', attachment.sizes.thumbnail.url);
         });

         file_frame.open();
      });

		function resetIndex() {
			$('#metabox-event-gallery #gallery-metabox-list li').each(function(i) {
				$(this).find('input:hidden').attr('name', 'ovaev_gallery_id[' + i + ']');
			});
		}

		function makeSortable() {
			$('#metabox-event-gallery #gallery-metabox-list').sortable({
				opacity: 0.6,
				stop: function() {
					resetIndex();
				}
			});
		}

		$(document).on('click', '#metabox-event-gallery a.remove-image', function(e) {
			e.preventDefault();

			$(this).parents('li').animate({ opacity: 0 }, 200, function() {
				$(this).remove();
				resetIndex();
			});
		});

		makeSortable();
	} );
	/***** End Upload Image *****/

	/***** Templates Single Event *****/
	$( function() {
		if ( $("#ovaev_get_template_single").length > 0 ) {
			$("#ovaev_get_template_single").select2();
		}
	});

	$( function() {
		if ( $("#ovaev_event_templates").length > 0 ) {
			$("#ovaev_event_templates").select2();
		}
	});
	/***** End Templates Single Event *****/

}); 	