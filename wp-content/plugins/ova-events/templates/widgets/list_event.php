<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get event time format
$time_format = OVAEV_Settings::archive_event_format_time();

// Get list event
$list_event = isset( $args['list_events'] ) ? $args['list_events'] : '';
if ( !empty( $list_event ) && is_array( $list_event ) ): ?>
    <div class="list-event">
        <?php foreach ( $list_event as $event ):
            // Get event id
            $id = $event->ID;

            // Get title
            $title = get_the_title( $id );

            // Get thumbnail
            $url_img = get_the_post_thumbnail_url( $id, 'post-thumbnail' );

            // Get link
            $link = get_the_permalink( $id );

            // Get event start date
            $ovaev_start_date = get_post_meta( $id, 'ovaev_start_date_time', true );

            // Get event end date
            $ovaev_end_date = get_post_meta( $id, 'ovaev_end_date_time', true );

            // Convert start date
            $start_date = $ovaev_start_date != '' ? date_i18n( get_option( 'date_format' ), $ovaev_start_date ) : '';

            // Convert end date
            $end_date = $ovaev_end_date != '' ? date_i18n( get_option( 'date_format' ), $ovaev_end_date ) : '';

            // Convert start time
            $start_time = $ovaev_start_date != '' ? date_i18n( $time_format, $ovaev_start_date) : '';

            // Convert end time
            $end_time = $ovaev_end_date != '' ? date_i18n( $time_format, $ovaev_end_date) : '';
        ?>
            <div class="item-event">
                <div class="ova-thumb-nail">
                    <a href="<?php echo esc_url( $link ); ?>" style="background-image:url(<?php echo esc_url( $url_img ); ?>);"></a>
                </div>
               <div class="ova-content">
                    <h3 class="title">
                        <a class="second_font" href="<?php echo esc_url( $link ); ?>">
                            <?php echo esc_html( $title ); ?>
                        </a>
                    </h3>
                    <?php if ( $start_date == $end_date && $start_date != '' ): ?>
                        <span class="time">
                            <span class="date">
                                <?php echo esc_html( $start_date ).' - '.$end_time; ?>
                            </span>
                        </span>
                    <?php else: ?>
                        <span class="time">
                            <span class="date">
                                <?php echo esc_html( $start_date ) .' '. esc_html__( '@', 'ovaev' ); ?>
                            </span>
                            <span> <?php echo esc_html( $start_time ); ?></span>

                            <?php if ( apply_filters( 'ovaev_show_more_date_text', true ) ): ?>
                                <a href="<?php echo esc_url( get_the_permalink( $id ) ); ?>" class="more_date_text" data-id="<?php echo get_the_id(); ?>">
                                    <span><?php esc_html_e( ', more', 'ovaev' ); ?></span>  
                                </a>
                            <?php endif; ?>
                        </span>
                    <?php endif; ?>
               </div>
            </div>
        <?php endforeach; ?>
    </div>
    <div class="button-all-event">
        <a class="second_font" href="<?php echo esc_url( apply_filters( 'ovaev_upcomming_event_url', get_post_type_archive_link( 'event' ) ) ); ?>">
            <?php esc_html_e( 'View All Events', 'ovaev' ); ?>
            <i data-feather="chevron-right"></i>
        </a>
    </div>
<?php endif;