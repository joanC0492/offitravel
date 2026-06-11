<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get List Event
$list_event = $args['events'];
if ( !empty( $list_event ) && is_array( $list_event ) ): ?>
    <div class="event-feature slide-event-feature">
        <div class="swiper swiper-loading">
            <div class="swiper-wrapper">
                <?php foreach ( $list_event as $event ):
                    $id = $event->ID;
                ?>
                    <div class="ovaev-content swiper-slide">
                        <div class="item">
                            <?php do_action( 'ovaev_loop_highlight_date_2', $id ); ?>
                            <div class="desc">
                                <?php do_action( 'ovaev_loop_thumbnail_grid', $id ); ?>
                                <div class="event_post">
                                    <?php  do_action( 'ovaev_loop_type', $id ); ?>
                                    <?php do_action( 'ovaev_loop_title', $id ); ?>
                                    <div class="time-event">
                                        <?php do_action( 'ovaev_loop_date_event', $id ); ?>
                                        <?php do_action( 'ovaev_loop_venue', $id ); ?>
                                    </div>
                                    <?php do_action( 'ovaev_loop_readmore_2', $id ); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif;