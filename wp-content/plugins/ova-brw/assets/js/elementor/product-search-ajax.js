(function($){
	"use strict";
	

	$(window).on('elementor/frontend/init', function () {

		elementorFrontend.hooks.addAction('frontend/element_ready/ovabrw_search_ajax.default', function(){

			/* Search Ajax */
			$(".ovabrw-search-ajax .wrap-search-ajax").each(function(){

				// price filter slider
				var price_wrap = $(this).find('.brw-tour-price-input');
				var price_from = $(this).find('.brw-tour-price-from');
				var price_to   = $(this).find('.brw-tour-price-to');
				var min        = price_from.data('value') ? price_from.data('value') : 0 ;
				var max        = price_to.data('value') ? price_to.data('value') : 500 ;
				var symbol     = price_wrap.data('currency_symbol');
				
	            $("#brw-tour-price-slider").slider({
		            range: true,
				    min: min,
				    max: max,
				    values: [ min, max ],
				    slide: function( event, ui ) {
				        $( ".brw-tour-price-from" ).val(ui.values[0]);
				        $( ".brw-tour-price-to" ).val(ui.values[1] );
				    },
				    stop: function( event, ui ) {
				    	var auto = $(this).closest('.search-advanced-content').find('.brw-tour-price-input').data('auto');

				    	if ( auto ) {
				    		$(this).closest('form').find('.ovabrw-search-btn button').click();
				    	}
				    }
				});

				$(".brw-tour-price-from").change(function () {
				    var value = $(this).val();
				    $("#brw-tour-price-slider").slider("values", 0, value);
				});

				$(".brw-tour-price-to").change(function () {
				    var value = $(this).val();
				    $("#brw-tour-price-slider").slider("values", 1, value);
				});  
	            
	            // Advanded search toggle
				function advanced_search_toggle(){

					var btn = $('.ovabrw-search-advanced .search-advanced-input');

					btn.on('click', function () {
			        	$(this).closest('.ovabrw-search-advanced').find('.search-advanced-field-wrapper').toggleClass('toggled');
			        	$(this).toggleClass( 'active' );

		                // change icon
			        	if ( $(this).hasClass('active') ) {
			        		$(this).find('i').removeClass('icomoon-chevron-down');
			        		$(this).find('i').addClass('icomoon-chevron-up');
			        	} else {
			        		$(this).find('i').removeClass('icomoon-chevron-up');
			        		$(this).find('i').addClass('icomoon-chevron-down');
			        	}
			        });
			    } 

			    advanced_search_toggle();

			    // Sort by filer dropdown
				function sort_by_filter_dropdown(){

					var sort_by  		      = $('.ovabrw-tour-filter .input_select_input');
					var sort_by_value  	 	  = $('.ovabrw-tour-filter .input_select_input_value');
					var term_item 		      = $('.ovabrw-tour-filter .input_select_list .term_item');
					var sort_by_text_default  = $('.ovabrw-tour-filter .input_select_list .term_item_selected').data('value');
					var sort_by_value_default = $('.ovabrw-tour-filter .input_select_list .term_item_selected').data('id');	
	                
	                sort_by.attr('value',sort_by_text_default);
					sort_by_value.attr('value',sort_by_value_default);

					sort_by.on('click', function () {
			        	$(this).closest('.filter-sort').find('.input_select_list').toggle();
			        	$(this).toggleClass( 'active' );
			        });

			        $('.ovabrw-tour-filter .asc_desc_sort').on('click', function () {
			        	$(this).closest('.ovabrw-tour-filter').find('.input_select_list').toggle();
			        	$(this).closest('.ovabrw-tour-filter').find('.input_select_input').toggleClass( 'active' );
			        });

			        term_item.on('click', function () {
			        	$(this).closest('.ovabrw-tour-filter').find('.input_select_list').hide();

			        	// change term item selected
			        	var item_active   = $('.ovabrw-tour-filter .input_select_list .term_item_selected').data('id');
			            var item          = $(this).data('id');
			            if ( item != item_active ) {
			                term_item.removeClass('term_item_selected');
			                $(this).addClass('term_item_selected');
			            }

			            // get value, id sort by
			            var sort_value = $(this).data('id');
			            var sort_label = $(this).data('value');

		                // change input select text
			        	sort_by.val(sort_label);
			        	// change input value
			        	sort_by_value.val(sort_value);
			        });
			    } 

			    sort_by_filter_dropdown();    

		    });

 			if ( $('.ovabrw-search-ajax .wrap-search-ajax').length > 0 ) {
                // Cargar resultados iniciales por instancia
                $('.ovabrw-search-ajax .wrap-search-ajax').each(function() {
                    loadAjaxSearch(null, $(this));
                });
            }

            $('.ovabrw-search-ajax .wrap-search-ajax .ovabrw-btn').on('click', function(e) {
                var wrap = $(this).closest('.wrap-search-ajax');
                loadAjaxSearch( true, wrap );

                var resultEl = wrap.find('.brw-search-ajax-result');
                if ( resultEl.length ) {
                    $('html, body').animate({
                        scrollTop: resultEl.offset().top - 250
                    }, 500);
                }

                // hide avanced search dropdown and change icon
                var advanced_search       = $(this).closest('.wrap-search-ajax').find('.search-advanced-field-wrapper');
                var advanced_search_input = advanced_search.closest('.ovabrw-search-advanced').find('.search-advanced-input i');
                advanced_search.removeClass('toggled');
                advanced_search_input.removeClass('icomoon-chevron-up');
                advanced_search_input.addClass('icomoon-chevron-down');
                
                // hide filter sort by dropdown 
                $(this).closest('.wrap-search-ajax').find('.input_select_list').hide();

                e.preventDefault();
            });

            /* Result Layout */
            $('.ovabrw-search-ajax').on('click', '.wrap-search-ajax .filter-layout' , function(e) {
                e.preventDefault();

                var that          = $(this);
                var wrap          = that.closest('.wrap-search-ajax');
                var layout_active = wrap.find('.filter-layout-active').attr('data-layout');
                var layout        = that.attr('data-layout');
                var clicked       = wrap.find('.ovabrw-products-result').data('clicked');

                if ( layout != layout_active ) {
                    wrap.find('.filter-layout').removeClass('filter-layout-active');
                    that.addClass('filter-layout-active');

                    if ( clicked ) {
                        loadAjaxSearch( true, wrap );
                    } else {
                        loadAjaxSearch( null, wrap );
                    }
                }
            });

            /* Sort by */
            $('.ovabrw-search-ajax').on('click', '.wrap-search-ajax .ovabrw-tour-filter .input_select_list .term_item' , function(e) {
                e.preventDefault();

                var that          = $(this);
                var sort_by_value = that.closest('.filter-sort').find('.input_select_input_value').val();
                var search_result = that.closest('.wrap-search-ajax').find('.brw-search-ajax-result');
                var wrap          = that.closest('.wrap-search-ajax');
                var clicked       = wrap.find('.ovabrw-products-result').data('clicked');

                if( sort_by_value == 'date') {
                    search_result.data('order','DESC');
                    search_result.data('orderby','date');
                    search_result.data('orderby_meta_key','');
                } else if( sort_by_value == 'rating_desc' ) {
                    search_result.data('order','DESC');
                    search_result.data('orderby','meta_value_num');
                    search_result.data('orderby_meta_key','_wc_average_rating');
                } else if( sort_by_value == 'price_asc' ) {
                    search_result.data('order','ASC');
                    search_result.data('orderby','meta_value_num');
                    search_result.data('orderby_meta_key','_price');
                } else if( sort_by_value == 'price_desc' ) {
                    search_result.data('order','DESC');
                    search_result.data('orderby','meta_value_num');
                    search_result.data('orderby_meta_key','_price');
                }

                if ( clicked ) {
                    loadAjaxSearch( true, wrap );
                } else {
                    loadAjaxSearch( null, wrap );
                }
            });

            /* Pagination */
            $(document).on('click', '.ovabrw-search-ajax .wrap-search-ajax .ovabrw-pagination-ajax .page-numbers', function(e) {
                e.preventDefault();

                var that    = $(this);
                var wrap    = that.closest('.wrap-search-ajax');
                var current = wrap.find('.ovabrw-pagination-ajax .current').attr('data-paged');
                var paged   = that.attr('data-paged');
                var clicked = that.closest('.brw-search-ajax-result').find('.ovabrw-products-result').data('clicked');

                if ( current != paged ) {
                    $(window).scrollTop(0);
                    wrap.find('.ovabrw-pagination-ajax .page-numbers').removeClass('current');
                    that.addClass('current');

                    if ( clicked ) {
                        loadAjaxSearch( true, wrap );
                    } else {
                        loadAjaxSearch( null, wrap );
                    }
                }
            });

            // Event click clear filter
            $(".ovabrw-tour-filter .clear-filter").on( "click", function(e) {
                e.preventDefault();
                var clear_btn       = $(this);
                var wrap_search     = clear_btn.closest('.wrap-search-ajax');
                var adults          = wrap_search.data('adults');
                var childrens       = wrap_search.data('childrens');
                var babies          = wrap_search.data('babies');
                var sort_by_default = wrap_search.data('sort_by_default');
                var start_price     = wrap_search.data('start-price');
                var end_price       = wrap_search.data('end-price');

                //reset data-paged
                clear_btn.closest('.wrap-search-ajax').find('.ovabrw-pagination-ajax').attr('data-paged', 1);

                // reset all input search bar
                wrap_search.find('#brw-destinations-select-box, .brw_custom_taxonomy_dropdown').val("all").trigger("change");
                var $mp = wrap_search.find('.ovabrw-monthpicker-start');
                if ( $mp.length ) {
                    $mp.val( null ).trigger( 'change' );
                } else {
                    wrap_search.find( '[name="ovabrw_pickup_date"]' ).val( '' ).trigger( 'change' );
                }

                wrap_search.find('input[name="ovabrw_adults"]').val(adults);
                wrap_search.find('input[name="ovabrw_childrens"]').val(childrens);
                wrap_search.find('input[name="ovabrw_babies"]').val(babies);

                if ( typeof adults === "undefined" || ! adults ) {
                    adults = 0;
                }

                if ( typeof childrens === "undefined" || ! childrens ) {
                    childrens = 0;
                }

                if ( typeof babies === "undefined" || ! babies ) {
                    babies = 0;
                }

                wrap_search.find('.ovabrw-guestspicker .gueststotal').html(adults + childrens + babies);

                wrap_search.find('.search-advanced-field-wrapper input:checkbox, .search-advanced-field-wrapper input:radio').removeAttr('checked');

                wrap_search.find('.brw-tour-price-from').val(start_price);
                wrap_search.find('.brw-tour-price-to').val(end_price);
                wrap_search.find('#brw-tour-price-slider .ui-slider-range').css({"left":"0","width":"100%"});
                wrap_search.find('#brw-tour-price-slider  span').css("left","100%");
                wrap_search.find('#brw-tour-price-slider .ui-slider-range + span').css("left","0");

                // reset sort by
                wrap_search.find('.input_select_list .term_item ').removeClass('term_item_selected');
                wrap_search.find('.input_select_list .term_item[data-id="'+sort_by_default+'"]').addClass('term_item_selected');

                var input_select_text = wrap_search.find('.input_select_list .term_item[data-id="'+sort_by_default+'"]').data('value');
                wrap_search.find('.input_select_input').val(input_select_text);
                wrap_search.find('.input_select_input_value').val(sort_by_default);

                var search_result = wrap_search.find('.brw-search-ajax-result');
                if ( sort_by_default == 'date' ) {
                    search_result.data('order','DESC');
                    search_result.data('orderby','date');
                    search_result.data('orderby_meta_key','');
                } else if( sort_by_default == 'rating_desc' ) {
                    search_result.data('order','DESC');
                    search_result.data('orderby','meta_value_num');
                    search_result.data('orderby_meta_key','_wc_average_rating');
                }  
                else if( sort_by_default == 'price_asc' ) {
                    search_result.data('order','ASC');
                    search_result.data('orderby','meta_value_num');
                    search_result.data('orderby_meta_key','_price');
                } else if( sort_by_default == 'price_desc' ) {
                    search_result.data('order','DESC');
                    search_result.data('orderby','meta_value_num');
                    search_result.data('orderby_meta_key','_price');
                }      

                loadAjaxSearch( null, wrap_search );
            });

            /* Video & Gallery */
            function video_popup( that ) {

                // Video
                var btn_video = that.find('.btn-video');

                // btn video click
                btn_video.each( function() {
                    $(this).on( 'click', function() {
                        var video_container = $(this).closest('.ova-video-gallery').find('.video-container');
                        var modal_close     = $(this).closest('.ova-video-gallery').find('.ovaicon-cancel');
                        var modal_video     = $(this).closest('.ova-video-gallery').find('.modal-video');

                        var url         = get_url( $(this).data('src') );
                        var controls    = $(this).data('controls');
                        var option      = '?';
                        option += ( 'yes' == controls.autoplay ) ? 'autoplay=1'     : 'autoplay=0';
                        option += ( 'yes' == controls.mute )    ? '&mute=1'     : '&mute=0';
                        option += ( 'yes' == controls.loop )    ? '&loop=1'     : '&loop=0';
                        option += ( 'yes' == controls.controls ) ? '&controls=1' : '&controls=0';
                        option += ( 'yes' == controls.rel )         ? '&rel=1'      : '&rel=0';
                        option += ( 'yes' == controls.modest )  ? '&modestbranding=1' : '&modestbranding=0';

                        if ( url != 'error' ) {
                            option += '&playlist='+url;
                            modal_video.attr('src', "https://www.youtube.com/embed/" + url + option );
                            video_container.css('display', 'flex');
                        }

                        // close video
                        modal_close.on('click', function() {
                            video_container.hide();
                            modal_video.removeAttr('src');
                        });

                        // window click
                        $(window).click( function(e) {
                            if ( e.target.className == 'video-container' ) {
                                video_container.hide();
                                modal_video.removeAttr('src');
                            }
                        });
                    });
                });
            }

            function get_url( url ) {
                var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=)([^#\&\?]*).*/;
                var match = url.match(regExp);

                if (match && match[2].length == 11) {
                    return match[2];
                } else {
                    return 'error';
                }
            }

            $(document).find(".wrap-search-ajax .brw-search-ajax-result .ova-video-gallery").each( function() {
                var that = $(this);
            });

            /* Product Gallery Fancybox */
            function product_gallery_fancybox( that ) {
                var btn_gallery = that.find('.btn-gallery');

                btn_gallery.on('click', function(){
                    var gallery_data = $(this).data('gallery');
                    Fancybox.show(gallery_data, {
                        Image: {
                            Panzoom: {
                                zoomFriction: 0.7,
                                maxScale: function () {
                                    return 3;
                                },
                            },
                        },
                    });
                });
            }

            function product_gallery_slider() {
                $('.ova-gallery-slideshow').each( function() {
                    var that    = $(this);
                    var options = that.data('options') ? that.data('options') : {};

                    var responsive_value = {
                        0:{
                            items:1,
                            nav:false,
                            slideBy: 1,
                        },
                        768:{
                            items: 2,
                            slideBy: 1,
                        },
                        1025:{
                            items: 3,
                            slideBy: 1,
                        },
                        1300:{
                            items: options.items,
                        }
                    };
                    
                    that.owlCarousel({
                        autoWidth: options.autoWidth,
                        margin: options.margin,
                        items: options.items,
                        loop: options.loop,
                        autoplay: options.autoplay,
                        autoplayTimeout: options.autoplayTimeout,
                        center: options.center,
                        lazyLoad: options.lazyLoad,
                        nav: options.nav,
                        dots: options.dots,
                        autoplayHoverPause: options.autoplayHoverPause,
                        slideBy: options.slideBy,
                        smartSpeed: options.smartSpeed,
                        rtl: options.rtl,
                        navText:[
                            '<i aria-hidden="true" class="'+ options.nav_left +'"></i>',
                            '<i aria-hidden="true" class="'+ options.nav_right +'"></i>'
                        ],
                        responsive: responsive_value,
                    });

                    that.find('.gallery-fancybox').off('click').on('click', function() {
                        var index = $(this).data('index');
                        var gallery_data = $(this).closest('.ova-gallery-popup').find('.ova-data-gallery').data('gallery');

                        Fancybox.show(gallery_data, {
                            Image: {
                                Panzoom: {
                                    zoomFriction: 0.7,
                                    maxScale: function () {
                                        return 3;
                                    },
                                },
                            },
                            startIndex: index,
                        });
                    });
                });
            }

            $(document).find(".wrap-search-ajax .brw-search-ajax-result .ova-video-gallery").each( function() {
                var that = $(this);
            });

            /* load ajax search tour */
            function loadAjaxSearch( clicked = null, wrap = null ) {
                // Si no se pasa un contenedor, iterar todas las instancias
                if ( !wrap ) {
                    $(document).find('.ovabrw-search-ajax .wrap-search-ajax').each(function() {
                        loadAjaxSearch(clicked, $(this));
                    });
                    return;
                }

                var that            = wrap;
                var layout          = that.find('.filter-layout-active').attr('data-layout');
                var grid_column     = that.data('grid_column');
                var thumbnailType   = that.data('thumbnail-type');
                
                var destination     = that.find('#brw-destinations-select-box :selected').val();

                var custom_taxonomy = [];
                var taxonomy_value  = [];

                that.find(".brw_custom_taxonomy_dropdown").each(function (index) {
                    var nameTaxonomy    = $(this).attr('name');
                    var valueTaxonomy   = $(this).val();
                    custom_taxonomy[index]  = nameTaxonomy; 
                    taxonomy_value[index]   = valueTaxonomy;
                });

                var $monthPick = that.find('.ovabrw-monthpicker-start');
                var start_date = '';
                if ( $monthPick.length ) {
                    var mv = $monthPick.val();
                    start_date = Array.isArray( mv ) ? mv.filter( Boolean ).join( ',' ) : ( mv || '' );
                } else {
                    start_date = that.find('[name="ovabrw_pickup_date"]').val() || '';
                }
                if ( !start_date ) {
                    try {
                        var params = new URLSearchParams(window.location.search);
                        start_date = params.get('ovabrw_pickup_date') || params.get('pickup_date') || '';
                    } catch (e) {
                        start_date = '';
                    }
                }
                var adults          = that.find('input[name="ovabrw_adults"]').val();
                var childrens       = that.find('input[name="ovabrw_childrens"]').val();
                var babies          = that.find('input[name="ovabrw_babies"]').val();
                var start_price     = that.find('.brw-tour-price-from').val();
                var end_price       = that.find('.brw-tour-price-to').val();
                var review_score    = [];
                var categories      = [];
                var duration_from   = that.find('.duration-filter:checked').val();
                var duration_to     = that.find('.duration-filter:checked').nextAll('.duration-filter-to').val();
                var duration_type   = that.find('.duration-filter:checked').nextAll('.duration-filter-type').val();

                that.find(".rating-filter:checked").each(function (index) {
                    review_score[index] = $(this).val(); 
                });
                
                that.find(".tour-category-filter:checked").each(function (index) {
                    categories[index] = $(this).val();
                });

                var result           = that.find('.brw-search-ajax-result');
                var order            = result.data('order');
                var orderby          = result.data('orderby');
                var orderby_meta_key = result.data('orderby_meta_key');
                var posts_per_page   = result.data('posts-per-page');
                var default_category = result.data('defautl-category');
                var show_category    = result.data('show-category');
                var paged            = result.find('.ovabrw-pagination-ajax .current').attr('data-paged');

                that.find('.wrap-load-more').show();

                var data_ajax   = {
                    action: 'ovabrw_search_ajax',
                    order: order,
                    orderby: orderby,
                    orderby_meta_key: orderby_meta_key,
                    posts_per_page: posts_per_page,
                    default_category: default_category,
                    show_category: show_category,
                    paged: paged,
                    layout: layout,
                    grid_column: grid_column,
                    thumbnail_type: thumbnailType,
                    destination: destination,
                    custom_taxonomy: custom_taxonomy,
                    taxonomy_value: taxonomy_value,
                    start_date: start_date,
                    adults: adults,
                    childrens: childrens,
                    babies: babies,
                    start_price: start_price,
                    end_price: end_price,
                    review_score: review_score,
                    categories: categories,
                    duration_from: duration_from,
                    duration_to: duration_to,
                    duration_type: duration_type,
                    clicked: clicked,
                };

                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: data_ajax,
                    success:function(response) {
                        if( response ){
                            var json = JSON.parse( response );
                            var item = $(json.result).fadeOut(300).fadeIn(500);
                            result.html(item);

                            // update number results found
                            var number_results_found =  result.find('.tour_number_results_found').val();

                            if ( number_results_found == undefined ) {
                                number_results_found = 0 ;
                            };

                            result.closest('.wrap-search-ajax').find('.number-result-tour-found').html('').append( number_results_found  );
                            
                            // hide icon loading ajax
                            that.find('.wrap-load-more').hide();
                            video_popup( that );
                            product_gallery_fancybox( that );
                            product_gallery_slider();
                        }
                    },
                });
            }
        
        });

	});
})(jQuery);