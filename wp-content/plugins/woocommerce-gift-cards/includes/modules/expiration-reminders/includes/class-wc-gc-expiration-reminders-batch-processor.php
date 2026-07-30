<?php
/**
 * Batch processor for Expiration Reminders.
 *
 * @package WooCommerce Gift Cards
 * @since 2.2.0
 */

use Automattic\WooCommerce\Internal\BatchProcessing\BatchProcessorInterface;
use Automattic\WooCommerce\Internal\BatchProcessing\BatchProcessingController;

/**
 * WC_GC_Expiration_Reminders_Batch_Processor class.
 *
 * @version 2.2.0
 */
class WC_GC_Expiration_Reminders_Batch_Processor implements BatchProcessorInterface {


	/**
	 * Get a user-friendly name for this processor.
	 */
	public function get_name(): string {
		return 'wc_gc_expiration_reminders_batch_processor';
	}

	/**
	 * Get a user-friendly description for this processor.
	 */
	public function get_description(): string {
		return 'WooCommerce Gift Cards Expiration Reminders Batch Processor';
	}

	/**
	 * Get the total number of pending items that require processing.
	 */
	public function get_total_pending_count(): int {
		$gift_cards_count = wc_gc_get_gift_cards(
			array(
				'count'                 => true,
				'is_delivered'          => true,
				'is_active'             => 'on',
				'expired_start'         => time(),
				'has_remaining_balance' => true,
				'last_modify_end'       => get_option( 'wc_gc_expiration_reminders_schedule_all_timestamp', time() ),
			)
		);
		return is_int( $gift_cards_count ) ? $gift_cards_count : 0;
	}

	/**
	 * Returns the next batch of items that need to be processed.
	 *
	 * @param int $size Maximum size of the batch to be returned.
	 */
	public function get_next_batch_to_process( int $size ): array {
		$gift_cards = wc_gc_get_gift_cards(
			array(
				'is_delivered'          => true,
				'is_active'             => 'on',
				'expired_start'         => time(),
				'has_remaining_balance' => true,
				'last_modify_end'       => get_option( 'wc_gc_expiration_reminders_schedule_all_timestamp', time() ),
				'limit'                 => $size,
			)
		);
		return false === $gift_cards ? array() : $gift_cards;
	}

	/**
	 * Process data for the supplied batch.
	 *
	 * @param array $gift_cards Batch of items to process.
	 */
	public function process_batch( array $gift_cards ): void {
		foreach ( $gift_cards as $gift_card ) {
			WC_GC_Expiration_Reminders::schedule_expiration_reminder( $gift_card );
			$gift_card->save(); // save gift card to update last_modify timestamp.
		}
	}

	/**
	 * Get the default batch size for this processor.
	 */
	public function get_default_batch_size(): int {
		/**
		 * Filter the default batch size for expiration reminders.
		 *
		 * @since 2.2.0
		 */
		return apply_filters( 'wc_gc_expiration_reminders_batch_size', 100 );
	}

	/**
	 * Start the background process for scheduling expiration reminders.
	 *
	 * @return string Informative string to show after the tool is triggered in UI.
	 */
	public static function enqueue(): string {
		$batch_processor = wc_get_container()->get( BatchProcessingController::class );
		if ( $batch_processor->is_enqueued( self::class ) ) {
			return __( 'Gift card expiration reminders are already scheduled in the background.', 'woocommerce-gift-cards' );
		}

		$batch_processor->enqueue_processor( self::class );
		return __( 'Gift card expiration reminders will be scheduled in the background. This might take a few minutes.', 'woocommerce-gift-cards' );
	}

	/**
	 * Stop the background process for scheduling expiration reminders.
	 */
	public static function dequeue(): void {
		$batch_processor = wc_get_container()->get( BatchProcessingController::class );
		if ( ! $batch_processor->is_enqueued( self::class ) ) {
			return;
		}
		$batch_processor->remove_processor( self::class );
	}
}
