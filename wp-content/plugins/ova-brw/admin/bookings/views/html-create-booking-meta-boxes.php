<?php if ( !defined( 'ABSPATH' ) ) exit; ?>

<div class="ovabrw-meta">
	<h3 class="title">
		<?php esc_html_e('Add Meta', 'ova-brw'); ?>
	</h3>
	<div class="rental_item ovabrw-adult-price">
		<label for="ovabrw-adult-price">
			<?php esc_html_e( 'Adult Price', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_wp_text_input([
			'type' 		=> 'text',
			'class' 	=> 'ovabrw_adult_price',
			'name' 		=> 'ovabrw_adult_price[]',
			'readonly' 	=> true
		]); ?>
		<span class="ovabrw-current-currency">
			<?php echo get_woocommerce_currency_symbol(); ?>
		</span>
		<div class="loading-total">
			<div class="dashicons-before dashicons-update-alt"></div>
		</div>
	</div>
	<div class="rental_item ovabrw-children-price">
		<label for="ovabrw-children-price">
			<?php esc_html_e( 'Children Price', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_wp_text_input([
			'type' 		=> 'text',
			'class' 	=> 'ovabrw_children_price',
			'name' 		=> 'ovabrw_children_price[]',
			'readonly' 	=> true
		]); ?>
		<span class="ovabrw-current-currency">
			<?php echo get_woocommerce_currency_symbol(); ?>
		</span>
		<div class="loading-total">
			<div class="dashicons-before dashicons-update-alt"></div>
		</div>
	</div>
	<div class="rental_item ovabrw-baby-price">
		<label for="ovabrw-baby-price">
			<?php esc_html_e( 'Baby Price', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_wp_text_input([
			'type' 		=> 'text',
			'class' 	=> 'ovabrw_baby_price',
			'name' 		=> 'ovabrw_baby_price[]',
			'readonly' 	=> true
		]); ?>
		<span class="ovabrw-current-currency">
			<?php echo get_woocommerce_currency_symbol(); ?>
		</span>
		<div class="loading-total">
			<div class="dashicons-before dashicons-update-alt"></div>
		</div>
	</div>
	<div class="rental_item ovabrw-pickup">
		<label for="ovabrw-pickup-date" class="ovabrw-required">
			<?php esc_html_e( 'Check-in', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_text_input([
            'type'      => 'text',
            'id'        => ovabrw_unique_id( 'checkin-date' ),
            'class'     => 'ovabrw-input-required checkin-date',
            'name'      => 'ovabrw_pickup_date[]',
            'data_type' => 'datepicker'
        ]); ?>
	</div>
	<div class="rental_item ovabrw-dropoff-date">
		<label class="ovabrw-required">
			<?php esc_html_e( 'Check-out', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_text_input([
	        'type'          => 'text',
	        'class'         => 'ovabrw-input-required checkout-date',
	        'name'          => 'ovabrw_pickoff_date[]',
	        'placeholder'   => ovabrw_get_placeholder_date(),
	        'readonly'      => true
	    ]); ?>
	</div>
	<div class="rental_item ovabrw-number-adults">
	    <label>
	    	<?php esc_html_e( 'Number of adults', 'ova-brw' ); ?>
	    </label>
	    <?php ovabrw_text_input([
	        'type'          => 'number',
	        'class'         => 'guests-input ovabrw_adults ovabrw-input-required',
	        'name'          => 'ovabrw_adults[]',
	        'value' 		=> 1,
	        'placeholder'   => 1,
	        'attrs' 		=> [
	        	'min' 			=> 1,
	        	'data-label' 	=> esc_html__( 'Adults', 'ova-brw' ),
	        	'data-name' 	=> 'ovabrw_adults'
	        ]
	    ]); ?>
	</div>
	<div class="rental_item ovabrw-number-childrens">
	    <label>
	    	<?php esc_html_e( 'Number of children', 'ova-brw' ); ?>
	    </label>
	    <?php ovabrw_text_input([
	        'type'          => 'number',
	        'class'         => 'guests-input ovabrw_childrens',
	        'name'          => 'ovabrw_childrens[]',
	        'value' 		=> 0,
	        'placeholder'   => 0,
	        'attrs' 		=> [
	        	'min' 			=> 0,
	        	'data-label' 	=> esc_html__( 'Children', 'ova-brw' ),
	        	'data-name' 	=> 'ovabrw_childrens'
	        ]
	    ]); ?>
	</div>
	<div class="rental_item ovabrw-number-babies">
	    <label>
	    	<?php esc_html_e( 'Number of babies', 'ova-brw' ); ?>
	    </label>
	    <?php ovabrw_text_input([
	        'type'          => 'number',
	        'class'         => 'guests-input ovabrw_babies',
	        'name'          => 'ovabrw_babies[]',
	        'value' 		=> 0,
	        'placeholder'   => 0,
	        'attrs' 		=> [
	        	'min' 			=> 0,
	        	'data-label' 	=> esc_html__( 'Babies', 'ova-brw' ),
	        	'data-name' 	=> 'ovabrw_babies'
	        ]
	    ]); ?>
	</div>
	<div class="rental_item ovabrw-item-guest">
	    <label>
	    	<?php esc_html_e( 'Guest info', 'ova-brw' ); ?>
	    </label>
	    <div class="ovabrw-guest-info">
	    	<div class="guest-info-accordion"></div>
	    </div>
	</div>
	<div class="rental_item ovabrw-custom_ckf"></div>
	<div class="rental_item ovabrw-resources">
		<label for="ovabrw-resources">
			<?php esc_html_e( 'Resources', 'ova-brw' ); ?>
		</label>
		<span class="ovabrw-resources-span"></span>
	</div>

	<div class="rental_item ovabrw-services">
		<label for="ovabrw-services">
			<?php esc_html_e( 'Services', 'ova-brw' ); ?>
		</label>
		<span class="ovabrw-services-span"></span>
	</div>
	<div class="rental_item ovabrw-amount-insurance">
		<label for="ovabrw-amount-insurance">
			<?php esc_html_e( 'Amount of insurance', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_wp_text_input([
			'type' 			=> 'text',
			'class' 		=> 'ovabrw_amount_insurance',
			'name' 			=> 'ovabrw_amount_insurance[]',
			'placeholder' 	=> 0,
			'readonly' 		=> true
		]); ?>
		<span class="ovabrw-current-currency">
			<?php echo get_woocommerce_currency_symbol(); ?>
		</span>
	</div>
	<div class="rental_item ovabrw-amount-deposite">
		<label for="ovabrw-amount-deposite">
			<?php esc_html_e( 'Deposit Amount', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_wp_text_input([
			'type' 			=> 'text',
			'class' 		=> 'ovabrw_amount_deposite',
			'name' 			=> 'ovabrw_amount_deposite[]',
			'placeholder' 	=> 0
		]); ?>
		<span class="ovabrw-current-currency">
			<?php echo get_woocommerce_currency_symbol(); ?>
		</span>
	</div>
	<div class="rental_item ovabrw-amount-remaining">
		<label for="ovabrw-amount-remaining">
			<?php esc_html_e( 'Remaining Amount', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_wp_text_input([
			'type' 			=> 'text',
			'class' 		=> 'ovabrw_amount_remaining',
			'name' 			=> 'ovabrw_amount_remaining[]',
			'placeholder' 	=> 0,
			'readonly' 		=> true
		]); ?>
		<span class="ovabrw-current-currency">
			<?php echo get_woocommerce_currency_symbol(); ?>
		</span>
		<div class="loading-total">
			<div class="dashicons-before dashicons-update-alt"></div>
		</div>
	</div>
	<div class="rental_item ovabrw-total">
		<label for="ovabrw-total-product">
			<?php esc_html_e( 'Total', 'ova-brw' ); ?>
		</label>
		<?php ovabrw_wp_text_input([
			'type' 			=> 'text',
			'class' 		=> 'ovabrw-total-product ovabrw-input-required',
			'name' 			=> 'ovabrw-total-product[]',
			'placeholder' 	=> 0,
			'readonly' 		=> true
		]); ?>
		<span class="ovabrw-current-currency">
			<?php echo get_woocommerce_currency_symbol(); ?>
		</span>
		<div class="loading-total">
			<div class="dashicons-before dashicons-update-alt"></div>
		</div>
	</div>
	<div class="rental_item ovabrw-error">
		<span class="ovabrw-error-span"></span>
	</div>
</div>