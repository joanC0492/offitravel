<?php if ( !defined( 'ABSPATH' ) ) exit(); 

// Get event id
$id = isset( $args['id'] ) ? $args['id'] : '';
if ( !$id ) return;

// Get class
$class = isset( $args['class'] ) ? $args['class'] : '';

// Get event organizer
$organizer = get_post_meta( $id, 'ovaev_organizer', true );

// Get event phone
$phone = get_post_meta( $id, 'ovaev_phone', true );

// Get event email
$email = get_post_meta( $id, 'ovaev_email', true );

// Get event website
$website = get_post_meta( $id, 'ovaev_website', true );

// Get event gallery
$gallery = get_post_meta( $id, 'ovaev_gallery_id', true );

if ( $organizer || $phone || $email || $website || ( !empty( $gallery ) && is_array( $gallery ) ) ): ?>
	<div class="single_event single-event ovaev-shortcode-tabs<?php echo ' '.esc_html( $class ); ?>">
		<div class="content-event">
			<div class="tab-Location">
				<ul class="event_nav event_nav-tabs" role="tablist">
				  	<?php if ( $organizer || $phone || $email || $website ): ?>
					  	<li class="event_nav-item">
					    	<a class="event_nav-link second_font" data-href="#contact" role="tab">
					    		<?php esc_html_e( 'Contact Details', 'ovaev' ); ?>
					    	</a>
					 	</li>
				 	<?php endif; ?>
				 	<?php if ( !empty( $gallery ) && is_array( $gallery ) ): ?>
					  	<li class="event_nav-item">
					    	<a class="event_nav-link second_font" data-href="#gallery" role="tab">
					    		<?php esc_html_e( 'Gallery', 'ovaev' ); ?>
					    	</a>
					  	</li>
				  	<?php endif; ?>
				</ul>
				<div class="tab-content">
					<?php if ( $organizer || $phone || $email || $website ): ?>
				  		<div role="tabpanel" class="event_tab-pane " id="contact">
				  			<div class="event_row">
								<div class="col_contact">
									<div class="contact">
										<ul class="info-contact">
											<?php if ( $organizer ): ?>
												<li>
													<span>
														<?php esc_html_e( 'Organizer Name:', 'ovaev' ); ?>
													</span>
													<span class="info">
														<?php echo esc_html( $organizer ); ?>
													</span>
												</li>
											<?php endif; ?>
											<?php if ( $phone ): ?>
												<li>
													<span>
														<?php esc_html_e( 'Phone:', 'ovaev' ); ?>
													</span>
													<a href="tel:<?php echo esc_attr( $phone ); ?>" class="info">
														<?php echo esc_html( $phone ); ?>
													</a>
												</li>
											<?php endif; ?>
										</ul>
									</div>
								</div>
								<div class="col_contact">
									<div class="contact">
										<ul class="info-contact">
											<?php if ( $email ): ?>
												<li>
													<span>
														<?php esc_html_e( 'Email:', 'ovaev' ); ?>
													</span>
													<a href="mailto:<?php echo esc_attr( $email ); ?>" class="info">
														<?php echo esc_html( $email ); ?>
													</a>
												</li>
											<?php endif; ?>
											<?php if ( $website ): ?>
												<li>
													<span>
														<?php esc_html_e( 'Website:', 'ovaev' ); ?>
													</span>
													<a href="<?php echo esc_url( $website ); ?>" class="info">
														<?php echo esc_html( $website ); ?>
													</a>
												</li>
											<?php endif; ?>
										</ul>
									</div>
								</div>
				  			</div>
				  		</div>
			  		<?php endif; ?>
			  		<?php if ( !empty( $gallery ) && is_array( $gallery ) ): ?>
			 		 	<div role="tabpanel" class="event_tab-pane " id="gallery">
			 		 		<div class="event_row">
			 		 			<?php foreach ( $gallery as $items ):
			 		 				$img_url = wp_get_attachment_image_url( $items, 'large' );
			 		 			?>
			 		 				<div class="event_col-6">
										<div class="gallery-items">
											<a 
												href="<?php echo esc_url( $img_url ); ?>" 
												data-gal="prettyPhoto[gal]">
												<img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo get_the_title( $id ); ?>" />
											</a> 
										</div>
									</div>
			 		 			<?php endforeach; ?>
			 		 		</div>
			 		 	</div>
	 		 		<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
<?php endif;