<?php
/**
 * Expiration reminders controller class.
 *
 * @package WooCommerce Gift Cards
 * @since 2.2.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_GC_Expiration_Reminders class.
 *
 * @version 2.2.2
 */
class WC_GC_Expiration_Reminders {
	const EXPIRATION_REMINDERS_GROUP = 'woocommerce_gc_expiration_reminders';
	const EXPIRATION_MINIMUM_BUFFER  = 2 * DAY_IN_SECONDS;

	/**
	 * Setup hooks.
	 */
	public static function init() {
		// Hook up actions for gift card modifications.

		add_action(
			'woocommerce_gc_create_gift_card',
			array( __CLASS__, 'schedule_expiration_reminder' )
		);

		add_action(
			'woocommerce_gc_update_gift_card',
			array( __CLASS__, 'reschedule_expiration_reminder' )
		);

		add_action(
			'woocommerce_gc_delete_gift_card',
			array( __CLASS__, 'unschedule_expiration_reminder' )
		);

		add_action(
			'woocommerce_gc_gift_card_debited',
			array( __CLASS__, 'handle_credit_and_debit_hook' ),
			10,
			2
		);

		add_action(
			'woocommerce_gc_gift_card_credited',
			array( __CLASS__, 'handle_credit_and_debit_hook' ),
			10,
			2
		);

		add_filter( 'woocommerce_email_classes', array( __CLASS__, 'email_classes' ) );

		add_filter( 'woocommerce_gc_settings', array( __CLASS__, 'add_expiration_reminders_settings' ) );

		add_filter( 'woocommerce_admin_settings_sanitize_option', array( __CLASS__, 'manage_settings' ), 10, 2 );

		// Hook up AS actions.

		add_action(
			'woocommerce_gc_send_expiration_reminder',
			array( __CLASS__, 'send_expiration_reminder' ),
			10
		);

		// Handle settings changes.
		add_action(
			'update_option_wc_gc_expiration_reminders_enabled',
			array( __CLASS__, 'maybe_schedule_all_expiration_reminders_job' ),
			10,
			2
		);

		add_action(
			'add_option_wc_gc_expiration_reminders_enabled',
			array( __CLASS__, 'maybe_schedule_all_expiration_reminders_job' ),
			10,
			2
		);

		add_action(
			'update_option_wc_gc_expiration_reminders_days_before',
			array( __CLASS__, 'maybe_schedule_all_expiration_reminders_job' ),
			10,
			2
		);

		add_action(
			'add_option_wc_gc_expiration_reminders_days_before',
			array( __CLASS__, 'maybe_schedule_all_expiration_reminders_job' ),
			10,
			2
		);
	}

	/**
	 * Validate settings upon save.
	 *
	 * @param  string $value Settings value.
	 * @param  string $option Settings option.
	 * @return string
	 */
	public static function manage_settings( $value, $option ) {
		if ( 'wc_gc_expiration_reminders_days_before' === $option['id'] ) {
			$value = max( $value, 1 );
		}
		return $value;
	}

	/**
	 * Add expiration reminders settings.
	 *
	 * @param array $settings Settings.
	 */
	public static function add_expiration_reminders_settings( $settings ) {
		$settings[] = array(
			'title' => __( 'Reminders', 'woocommerce-gift-cards' ),
			'type'  => 'title',
			'id'    => 'gc_settings_reminders',
		);
		$settings[] = array(
			'title'    => __( 'Expiration reminders', 'woocommerce-gift-cards' ),
			'desc'     => __( 'Enable expiration reminders', 'woocommerce-gift-cards' ),
			'desc_tip' => __( 'Enable this option to send reminders to gift card recipients before their gift card expires.', 'woocommerce-gift-cards' ),
			'id'       => 'wc_gc_expiration_reminders_enabled',
			'default'  => 'no',
			'type'     => 'checkbox',
		);
		$settings[] = array(
			'title'   => __( 'Days before expiration', 'woocommerce-gift-cards' ),
			'desc'    => __( 'Specify how many days before expiration to send gift card reminders.', 'woocommerce-gift-cards' ),
			'id'      => 'wc_gc_expiration_reminders_days_before',
			'default' => '30',
			'type'    => 'number',
		);
		$settings[] = array(
			'type' => 'sectionend',
			'id'   => 'gc_settings_reminders',
		);
		return $settings;
	}

	/**
	 * Add expiration reminder email class.
	 *
	 * @param array $emails Email classes.
	 */
	public static function email_classes( $emails ) {

		$emails['WC_GC_Email_Expiration_Reminder'] = include WC_GC_ABSPATH . 'includes/modules/expiration-reminders/includes/emails/class-wc-gc-email-expiration-reminder.php';
		if ( is_a( $emails['WC_GC_Email_Expiration_Reminder'], 'WC_Email' ) ) {
			$emails['WC_GC_Email_Expiration_Reminder']->setup_hooks();
		}

		return $emails;
	}

	/**
	 * Maybe schedule all expiration reminders.
	 */
	public static function maybe_schedule_all_expiration_reminders_job() {

		if ( ! ( class_exists( 'ActionScheduler' ) && ActionScheduler::is_initialized() ) ) {
			return;
		}

		update_option( 'wc_gc_expiration_reminders_schedule_all_timestamp', time() );

		WC()->queue()->cancel_all( 'woocommerce_gc_send_expiration_reminder' );
		if ( ! self::is_enabled() ) {
			WC_GC_Expiration_Reminders_Batch_Processor::dequeue();
			return;
		}
		$message = WC_GC_Expiration_Reminders_Batch_Processor::enqueue();
		if ( $message ) {
			WC_GC_Admin_Notices::add_notice( $message, 'info' );
		}
	}

	/**
	 * Schedule expiration reminder.
	 *
	 * @param WC_GC_Gift_Card_Data $gift_card_data Gift card data.
	 */
	public static function schedule_expiration_reminder( WC_GC_Gift_Card_Data $gift_card_data ) {
		if (
			! self::is_enabled() ||
			! self::should_expiration_reminder_be_sent( $gift_card_data )
		) {
			return;
		}

		$expiration_reminder_date = self::calculate_expiration_reminder_date( $gift_card_data );

		WC()->queue()->schedule_single(
			$expiration_reminder_date,
			'woocommerce_gc_send_expiration_reminder',
			self::get_action_scheduler_action_args( $gift_card_data ),
			self::EXPIRATION_REMINDERS_GROUP
		);
	}

	/**
	 * Extracts the gift card data from the action arguments and reschedules the expiration reminder.
	 *
	 * @param float           $amount Amount.
	 * @param WC_GC_Gift_Card $gift_card Gift card.
	 */
	public static function handle_credit_and_debit_hook( $amount, WC_GC_Gift_Card $gift_card ) {
		self::reschedule_expiration_reminder( $gift_card->data );
	}

	/**
	 * Reschedule expiration reminder.
	 *
	 * @param WC_GC_Gift_Card_Data $gift_card_data Gift card data.
	 */
	public static function reschedule_expiration_reminder( WC_GC_Gift_Card_Data $gift_card_data ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		$scheduled_date = WC()->queue()->get_next(
			'woocommerce_gc_send_expiration_reminder',
			self::get_action_scheduler_action_args( $gift_card_data ),
			self::EXPIRATION_REMINDERS_GROUP
		);

		// Something has changed that makes the reminder no longer necessary.
		if ( $scheduled_date && ! self::should_expiration_reminder_be_sent( $gift_card_data ) ) {
			self::unschedule_expiration_reminder( $gift_card_data );
			return;
		}

		// Reminder is already scheduled for the correct date.
		if ( $scheduled_date && self::calculate_expiration_reminder_date( $gift_card_data ) === $scheduled_date->getTimestamp() ) {
			return;
		}

		if ( $scheduled_date ) {
			// Unschedule the existing reminder, which will be rescheduled to a different time.
			self::unschedule_expiration_reminder( $gift_card_data );
		}

		self::schedule_expiration_reminder( $gift_card_data );
	}

	/**
	 * Get gift card args.
	 *
	 * @param WC_GC_Gift_Card_Data $gift_card_data Gift card data.
	 */
	public static function get_action_scheduler_action_args( WC_GC_Gift_Card_Data $gift_card_data ) {
		return array(
			'gift_card_id' => $gift_card_data->get_id(),
		);
	}

	/**
	 * Unschedule expiration reminder.
	 *
	 * @param WC_GC_Gift_Card_Data $gift_card_data Gift card data.
	 */
	public static function unschedule_expiration_reminder( WC_GC_Gift_Card_Data $gift_card_data ) {
		WC()->queue()->cancel(
			'woocommerce_gc_send_expiration_reminder',
			self::get_action_scheduler_action_args( $gift_card_data ),
			self::EXPIRATION_REMINDERS_GROUP
		);
	}

	/**
	 * Send expiration reminder.
	 *
	 * @param int $gift_card_id Gift card ID.
	 */
	public static function send_expiration_reminder( int $gift_card_id ) {
		if ( ! self::is_enabled() ) {
			return;
		}

		$gift_card = new WC_GC_Gift_Card( $gift_card_id );

		if ( ! self::should_expiration_reminder_be_sent( $gift_card->data ) ) {
			return;
		}

		WC_Emails::instance(); // making sure that the e-mail classes are initialized, since they are not loaded when running in background.

		/**
		 * Send expiration reminder email.
		 *
		 * @param WC_GC_Gift_Card $gift_card Gift card.
		 *
		 * @since 2.2.0
		 */
		do_action(
			'woocommerce_gc_send_expiration_reminder_email',
			$gift_card
		);
	}

	/**
	 * Should an expiration reminder be sent?
	 *
	 * @param WC_GC_Gift_Card_Data $gift_card Gift card data object.
	 *
	 * @return bool
	 */
	public static function should_expiration_reminder_be_sent( WC_GC_Gift_Card_Data $gift_card ): bool {
		// WC_GC_Gift_Card_Data::is_delivered() returns an int when the gift card is delivered. This can be zero.
		$is_delivered = is_int( $gift_card->is_delivered() );

		$expiration_reminder_date = self::calculate_expiration_reminder_date( $gift_card );

		$delivered_date = 0 < $gift_card->get_deliver_date() ? $gift_card->get_deliver_date() : $gift_card->get_date_created();

		$should_expiration_reminder_be_sent = self::is_enabled()
			&& $gift_card->is_active()
			&& $gift_card->get_expire_date() > 0
			&& ! $gift_card->has_expired()
			&& $is_delivered
			&& $gift_card->get_balance() > 0
			&& $expiration_reminder_date > time()
			&& ( $expiration_reminder_date - $delivered_date ) >= self::EXPIRATION_MINIMUM_BUFFER;

		/**
		 * Filter whether an expiration reminder should be sent.
		 *
		 * @param bool $should_expiration_reminder_be_sent Should an expiration reminder be sent?
		 * @param WC_GC_Gift_Card_Data $gift_card Gift card object.
		 *
		 * @since 2.2.0
		 */
		return apply_filters(
			'woocommerce_gc_should_expiration_reminder_be_sent',
			$should_expiration_reminder_be_sent,
			$gift_card
		);
	}

	/**
	 * Calculate expiration reminder date.
	 *
	 * @param WC_GC_Gift_Card_Data $gift_card Gift card object.
	 *
	 * @return int Unix timestamp.
	 */
	public static function calculate_expiration_reminder_date( WC_GC_Gift_Card_Data $gift_card ): int {
		$expire_date = $gift_card->get_expire_date();

		if ( 0 === $expire_date ) {
			return 0;
		}

		$number_of_days_before = get_option( 'wc_gc_expiration_reminders_days_before', 30 );

		return $expire_date - $number_of_days_before * DAY_IN_SECONDS;
	}

	/**
	 * Check if expiration reminders are enabled.
	 *
	 * @return bool
	 */
	public static function is_enabled(): bool {
		return 'yes' === get_option( 'wc_gc_expiration_reminders_enabled', 'no' );
	}
}

WC_GC_Expiration_Reminders::init();
