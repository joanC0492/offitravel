(function($) {
    "use strict";

    $(document).ready(function() {
        // Gallery PrettyPhoto
        if ( $(".gallery-items a[data-gal^='prettyPhoto']").length > 0 ) {
            $("a[data-gal^='prettyPhoto']").prettyPhoto({hook: 'data-gal', theme: 'facebook',slideshow:5000, autoplay_slideshow:true});
        } // END

        // Date Time Picker
        $('.ovaev_start_date_search, .ovaev_end_date_search').each( function() {
            if ( $().datetimepicker ) {
                const lang = $(this).data('lang');
                if ( lang ) $.datetimepicker.setLocale(lang);

                // Date format
                let dateFormat = 'd-m-Y';
                if ( $(this).data('date') ) {
                    dateFormat = $(this).data('date');
                }

                // Datetime picker
                $(this).datetimepicker({
                    timepicker: false,
                    format: dateFormat,
                    formatDate: dateFormat,
                    dayOfWeekStart: $(this).data('first-day') || 1,
                    disabledWeekDays: [],
                    disabledDates: [],
                    scrollInput: false
                });
            }
        }); // END

        // Slide event feature
        $('.slide-event-feature').each( function() {
            const that = $(this);

            // Slider element
            const sliderEl = that.find('.swiper')[0];

            // Swiper wrap
            const swiperWrapper = $(sliderEl).find('.swiper-wrapper');

            // Slider
            const slides = swiperWrapper.find('.swiper-slide');

            // Slider data
            let sliderData = {
                loop: true,
                loopAddBlankSlides: false,
                speed: 500,
                slidesPerGroup: 1,
                slidesPerView: 1,
                spaceBetween: 80,
                autoplay: false,
                navigation: false,
                pagination: false,
                rtl: true,
                breakpoints: {
                    0: {
                        slidesPerView: 1
                    },
                    600: {
                        slidesPerView: 1
                    },
                    1000: {
                        slidesPerView: 1
                    }
                }
            };

            // New swiper
            const swiper = new Swiper(sliderEl, sliderData);
        }); // END

        // Select2
        $('.search_archive_event #ovaev_type').on( 'change', function() {
            $(this).closest('.search_archive_event').find('.select2-selection__rendered').css('color', '#333');
        });
        if ( $('.ovaev_type').length > 0 ) {
            $('.ovaev_type').select2();
        }; // END

        // Tab Pane
        function activeTab(obj) {
            $('.tab-Location ul li ').removeClass('active');
            $(obj).addClass('active');
            $('.event_tab-pane').hide();

            // Get id
            const id = $(obj).find('a').data('href');
            $(id).show();
        }
        $('.event_nav-tabs li').on( 'click', function() {
            activeTab(this);
            return false;
        });
        activeTab($('.event_nav-tabs li:first-child')); // END
        
        //calendar
        let calendars = {};
        $('.ovaev_simple_calendar').each( function(e) {
            // Get this month
            const thisMonth = moment().format('YYYY-MM');

            // Days of the week
            const daysOfTheWeek = $(this).data('days-of-the-week');

            // Get events
            let events = $(this).attr('events');
            if ( events && events.length > 0 ) {
               events = JSON.parse(events);
            }

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
                if ( e.keyCode == 37 ) {
                    calendars.clndr1.back();
                }

                // Right arrow
                if ( e.keyCode == 39 ) {
                    calendars.clndr1.forward();
                }
            });
        }); // END

        // Event silde
        $('.ovaev-slide').each( function() {
            if ( typeof Swiper !== 'function' ) return;

            const that = $(this);

            // Slider options
            const sliderOpts = that.data('options');

            // Slider element
            const sliderEl = that.find('.swiper')[0];

            // Swiper wrap
            const swiperWrapper = $(sliderEl).find('.swiper-wrapper');

            // Slider
            const slides = swiperWrapper.find('.swiper-slide');

            // Slider data
            let sliderData = {
                loop: sliderOpts.loop,
                loopAddBlankSlides: false,
                speed: sliderOpts.speed || 500,
                slidesPerGroup: sliderOpts.slidesPerGroup,
                slidesPerView: sliderOpts.slidesPerView,
                spaceBetween: sliderOpts.spaceBetween,
                centeredSlides: sliderOpts.centeredSlides,
                rtl: sliderOpts.rtl,
                breakpoints: sliderOpts.breakpoints,
                on: {
                    beforeInit(swiper) {
                        // Get number of items
                        let numberOfItems = slides.length;
                        
                        if ( sliderOpts.loop ) {
                            // Flag
                            let flag = ( slides.length > 1 && numberOfItems == swiper.params.slidesPerView ) ? 1 : 0;

                            // Loop
                            while ( numberOfItems <= swiper.params.slidesPerView ) {
                                if ( flag == slides.length ) flag = 0;

                                // Clone item
                                swiperWrapper.append(slides[flag].cloneNode(true));

                                // Update number of items
                                numberOfItems = swiper.el.querySelectorAll('.swiper-slide').length;

                                flag++;
                            }
                        } else if ( sliderOpts.nav ) {
                            if ( numberOfItems <= swiper.params.slidesPerView ) {
                                that.find('.button-nav').hide();
                            } else {
                                that.find('.button-nav').show();
                            }
                        }
                    },
                    init(swiper) {
                        // Remove class 'swiper-loading'
                        $(sliderEl).removeClass('swiper-loading');
                    }
                }
            };

            // Autoplay
            if ( sliderOpts.autoplay ) {
                sliderData['autoplay'] = {
                    delay: sliderOpts.delay || 3000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: sliderOpts.pauseOnMouseEnter
                };
            }

            // Navigation
            if ( sliderOpts.nav ) {
                sliderData['navigation'] = {
                    nextEl: that.find('.button-next')[0],
                    prevEl: that.find('.button-prev')[0],
                };
            }

            // Pagination
            if ( sliderOpts.dots ) {
                sliderData['pagination'] = {
                    el: that.find('.button-dots')[0],
                    clickable: true,
                    dynamicBullets: true,
                    dynamicMainBullets: 3
                };
            }

            // New swiper
            const swiper = new Swiper(sliderEl, sliderData);
        }); // END

        // Event ajax
        $('.ovapo_project_grid').each( function() {
            const sliderEl = $(this).find('.grid');
            if ( sliderEl.length > 0 ) {
                eventSlider(sliderEl);
            }

            // Add class: active
            $(this).find('.button-filter button:first-child').addClass('active');
            $(this).find('.button-filter').each( function() {
                const projectGrid = $(this).closest('.ovapo_project_grid');

                // Get items
                const items = projectGrid.find('.items');

                $(this).on('click', 'button', function(e) {
                    e.preventDefault();

                    $(this).parent().find('.active').removeClass('active');
                    $(this).addClass('active');

                    // Get filter
                    const filter = $(this).data('filter');

                    // Get order
                    const order = $(this).data('order');

                    // Get orderby
                    const orderby = $(this).data('orderby');

                    // Get number post
                    const numberPost = $(this).data('number_post');

                    // Get layout
                    const layout = $(this).data('layout');

                    // Get first term
                    const firstTerm = $(this).data('first_term');

                    // Get term id
                    const termId = $(this).data('term_id_filter_string');

                    // Show featured
                    const showFeatured = $(this).data('show_featured');

                    // Slide options
                    const slideOptions = items.data('options');

                    projectGrid.find('.wrap_loader').fadeIn(100);
                
                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'POST',
                        data: ({
                            action: 'filter_elementor_grid',
                            filter: filter,
                            order: order,
                            orderby: orderby,
                            number_post: numberPost,
                            layout: layout,
                            first_term: firstTerm,
                            term_id_filter_string: termId,
                            show_featured: showFeatured,
                            slide_options: slideOptions
                        }),
                        success: function(response) {
                            projectGrid.find('.wrap_loader').fadeOut(200);
                            items.html(response).fadeIn(300);
                            
                            // Slider
                            if ( sliderEl.length > 0 ) {
                                eventSlider(sliderEl);
                            }
                        }
                    })
                });
            });
        });

        // Event slider
        function eventSlider( that = null ) {
            if ( !that || !that.length ) return;
            if ( typeof Swiper !== 'function' ) return;

            // Slider options
            const sliderOpts = that.data('options');

            // Slider element
            const sliderEl = that.find('.swiper')[0];

            // Swiper wrap
            const swiperWrapper = $(sliderEl).find('.swiper-wrapper');

            // Slider
            const slides = swiperWrapper.find('.swiper-slide');

            // Slider data
            let sliderData = {
                loop: sliderOpts.loop,
                loopAddBlankSlides: false,
                speed: sliderOpts.speed || 500,
                slidesPerGroup: sliderOpts.slidesPerGroup,
                slidesPerView: sliderOpts.slidesPerView,
                spaceBetween: sliderOpts.spaceBetween,
                centeredSlides: sliderOpts.centeredSlides,
                rtl: sliderOpts.rtl,
                breakpoints: sliderOpts.breakpoints,
                on: {
                    beforeInit(swiper) {
                        // Get number of items
                        let numberOfItems = slides.length;

                        if ( sliderOpts.loop ) {
                            // Flag
                            let flag = ( slides.length > 1 && numberOfItems == swiper.params.slidesPerView ) ? 1 : 0;

                            // Loop
                            while ( numberOfItems <= swiper.params.slidesPerView ) {
                                if ( flag == slides.length ) flag = 0;

                                // Clone item
                                swiperWrapper.append(slides[flag].cloneNode(true));

                                // Update number of items
                                numberOfItems = swiper.el.querySelectorAll('.swiper-slide').length;

                                flag++;
                            }
                        } else if ( sliderOpts.nav ) {
                            if ( numberOfItems <= swiper.params.slidesPerView ) {
                                that.find('.button-nav').hide();
                            } else {
                                that.find('.button-nav').show();
                            }
                        }
                    },
                    init(swiper) {
                        // Remove class 'swiper-loading'
                        $(sliderEl).removeClass('swiper-loading');
                    }
                }
            };

            // Autoplay
            if ( sliderOpts.autoplay ) {
                sliderData['autoplay'] = {
                    delay: sliderOpts.delay || 3000,
                    disableOnInteraction: false,
                    pauseOnMouseEnter: sliderOpts.pauseOnMouseEnter
                };
            }

            // Navigation
            if ( sliderOpts.nav ) {
                sliderData['navigation'] = {
                    nextEl: that.find('.button-next')[0],
                    prevEl: that.find('.button-prev')[0],
                };
            }

            // Pagination
            if ( sliderOpts.dots ) {
                sliderData['pagination'] = {
                    el: that.find('.button-dots')[0],
                    clickable: true,
                    dynamicBullets: true,
                    dynamicMainBullets: 3
                };
            }

            // New swiper
            const swiper = new Swiper(sliderEl, sliderData);
        } // END func

        // Full calendar
        $('.ovaev_fullcalendar').each( function(e) {
            // Full calendar
            const fullCalendar = $(this).find('.ovaev_events_fullcalendar')[0];

            // Get language
            const lang = $(this).data('lang');

            // Get button text
            const buttonText = $(this).data('button-text');

            // No events text
            const noEventsText = $(this).data('no-events-text');

            // All day text
            const allDayText = $(this).data('all-day-text');

            // First day
            const firstDay = $(this).data('first-day');

            // Get events
            let events = $(this).attr('full_events');
            if ( events && events.length > 0 ) {
                events = JSON.parse(events);
            }

            // new calendar
            const srcCalendar = new FullCalendar.Calendar(fullCalendar, {
                eventDidMount: function(info) {
                    const tooltip = new Tooltip(info.el, {
                        title: info.event.extendedProps.desc,
                        placement: 'top',
                        trigger: 'hover',
                        container: 'body',
                        html:true
                    });
                },
                buttonText: buttonText,
                noEventsText: noEventsText,
                allDayText: allDayText,
                firstDay: firstDay,
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
                },
            });

            srcCalendar.render();

            // Date time now
            const datetime = Date.now();

            // Calendar filter event
            const calendarFilterEvent = $(this).find("#calendar_filter_event").val();

            $(this).find('#calendar_filter_event').on('change',function () {
                calendarFilterEvent = $(this).val();
                srcCalendar.getEvents().forEach( event => event.remove() );

                if ( calendarFilterEvent == 'all' ) {
                    $.each( events, function( key, value ) {
                        srcCalendar.addEvent(value);
                    });
                } else if ( calendarFilterEvent == 'past_event' ) {
                    $.each( events, function( key, value ) {
                        const endDate = new Date(value['end']).getTime();
                        if ( endDate < datetime ) {
                            srcCalendar.addEvent(value);
                        }
                    });
                } else if ( calendarFilterEvent == 'upcoming_event' ) {
                    $.each( events, function( key, value ) {
                        const startDate = new Date(value['start']).getTime();
                        if ( startDate > datetime ) {
                            srcCalendar.addEvent(value);
                        }
                    });
                } else {
                    $.each( events, function( key, value ) {
                        const special = value['special'];
                        if ( special == 'checked' ) {
                          srcCalendar.addEvent(value);
                        }
                    });
                }
            });
        }); // END

        // Search Ajax
        $('.ovaev-wrapper-search-ajax').each( function(e) {
            const that = $(this);

            // Search content
            const searchAjax = that.find('.search-ajax-content');

            // Event data
            const eventsData = that.find('.data-events');

            // Pagination
            const pagination = that.find('.search-ajax-pagination-wrapper');

            // Search form
            const searchForm = that.find('.ovaev-search-ajax-form');

            // Type
            const type = that.find('.ovaev_type');
            if ( type.length > 0 ) {
                type.select2();
            };

            // When form change
            searchForm.on( 'change', function(e) {
                e.preventDefault();

                // Form
                const form = $(this);

                // Start date
                const startDate = form.find('input[name="ovaev_start_date_search"]').val();

                // End date
                const endDate = form.find('input[name="ovaev_end_date_search"]').val();

                // Category
                const category = form.find('select[name="ovaev_type"]').val();

                // Layout
                const layout = eventsData.data('layout');

                // Column
                const column = eventsData.data('column');

                // Per page
                const perPage = eventsData.data('per-page');

                // Order
                const order = eventsData.data('order');

                // Orderby
                const orderby = eventsData.data('orderby');

                // Category slug
                const catSlug = eventsData.data('category-slug');

                // Time event
                const timeEvent = eventsData.data('time-event');

                // Loader
                that.find('.wrap_loader').fadeIn(100);

                $.ajax({
                    url: ajax_object.ajax_url,
                    type: 'POST',
                    data: ({
                        action: 'search_ajax_events',
                        start_date: startDate,
                        end_date: endDate,
                        category: category,
                        layout: layout,
                        column: column,
                        per_page: perPage,
                        order: order,
                        orderby: orderby,
                        cat_slug: catSlug,
                        time_event: timeEvent
                    }),
                    success: function(response) {
                        const data = JSON.parse(response);
                        that.find('.wrap_loader').fadeOut(200);
                        searchAjax.html('').append(data['result']).fadeIn(300);
                        pagination.html('').append(data['pagination']).fadeIn(300);
                    },
                });
            });

            // When click pagination
            $(document).on( 'click', '.ovaev-wrapper-search-ajax .search-ajax-pagination-wrapper .search-ajax-pagination .page-numbers', function(e) {
                e.preventDefault();
                const that = $(this);

                // Search wrapper
                const searchWrap = that.closest('.ovaev-wrapper-search-ajax');

                // Paged
                const paged = that.closest('.search-ajax-pagination').find('.current').data('paged');

                // Current page
                const currentPage = that.closest('.search-ajax-pagination').find('.current');

                // Offset
                const offset = that.attr('data-paged');

                // Total page
                const totalPage = that.closest('.search-ajax-pagination').data('total-page');

                if ( offset != paged ) {
                    // Start date
                    const startDate = that.closest('.ovaev-wrapper-search-ajax').find('input[name="ovaev_start_date_search"]').val();

                    // End date
                    const endDate = that.closest('.ovaev-wrapper-search-ajax').find('input[name="ovaev_end_date_search"]').val();

                    // Get category
                    const category = that.closest('.ovaev-wrapper-search-ajax').find('select[name="ovaev_type"]').val();

                    // Get layout
                    const layout = that.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('layout');

                    // Get column
                    const column = that.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('column');

                    // Per page
                    const perPage = that.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('per-page');

                    // Get order
                    const order = that.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('order');

                    // Get orderby
                    const orderby = that.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('orderby');

                    // Get category slug
                    const catSlug = that.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('category-slug');

                    // Get time event
                    const timeEvent = that.closest('.ovaev-wrapper-search-ajax').find('.data-events').data('time-event');

                    // Loader
                    searchWrap.find('.wrap_loader').fadeIn(100);

                    $.ajax({
                        url: ajax_object.ajax_url,
                        type: 'POST',
                        data: ({
                            action: 'search_ajax_events_pagination',
                            start_date: startDate,
                            end_date: endDate,
                            category: category,
                            layout: layout,
                            column: column,
                            per_page: perPage,
                            order: order,
                            orderby: orderby,
                            cat_slug: catSlug,
                            time_event: timeEvent,
                            offset: offset
                        }),
                        success: function(response) {
                            const data = JSON.parse(response);

                            searchWrap.find('.wrap_loader').fadeOut(200);
                            searchWrap.find('.search-ajax-content').html('').append(data['result']).fadeIn(300);
                            that.closest('.search-ajax-pagination').find('.page-numbers').removeClass('current');

                            if ( that.hasClass('next') ) {
                                currentPage.closest('li').next().children('.page-numbers').addClass('current');
                            } else if ( that.hasClass('prev') ) {
                                currentPage.closest('li').prev().children('.page-numbers').addClass('current');
                            } else {
                                that.addClass('current');
                            }

                            if ( parseInt(offset) > 1 ) {
                                that.closest('.search-ajax-pagination').find('.prev').attr('data-paged', parseInt(offset)-1);
                                that.closest('.search-ajax-pagination').find('.prev').css('display', 'inline-flex');
                            } else {
                                that.closest('.search-ajax-pagination').find('.prev').attr('data-paged', 0);
                                that.closest('.search-ajax-pagination').find('.prev').css('display', 'none');
                            }

                            if ( parseInt(offset) == parseInt(totalPage) ) {
                                that.closest('.search-ajax-pagination').find('.next').attr('data-paged', parseInt(offset));
                                that.closest('.search-ajax-pagination').find('.next').css('display', 'none');
                            } else {
                                that.closest('.search-ajax-pagination').find('.next').attr('data-paged', parseInt(offset)+1);
                                that.closest('.search-ajax-pagination').find('.next').css('display', 'inline-flex');
                            }
                        },
                    });
                }
            }); // END pagination
        }); // END Search Ajax
        
        // Event Filter
        $('.ovaev-filter input[name="ovaev_start_date"], .ovaev-filter input[name="ovaev_end_date"]').focus( function(e) {
            $(this).blur();
        });
        $('.ovaev-filter input[name="ovaev_start_date"], .ovaev-filter input[name="ovaev_end_date"]').each( function() {
            if ( $().datetimepicker ) {
                const format    = $(this).data('format');
                const language  = $(this).data('language');
                const firstDay  = $(this).data('first-day');

                $(this).datetimepicker({
                    format: format,
                    formatDate: format,
                    timepicker: false,
                    dayOfWeekStart: firstDay,
                });
                $.datetimepicker.setLocale(language);
            }
        });

        // Crrent start date
        let currentStartDate = '';
        $('.ovaev-filter input[name="ovaev_start_date"]').on( 'change', function() {
            if ( $(this).val() && $(this).val() != currentStartDate ) {
                $(this).closest('.ovaev-filter').find('input[name="ovaev_end_date"]').val('');
                currentStartDate = $(this).val();
            }
        });
        $('.ovaev-filter input[name="ovaev_end_date"]').on( 'click', function() {
            const startDate = $(this).closest('.ovaev-filter').find('input[name="ovaev_start_date"]').val();
            if ( startDate ) {
                const format    = $(this).data('format');
                const language  = $(this).data('language');
                const firstDay  = $(this).data('first-day');

                $(this).datetimepicker({
                    format: format,
                    formatDate: format,
                    timepicker: false,
                    dayOfWeekStart: firstDay,
                    minDate: startDate,
                    startDate: startDate,
                });
            }
        }); // END

        // Time Click
        $('.ovaev-filter .ovaev-filter-form .ovaev-filter-time .ovaev-btn-checkbox .checkmark').on( 'click', function(e) {
            if ( $(this).hasClass('active') ) {
                $(this).removeClass('active');
            } else {
                $('.ovaev-filter .ovaev-filter-form .ovaev-filter-time .ovaev-btn-checkbox .checkmark').removeClass('active');
                $(this).addClass('active');
            }

            // Get time
            const time = $('.ovaev-filter .ovaev-filter-form .ovaev-filter-time .ovaev-btn-checkbox .checkmark.active').data('time');
            if ( time ) {
                $(this).closest('.ovaev-filter-form').find('input[name="ovaev_start_date"], input[name="ovaev_end_date"]').val('').prop('readonly', true);
            } else {
                $(this).closest('.ovaev-filter-form').find('input[name="ovaev_start_date"], input[name="ovaev_end_date"]').val('').prop('readonly', false);
            }

            $(this).closest('.ovaev-filter-form').find('input[name="ovaev_time"]').val(time);
        });

        // Search
        $(document).on( 'click', '.ovaev-filter .ovaev-filter-form .ovaev-btn-search .ovaev-btn-submit', function(e) {
            e.preventDefault();
            ovaevFilterAjax($(this));
        });

        // Category Click
        $(document).on( 'click', '.ovaev-filter .ovaev-filter-categories .event-categories .ovaev-term', function(e) {
            e.preventDefault();

            const that = $(this);
            const btn  = that.closest('.ovaev-filter').find('.ovaev-btn-submit');
            
            if ( that.hasClass('active') ) {
                that.removeClass('active');
            } else {
                $('.ovaev-filter .ovaev-filter-categories .event-categories .ovaev-term').removeClass('active');
                that.addClass('active');
            }

            // Category filter ajax
            ovaevCategoryFilterAjax(btn);
        });

        // Fillter ajax
        function ovaevFilterAjax( that = null ) {
            if ( !that || !that.length ) return;
            
            // Filter
            const filter = that.closest('.ovaev-filter');

            // Get content
            const content = filter.find('.ovaev-filter-content');

            // Get category
            const category = filter.find('.event-categories');

            // Get settings
            const settings = filter.find('input[name="ovaev-data-filter"]').data('settings');

            // Get start date
            const startDate = filter.find('input[name="ovaev_start_date"]').val();

            // Get end date
            const endDate = filter.find('input[name="ovaev_end_date"]').val();

            // Get keyword
            const keyword = filter.find('input[name="ovaev_keyword"]').val();

            // Get time
            const time = filter.find('input[name="ovaev_time"]').val();

            // Get categories
            const categories = [];
            filter.find('.ovaev-term').each( function() {
                if ( $(this).hasClass('active') ) {
                    const termID = $(this).data('term-id');
                    if ( termID ) categories.push(termID);
                }
            });

            // Loader icon
            filter.find('.wrap_loader').fadeIn(100);

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: ({
                    action: 'ovaev_filter_ajax',
                    settings: settings,
                    start_date: startDate,
                    end_date: endDate,
                    keyword: keyword,
                    time: time,
                    categories: categories,
                }),
                success: function(response){
                    const data = JSON.parse(response);
                    filter.find('.wrap_loader').fadeOut(200);
                    content.html('').append(data['result']).fadeIn(300);
                    category.html('').append(data['category']).fadeIn(300);
                },
            });
        } // END

        // Category filter ajax
        function ovaevCategoryFilterAjax( that = null ) {
            if ( !that || !that.length ) return;

            // Get filter
            const filter = that.closest('.ovaev-filter');

            // Get content
            const content = filter.find('.ovaev-filter-content');

            // Get settings
            const settings = filter.find('input[name="ovaev-data-filter"]').data('settings');

            // Get start date
            const startDate = filter.find('input[name="ovaev_start_date"]').val();

            // Get end date
            const endDate = filter.find('input[name="ovaev_end_date"]').val();

            // Get keyword
            const keyword = filter.find('input[name="ovaev_keyword"]').val();

            // Get time
            const time = filter.find('input[name="ovaev_time"]').val();

            // Get categories
            const categories = [];
            filter.find('.ovaev-term').each( function() {
                if ( $(this).hasClass('active') ) {
                    const termID = $(this).data('term-id');
                    if ( termID ) categories.push(termID);
                }
            });

            // Loader
            filter.find('.wrap_loader').fadeIn(100);

            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: ({
                    action: 'ovaev_category_filter_ajax',
                    settings: settings,
                    start_date: startDate,
                    end_date: endDate,
                    keyword: keyword,
                    time: time,
                    categories: categories,
                }),
                success: function(response) {
                    const data = JSON.parse(response);
                    filter.find('.wrap_loader').fadeOut(200);
                    content.html('').append(data['result']).fadeIn(300);
                }
            });
        } // END
    });
    
})(jQuery);