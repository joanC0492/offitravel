<?php if ( !defined( 'ABSPATH' ) ) exit();

/**
 * Class OVAEV_Admin_Settings
 */
if ( !class_exists( 'OVAEV_Admin_Settings' ) ) {

	class OVAEV_Admin_Settings {

		/**
		 * Construct Admin
		 */
		public function __construct() {
			add_action( 'admin_enqueue_scripts', [ $this, 'load_media' ] );
			add_action( 'admin_init', [ $this, 'register_options' ] );
		}

		/**
		 * Load media
		 */
		public function load_media() {
			wp_enqueue_media();
		}

		/**
		 * Print options section
		 */
		public function print_options_section() {
			return true;
		}

		/**
		 * Register options
		 */
		public function register_options() {
			register_setting(
				'ovaev_options_group', // Option group
				'ovaev_options', // Name Option
				[ $this, 'settings_callback' ] // Call Back
			);

			// Add Section: General Settings
			add_settings_section(
				'ovaev_general_section_id', // ID
				esc_html__( 'General Setting', 'ovaev' ), // Title
				[ $this, 'print_options_section' ],
				'ovaev_general_settings' // Page
			);

			add_settings_field(
				'archive_event_format_date', // ID
				esc_html__( 'Date Format', 'ovaev' ),
				[ $this, 'archive_event_format_date' ],
				'ovaev_general_settings', // Page
				'ovaev_general_section_id' // Section ID
			);

			add_settings_field(
				'archive_event_format_time', // ID
				esc_html__( 'Time Format', 'ovaev' ),
				[ $this, 'archive_event_format_time' ],
				'ovaev_general_settings', // Page
				'ovaev_general_section_id' // Section ID
			);

			add_settings_field(
				'archive_format_date_lang', // ID
				esc_html__( 'Calendar Language', 'ovaev' ),
				[ $this, 'archive_format_date_lang' ],
				'ovaev_general_settings', // Page
				'ovaev_general_section_id' // Section ID
			);

			
			// Add Section: Archive Event Settings
			add_settings_section(
				'ovaev_archive_event_section_id', // ID
				esc_html__( 'Archive Event Setting', 'ovaev' ), // Title
				[ $this, 'print_options_section' ],
				'ovaev_archive_event_settings' // Page
			);

			add_settings_field(
				'archive_event_type', // ID
				esc_html__( 'Templates', 'ovaev' ),
				[ $this, 'archive_event_type' ],
				'ovaev_archive_event_settings', // Page
				'ovaev_archive_event_section_id' // Section ID
			);

			add_settings_field(
				'archive_event_col', // ID
				esc_html__( 'Columns', 'ovaev' ),
				[ $this, 'archive_event_col' ],
				'ovaev_archive_event_settings', // Page
				'ovaev_archive_event_section_id' // Section ID
			);

			add_settings_field(
				'ovaev_show_past', // ID
				esc_html__( 'Show event in past', 'ovaev' ),
				[ $this, 'ovaev_show_past' ],
				'ovaev_archive_event_settings', // Page
				'ovaev_archive_event_section_id' // Section ID
			);

			add_settings_field(
				'archive_event_posts_per_page',
				esc_html__( 'Posts per page', 'ovaev' ),
				[ $this, 'archive_event_posts_per_page' ],
				'ovaev_archive_event_settings',
				'ovaev_archive_event_section_id'
			);

			add_settings_field(
				'archive_event_orderby', // ID
				esc_html__( 'Order By', 'ovaev' ),
				[ $this, 'archive_event_orderby' ],
				'ovaev_archive_event_settings', // Page
				'ovaev_archive_event_section_id' // Section ID
			);

			add_settings_field(
				'archive_event_order', // ID
				esc_html__( 'Order', 'ovaev' ),
				[ $this, 'archive_event_order' ],
				'ovaev_archive_event_settings', // Page
				'ovaev_archive_event_section_id' // Section ID
			);

			add_settings_field(
				'archive_event_thumbnail', // ID
				esc_html__( 'Thumbnail', 'ovaev' ),
				[ $this, 'archive_event_thumbnail' ],
				'ovaev_archive_event_settings', // Page
				'ovaev_archive_event_section_id' // Section ID
			);

			add_settings_field(
				'archive_event_header', // ID
				esc_html__( 'Header', 'ovaev' ),
				[ $this, 'archive_event_header' ],
				'ovaev_archive_event_settings', // Page
				'ovaev_archive_event_section_id' // Section ID
			);

			add_settings_field(
				'archive_event_footer', // ID
				esc_html__( 'Footer', 'ovaev' ),
				[ $this, 'archive_event_footer' ],
				'ovaev_archive_event_settings', // Page
				'ovaev_archive_event_section_id' // Section ID
			);

			// Add Section: Single Event Settings
			add_settings_section(
				'ovaev_single_event_section_id', // ID
				esc_html__( 'Single Event Setting', 'ovaev' ), // Title
				[ $this, 'print_options_section' ],
				'ovaev_single_event_settings' // Page
			);

			add_settings_field(
				'ovaev_show_title_single', // ID
				esc_html__( 'Show title', 'ovaev' ),
				[ $this, 'ovaev_show_title_single' ],
				'ovaev_single_event_settings', // Page
				'ovaev_single_event_section_id' // Section ID
			);

			add_settings_field(
				'single_event_header', // ID
				esc_html__( 'Header', 'ovaev' ),
				[ $this, 'single_event_header' ],
				'ovaev_single_event_settings', // Page
				'ovaev_single_event_section_id' // Section ID
			);

			add_settings_field(
				'single_event_footer', // ID
				esc_html__( 'Footer', 'ovaev' ),
				[ $this, 'single_event_footer' ],
				'ovaev_single_event_settings', // Page
				'ovaev_single_event_section_id' // Section ID
			);

			add_settings_field(
				'ovaev_get_template_single', // ID
				esc_html__( 'Templates', 'ovaev' ),
				[ $this, 'ovaev_get_template_single' ],
				'ovaev_single_event_settings', // Page
				'ovaev_single_event_section_id' // Section ID
			);
		}

		/**
		 * Input data
		 */
		public function settings_callback( $input ) {
			$new_input = [];

			// Show title single
			if ( isset( $input['ovaev_show_title_single'] ) ) {
				$new_input['ovaev_show_title_single'] = sanitize_text_field( $input['ovaev_show_title_single'] ) ? sanitize_text_field( $input['ovaev_show_title_single'] ) : 'yes';
			}

			// Get template single
			if ( isset( $input['ovaev_get_template_single'] ) ) {
				$new_input['ovaev_get_template_single'] = sanitize_text_field( $input['ovaev_get_template_single'] ) ? sanitize_text_field( $input['ovaev_get_template_single'] ) : 'default';
			}

			// Show past
			if ( isset( $input['ovaev_show_past'] ) ) {
				$new_input['ovaev_show_past'] = sanitize_text_field( $input['ovaev_show_past'] ) ? sanitize_text_field( $input['ovaev_show_past'] ) : 'yes';
			}

			// Event posts per page
			if ( isset( $input['archive_event_posts_per_page'] ) ) {
				$new_input['archive_event_posts_per_page'] = sanitize_text_field( $input['archive_event_posts_per_page'] ) ? sanitize_text_field( $input['archive_event_posts_per_page'] ) : 6;
			}

			// Event orderby
			if ( isset( $input['archive_event_orderby'] ) ) {
				$new_input['archive_event_orderby'] = sanitize_text_field( $input['archive_event_orderby'] ) ? sanitize_text_field( $input['archive_event_orderby'] ) : 'ID';
			}

			// Event order
			if ( isset( $input['archive_event_order'] ) ) {
				$new_input['archive_event_order'] = sanitize_text_field( $input['archive_event_order'] ) ? sanitize_text_field( $input['archive_event_order'] ) : 'DESC';
			}

			// Event thumbnail
			if ( isset( $input['archive_event_thumbnail'] ) ) {
				$new_input['archive_event_thumbnail'] = sanitize_text_field( $input['archive_event_thumbnail'] ) ? sanitize_text_field( $input['archive_event_thumbnail'] ) : '700x450';
			}

			// Event type
			if ( isset( $input['archive_event_type'] ) ) {
				$new_input['archive_event_type'] = sanitize_text_field( $input['archive_event_type'] ) ? sanitize_text_field( $input['archive_event_type'] ) : 'type1';
			}

			// Event column
			if ( isset( $input['archive_event_col'] ) ) {
				$new_input['archive_event_col'] = sanitize_text_field( $input['archive_event_col'] ) ? sanitize_text_field( $input['archive_event_col'] ) : 'col2';
			}
			
			// Event format date
			if ( isset( $input['archive_event_format_date'] ) ) {
				$new_input['archive_event_format_date'] = sanitize_text_field( $input['archive_event_format_date'] ) ? sanitize_text_field( $input['archive_event_format_date'] ) : 'd-m-Y';
			}

			// Format date lang
			if ( isset( $input['archive_format_date_lang'] ) ) {
				$new_input['archive_format_date_lang'] = sanitize_text_field( $input['archive_format_date_lang'] ) ? sanitize_text_field( $input['archive_format_date_lang'] ) : 'en';
			}

			// Event format time
			if ( isset( $input['archive_event_format_time'] ) ) {
				$new_input['archive_event_format_time'] = sanitize_text_field( $input['archive_event_format_time'] ) ? sanitize_text_field( $input['archive_event_format_time'] ) : 'H:i';
			}

			// Archive header
			if ( isset( $input['archive_event_header'] ) ) {
				$new_input['archive_event_header'] = sanitize_text_field( $input['archive_event_header'] ) ? sanitize_text_field( $input['archive_event_header'] ) : 'default';
			}

			// Archive footer
			if ( isset( $input['archive_event_footer'] ) ) {
				$new_input['archive_event_footer'] = sanitize_text_field( $input['archive_event_footer'] ) ? sanitize_text_field( $input['archive_event_footer'] ) : 'default';
			}

			// Single header
			if ( isset( $input['single_event_header'] ) ) {
				$new_input['single_event_header'] = sanitize_text_field( $input['single_event_header'] ) ? sanitize_text_field( $input['single_event_header'] ) : 'default';
			}

			// Single footer
			if ( isset( $input['single_event_footer'] ) ) {
				$new_input['single_event_footer'] = sanitize_text_field( $input['single_event_footer'] ) ? sanitize_text_field( $input['single_event_footer'] ) : 'default';
			}

			return $new_input;
		}

		/**
		 * Settings HTML
		 */
		public static function create_admin_setting_page() { ?>
			<div class="wrap">
				<h1>
					<?php esc_html_e( 'Event Settings', 'ovaev' ); ?>
				</h1>
				<form method="post" action="options.php">
					<div id="tabs">
						<?php settings_fields( 'ovaev_options_group' ); // Options group ?>
						<ul>
							<li>
								<a href="#ovaev_general_settings">
									<?php esc_html_e( 'General Settings', 'ovaev' ); ?>
								</a>
							</li>
							<li>
								<a href="#ovaev_event_settings">
									<?php esc_html_e( 'Event Settings', 'ovaev' ); ?>
								</a>
							</li>
						</ul>
						<div id="ovaev_general_settings" class="ovaev_admin_settings">
							<?php do_settings_sections( 'ovaev_general_settings' ); // Page ?>
						</div>
						<div id="ovaev_event_settings" class="ovaev_admin_settings">
							<?php do_settings_sections( 'ovaev_archive_event_settings' ); // Page ?>
							<hr>
							<?php do_settings_sections( 'ovaev_single_event_settings' ); // Page ?>
						</div>
					</div>
					<?php submit_button(); ?>
				</form>
			</div>
			<?php
		}

		/**
		 * Show past
		 */
		public function ovaev_show_past() {
			$show_past = OVAEV_Settings::ovaev_show_past();
			if ( !$show_past ) $show_past = 'yes';

			?>
			<select name="ovaev_options[ovaev_show_past]" id="ovaev_show_past">
				<option value="yes"<?php selected( 'yes', $show_past ); ?>>
					<?php echo esc_html__( 'Yes', 'ovaev' ); ?>
				</option>
				<option value="no"<?php selected( 'no', $show_past ); ?>>
					<?php echo esc_html__( 'No', 'ovaev' ); ?>
				</option>
			</select>
			<?php
		}

		/**
		 * Posts per page
		 */
		public function archive_event_posts_per_page() {
			$posts_per_page = OVAEV_Settings::archive_event_posts_per_page();
			if ( !$posts_per_page ) $posts_per_page = 6;

			?>
			<input
				type="number"
				id="archive_event_posts_per_page"
				name="ovaev_options[archive_event_posts_per_page]"
				value="<?php esc_attr_e( $posts_per_page ); ?>"
				min="-1"
				max="10"
				step="1" 
			/>
			<?php
		}

		/**
		 * Orderby
		 */
		public function archive_event_orderby() {
			$orderby = OVAEV_Settings::archive_event_orderby();
			if ( !$orderby ) $orderby = 'ID';

			?>
			<select name="ovaev_options[archive_event_orderby]" id="archive_event_orderby">
				<option value="title"<?php selected( 'title', $orderby ); ?>>
					<?php echo esc_html__( 'Title', 'ovaev' ); ?>
				</option>
				<option value="event_custom_sort"<?php selected( 'event_custom_sort', $orderby ); ?>>
					<?php echo esc_html__( 'Custom Sort', 'ovaev' ); ?>
				</option>
				<option value="ovaev_start_date"<?php selected( 'ovaev_start_date', $orderby ); ?>>
					<?php echo esc_html__( 'Start Date', 'ovaev' ); ?>
				</option>
				<option value="ID"<?php selected( 'ID', $orderby ); ?>>
					<?php echo esc_html__( 'ID', 'ovaev' ); ?>
				</option>
			</select>
			<?php
		}

		/**
		 * Order
		 */
		public function archive_event_order() {
			$order = OVAEV_Settings::archive_event_order(); 	
			if ( !$order ) $order = 'DESC';

			?>
			<select name="ovaev_options[archive_event_order]" id="archive_event_order">
				<option value="ASC"<?php selected( 'ASC', $order ); ?>>
					<?php echo esc_html__( 'Increasing', 'ovaev' ); ?>
				</option>
				<option value="DESC"<?php selected( 'DESC', $order ); ?>>
					<?php echo esc_html__( 'Decreasing', 'ovaev' ); ?>
				</option>
			</select>
			<?php
		}

		/**
		 * Type
		 */
		public function archive_event_type() {
			$type = OVAEV_Settings::archive_event_type(); 	
			if ( !$type ) $type = 'type1';

			?>
			<select name="ovaev_options[archive_event_type]" id="archive_event_type">
				<option value="type1"<?php selected( 'type1', $type ); ?>>
					<?php echo esc_html__( 'Event 1', 'ovaev' ); ?>
				</option>
				<option value="type2"<?php selected( 'type2', $type ); ?>>
					<?php echo esc_html__( 'Event 2', 'ovaev' ); ?>
				</option>
				<option value="type3"<?php selected( 'type3', $type ); ?>>
					<?php echo esc_html__( 'Event 3', 'ovaev' ); ?>
				</option>
				<option value="type4"<?php selected( 'type4', $type ); ?>>
					<?php echo esc_html__( 'Event 4', 'ovaev' ); ?>
				</option>
				<option value="type5"<?php selected( 'type5', $type ); ?>>
					<?php echo esc_html__( 'Event 5', 'ovaev' ); ?>
				</option>
				<option value="type6"<?php selected( 'type6', $type ); ?>>
					<?php echo esc_html__( 'Event 6', 'ovaev' ); ?>
				</option>
			</select>
			<?php
		}

		/**
		 * Columns
		 */
		public function archive_event_col() {
			$column = OVAEV_Settings::archive_event_col(); 	
			if ( !$column ) $column = 'col2';

			?>
			<select name="ovaev_options[archive_event_col]" id="archive_event_col">
				<option value="col1"<?php selected( 'col1', $column ); ?>>
					<?php echo esc_html__( 'Column 1', 'ovaev' ); ?>
				</option>
				<option value="col2"<?php selected( 'col2', $column ); ?>>
					<?php echo esc_html__( 'Column 2', 'ovaev' ); ?>
				</option>
				<option value="col3"<?php selected( 'col3', $column ); ?>>
					<?php echo esc_html__( 'Column 3', 'ovaev' ); ?>
				</option>
			</select>
			<?php
		}

		/**
		 * Format date
		 */
		public function archive_event_format_date() {
			// Get event date format
			$event_date_format = OVAEV_Settings::archive_event_format_date() ? OVAEV_Settings::archive_event_format_date() : 'd-m-Y';

			// Get all date format
			$date_format = apply_filters( 'ovaev_date_format', [
				'd-m-Y'	=> esc_html__( 'd-m-Y', 'ovaev' ),
				'm/d/Y'	=> esc_html__( 'm/d/Y', 'ovaev' ),
				'Y/m/d'	=> esc_html__( 'Y/m/d', 'ovaev' ),
				'Y-m-d'	=> esc_html__( 'Y-m-d', 'ovaev' )
			]);

			?>
			<select name="ovaev_options[archive_event_format_date]">
				<?php foreach ( $date_format as $k => $v ): ?>
					<option value="<?php echo esc_attr( $k ); ?>"<?php echo selected( $k, $event_date_format ); ?>>
						<?php echo esc_html( $v ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php 
		}

		/**
		 * Format time
		 */
		public function archive_event_format_time() {
			$event_format_time = OVAEV_Settings::archive_event_format_time() ? OVAEV_Settings::archive_event_format_time() : 'H:i';

			// Time format
			$time_format = apply_filters( 'ovaev_time_format', [
				'H:i'	=> 'H:i'.' '.esc_html__( '24 hour', 'ovaev' ),
				'g:i A'	=> 'g:i A'.' '.esc_html__( '12 hour', 'ovaev' ),
				'g:i a'	=> 'g:i a'.' '.esc_html__( '12 hour', 'ovaev' )
			]);

			?>
			<select name="ovaev_options[archive_event_format_time]">
				<?php foreach ( $time_format as $k => $v ): ?>
					<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $k, $event_format_time ); ?>>
						<?php echo esc_html( $v ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php	
		}

		/**
		 * Language
		 */
		public function archive_format_date_lang() {
			$date_format_lang = OVAEV_Settings::archive_format_date_lang() ? OVAEV_Settings::archive_format_date_lang() : 'en';

			$langauges = [
				'ar'	=> esc_html__( 'Arabic', 'ovaev' ),
				'az'	=> esc_html__( 'Azerbaijanian', 'ovaev' ),
				'bg'	=> esc_html__( 'Bulgarian', 'ovaev' ),
				'bs'	=> esc_html__( 'Bosanski', 'ovaev' ),
				'ca'	=> esc_html__( 'Català', 'ovaev' ),
				'ch'	=> esc_html__( 'Simplified Chinese', 'ovaev' ),
				'cs'	=> esc_html__( 'Čeština', 'ovaev' ),
				'da'	=> esc_html__( 'Dansk', 'ovaev' ),
				'de'	=> esc_html__( 'German', 'ovaev' ),
				'el'	=> esc_html__( 'Ελληνικά', 'ovaev' ),
				'en'	=> esc_html__( 'English', 'ovaev' ),
				'en-GB'	=> esc_html__( 'English(British)', 'ovaev' ),
				'es'	=> esc_html__( 'Spanish', 'ovaev' ),
				'et'	=> esc_html__( 'Eesti', 'ovaev' ),
				'eu'	=> esc_html__( 'Euskara', 'ovaev' ),
				'fa'	=> esc_html__( 'Finnish(Suomi)', 'ovaev' ),
				'fr'	=> esc_html__( 'French', 'ovaev' ),
				'gl'	=> esc_html__( 'Galego', 'ovaev' ),
				'he'	=> esc_html__( 'Hebrew', 'ovaev' ),
				'hr'	=> esc_html__( 'Hrvatski', 'ovaev' ),
				'hu'	=> esc_html__( 'Hungarian', 'ovaev' ),
				'id'	=> esc_html__( 'Indonesian', 'ovaev' ),
				'it'	=> esc_html__( 'Italian', 'ovaev' ),
				'ja'	=> esc_html__( 'Japanese', 'ovaev' ),
				'ko'	=> esc_html__( 'Korean', 'ovaev' ),
				'kr'	=> esc_html__( 'Korean', 'ovaev' ),
				'lt'	=> esc_html__( 'Lithuanian', 'ovaev' ),
				'lv'	=> esc_html__( 'Latvian', 'ovaev' ),
				'mk'	=> esc_html__( 'Macedonian', 'ovaev' ),
				'mn'	=> esc_html__( 'Mongolian', 'ovaev' ),
				'nl'	=> esc_html__( 'Dutch', 'ovaev' ),
				'no'	=> esc_html__( 'Norwegian', 'ovaev' ),
				'pl'	=> esc_html__( 'Polish', 'ovaev' ),
				'pt'	=> esc_html__( 'Portuguese', 'ovaev' ),
				'pt-BR'	=> esc_html__( 'Português', 'ovaev' ),
				'ro'	=> esc_html__( 'Romanian', 'ovaev' ),
				'ru'	=> esc_html__( 'Russian', 'ovaev' ),
				'se'	=> esc_html__( 'Swedish', 'ovaev' ),
				'sk'	=> esc_html__( 'Slovenčina', 'ovaev' ),
				'sl'	=> esc_html__( 'Slovenščina', 'ovaev' ),
				'sq'	=> esc_html__( 'Albanian', 'ovaev' ),
				'sr'	=> esc_html__( 'Serbian', 'ovaev' ),
				'sr-YU'	=> esc_html__( 'Serbian (Srpski)', 'ovaev' ),
				'sv'	=> esc_html__( 'Svenska', 'ovaev' ),
				'th'	=> esc_html__( 'Thai', 'ovaev' ),
				'tr'	=> esc_html__( 'Turkish', 'ovaev' ),
				'uk'	=> esc_html__( 'Ukrainian', 'ovaev' ),
				'vi'	=> esc_html__( 'Vietnamese', 'ovaev' ),
				'zh'	=> esc_html__( 'Simplified Chinese ', 'ovaev' ),
				'zh-TW'	=> esc_html__( 'Traditional Chinese', 'ovaev' )
			];

			?>
			<select name="ovaev_options[archive_format_date_lang]">
				<?php foreach ( $langauges as $k => $v ): ?>
					<option value="<?php echo esc_attr( $k ); ?>"<?php echo selected( $k, $date_format_lang ); ?>>
						<?php echo esc_html( $v ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
		}

		/**
		 * Single Event Settings
		 */
		public function ovaev_show_title_single() {
			$show_title_single = OVAEV_Settings::ovaev_show_title_single();
			if ( !$show_title_single ) $show_title_single = 'yes';

			?>
			<select name="ovaev_options[ovaev_show_title_single]" id="ovaev_show_title_single">
				<option value="yes"<?php selected( 'yes', $show_title_single ); ?>>
					<?php echo esc_html__( 'Yes', 'ovaev' ); ?>
				</option>
				<option value="no"<?php selected( 'no', $show_title_single ); ?>>
					<?php echo esc_html__( 'No', 'ovaev' ); ?>
				</option>
			</select>
			<?php
		}

		/**
		 * Template single
		 */
		public function ovaev_get_template_single() {
			// Template single
			$template_single = OVAEV_Settings::ovaev_get_template_single();
			if ( !$template_single ) $template_single = 'default';

			// Get templates
			$templates = get_posts([
				'post_type' 	=> 'elementor_library',
				'meta_key' 		=> '_elementor_template_type',
				'meta_value' 	=> 'page'
			]);

			?>
			<div class="ovaev_template_single">
				<select name="ovaev_options[ovaev_get_template_single]" id="ovaev_get_template_single">
					<option value="default"<?php selected( 'default', $template_single ); ?>>
						<?php echo esc_html__( 'Default', 'ovaev' ); ?>
					</option>
					<?php if ( !empty( $templates ) && is_array( $templates ) ):
						foreach ( $templates as $template ):
							$id 	= $template->ID;
							$title 	= $template->post_title;
						?>
						<option value="<?php echo esc_attr( $id ); ?>"<?php selected( $id, $template_single ); ?>>
							<?php echo esc_html( $title ); ?>
						</option>
					<?php endforeach;
					endif; ?>
				</select>
				<?php
					echo '<br/>'; 
					esc_html_e( 'Default or Other (made in Templates of Elementor)', 'ovaev' );
				?>
			</div>
			<?php
		}

		/**
		 * Archive thumbnail size
		 */
		public function archive_event_thumbnail() {
			$event_thumbnail = OVAEV_Settings::archive_event_thumbnail();
			printf( '<input type="text" id="archive_event_thumbnail"  name="ovaev_options[archive_event_thumbnail]" value="%s" />', esc_attr( $event_thumbnail ) );
			echo '<br/>'; 
			esc_html_e( 'Example: 700x450', 'ovaev' );
		}

		/**
		 * Archive header
		 */
		public function archive_event_header() {
			// Get current header
			$current_header = OVAEV_Settings::archive_event_header();

			// Get list header
			$list_header = apply_filters( 'tripgo_list_header', [] );

			?>
			<select name="ovaev_options[archive_event_header]" id="archive_event_header">
				<?php if ( !empty( $list_header ) && is_array( $list_header ) ):
					foreach ( $list_header as $k => $v  ): ?>
						<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $k, $current_header ); ?>>
							<?php echo esc_html( $v ); ?>
						</option>
					<?php endforeach;
				endif; ?>
			</select>
			<?php
		}

		/**
		 * Archive footer
		 */
		public function archive_event_footer() {
			// Get current footer
			$current_footer = OVAEV_Settings::archive_event_footer();

			// Get list footer
			$list_footer = apply_filters( 'tripgo_list_footer', [] );

			?>
			<select name="ovaev_options[archive_event_footer]" id="archive_event_footer">
				<?php if ( !empty( $list_footer ) && is_array( $list_footer ) ):
					foreach ( $list_footer as $k => $v ): ?>
						<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $k, $current_footer ); ?>>
							<?php echo esc_html( $v ); ?>
						</option>
					<?php endforeach;
				endif; ?>
			</select>
			<?php
		}

		/**
		 * Single header
		 */
		public function single_event_header() {
			// Get current header
			$current_header = OVAEV_Settings::single_event_header();

			// List header
			$list_header = apply_filters( 'tripgo_list_header', [] );

			?>
			<select name="ovaev_options[single_event_header]" id="single_event_header">
				<?php if ( !empty( $list_header ) && is_array( $list_header ) ):
					foreach ( $list_header as $k => $v ): ?>
						<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $k, $current_header ); ?>>
							<?php echo esc_html( $v ); ?>
						</option>
					<?php endforeach;
				endif; ?>
			</select>
			<?php
		}

		/**
		 * Single footer
		 */
		public function single_event_footer() {
			// Get current footer
			$current_footer = OVAEV_Settings::single_event_footer();

			// Get list footer
			$list_footer = apply_filters( 'tripgo_list_footer', [] );

			?>
			<select name="ovaev_options[single_event_footer]" id="single_event_footer">
				<?php if ( !empty( $list_footer ) && is_array( $list_footer ) ):
					foreach ( $list_footer as $k => $v ): ?>
						<option value="<?php echo esc_attr( $k ); ?>"<?php selected( $k, $current_footer ); ?>>
							<?php echo esc_html( $v ); ?>
						</option>
					<?php endforeach;
				endif; ?>
			</select>
			<?php
		}
	}

	// init class
	new OVAEV_Admin_Settings();
}