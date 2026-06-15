<?php
if(!defined('ABSPATH')){ exit; }

if(!class_exists('THWMSCF_Settings')):

class THWMSCF_Settings {
	const WMSC_SETTINGS = 'THWMSC_SETTINGS';
	protected static $_instance = null;	
	private $tabs = '';
	private $settings = '';
    private $screen_id;
	
	private $cell_props = array();
	private $cell_props_L = array();
	private $cell_props_R = array();
	private $cell_props_CB = array(); 

	public function __construct(){
		$this->tabs = array( 'msc_settings' => 'General Settings', 'premium_features' => 'Premium features');
		
		$this->cell_props = array( 
			'label_cell_props' => 'style="width: 23%;" class="titledesc" scope="row"', 
			'input_cell_props' => 'class="forminp"', 
			'input_width' => '250px', 'label_cell_th' => true 
		);
		$this->cell_props_L = array( 
			'label_cell_props' => 'style="width: 23%;" class="titledesc" scope="row"', 
			'input_cell_props' => 'style="width: 25%;" class="forminp"', 
			'input_width' => '250px', 'label_cell_th' => true 
		);
		$this->cell_props_R = array( 
			'label_cell_props' => 'style="width: 15%;" class="titledesc" scope="row"', 
			'input_cell_props' => 'style="width: 30%;" class="forminp" ', 
			'input_width' => '250px', 'label_cell_th' => true 
		);
		//$this->cell_props_R = array( 'label_cell_width' => '13%', 'input_cell_width' => '34%', 'input_width' => '250px' );
		$this->cell_props_CB = array( 'cell_props' => 'colspan="3"' );

		$this->settings = $this->get_settings();

		add_action('admin_head', array( $this, 'review_banner_custom_css') );

		add_action( 'admin_init', array( $this, 'thwmsc_notice_actions' ), 20 );
		add_action( 'admin_notices', array($this, 'output_review_request_link'));

		add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
		add_action('admin_menu', array($this, 'admin_menu'));
		add_filter('woocommerce_screen_ids', array($this, 'add_screen_id'));

		add_filter('plugin_action_links_'.THWMSCF_BASE_NAME, array($this, 'add_settings_link'));
		add_action('thwmscf_woocommerce_checkout_review_order', 'woocommerce_order_review');

		add_action('thwmscf_woocommerce_before_checkout_form', array($this, 'hide_checkout_coupon_form'), 10);
		add_action('thwmscf_woocommerce_review_order_before_payment', array($this, 'woocommerce_checkout_coupon_form_custom'));

		add_filter('thwmscf_steps_front_end', array($this, 'thwmsc_make_order_review_on_right'), 10);
		add_action('thwmscf_multi_step_tab_panels', array($this, 'add_review_order_on_right_side'), 25);
        add_action('thwmscf_multi_step_after_tab_panels', array($this, 'render_buttons_after_tab_panels'), 10);

		add_action('admin_footer', array($this, 'admin_notice_js_snippet'), 9999);
		add_action('wp_ajax_hide_thwmscf_admin_notice', array($this, 'hide_thwmscf_admin_notice'));

        add_action('admin_footer', array($this, 'quick_links'), 10);

		add_action('admin_footer-plugins.php', array($this, 'thwmscf_deactivation_form'));
        add_action('wp_ajax_thwmscf_deactivation_reason', array($this, 'thwmscf_deactivation_reason'));

        add_action( 'wp_ajax_thwmscf_step_validation', array( $this, 'thwmscf_step_validation' ), 1 );
        add_action( 'wp_ajax_nopriv_thwmscf_step_validation', array( $this, 'thwmscf_step_validation' ), 1 );
		
		$this->init();
	}

	public static function instance(){
		if(is_null(self::$_instance)){
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * menu function.
	 */
	public function admin_menu() {	
		$this->screen_id = add_submenu_page('woocommerce', __('Woo Multistep Checkout', 'woo-multistep-checkout'), __('Multistep Checkout', 'woo-multistep-checkout'), 
		'manage_woocommerce', 'woo_multistep_checkout', array($this, 'multistep_checkout'));

	}	
	
	public function add_settings_link($links) {
		$settings_link = '<a href="'. esc_url(admin_url('admin.php?page=woo_multistep_checkout')) .'">'. __('Settings') .'</a>';
		array_unshift($links, $settings_link);
        $pro_link = '<a style="color:green; font-weight:bold" target="_blank" href="https://www.themehigh.com/product/woocommerce-multi-step-checkout/?utm_source=free&utm_medium=plugin_action_link&utm_campaign=wmsc_upgrade_link">'. __('Get Pro', 'woo-multistep-checkout') .'</a>';
        array_push($links,$pro_link);

		if (array_key_exists('deactivate', $links)) {
            $links['deactivate'] = str_replace('<a', '<a class="thwmscf-deactivate-link"', $links['deactivate']);
        }

		return $links;
	}

	function enqueue_admin_scripts($hook) {
		if(strpos($hook, 'page_woo_multistep_checkout') === false) {
			return;
		}

		wp_enqueue_style('woocommerce_admin_styles');		
		wp_enqueue_style('thwmscf-admin-style', plugins_url('/assets/css/thwmscf-admin.css', dirname(__FILE__)), THWMSCF_VERSION);  
		wp_enqueue_script('thwmscf-admin-js', THWMSCF_ASSETS_URL.'js/thwmscf-admin.js',array('jquery','wp-color-picker'), THWMSCF_VERSION, true);
	}

	/**
	 * add_screen_id function.
	 */
	function add_screen_id($ids){
		$ids[] = 'woocommerce_multistep_checkout';
		$ids[] = strtolower(__('WooCommerce', 'woo-multistep-checkout')) .'_multistep_checkout';

		return $ids;
	}

	function multistep_checkout() { 		
		$this->wmsc_design();
	}

	public function get_settings(){		
		$settings_default = array(
			'enable_wmsc' 			=> __('yes','woo-multistep-checkout'),
			'title_login' 			=> __('Login','woo-multistep-checkout'),
			'title_billing' 		=> __('Billing details','woo-multistep-checkout'),
			'title_shipping' 		=> __('Shipping details','woo-multistep-checkout'),
			'title_order_review' 	=> __('Order review','woo-multistep-checkout'),
			'title_confirm_order' 	=> __('Payment','woo-multistep-checkout'),
			'step_bg_color'   		=> '#F4F4F4',
			'step_text_color'		=> '#8B8B8B',
			'step_bg_color_active'  => '#41359F',
			'step_text_color_active'=> '#FFFFFF',
			'tab_panel_bg_color' 	=> '#FFFFFF',
		);
		$saved_settings = $this->get_wmsc_settings();
		
		$settings = !empty($saved_settings) ? $saved_settings : $settings_default ;
		return apply_filters('thwmcf_plugin_settings', $settings);

	}

    public function thwmscf_deactivation_form(){
        $is_snooze_time = get_user_meta( get_current_user_id(), 'thwmscf_deactivation_snooze', true );
        $now = time();

        if($is_snooze_time && ($now < $is_snooze_time)){
            return;
        }

        $deactivation_reasons = $this->get_deactivation_reasons();
        ?>
        <div id="thwmscf_deactivation_form" class="thpladmin-modal-mask">
            <div class="thpladmin-modal">
                <div class="modal-container">
                    <div class="modal-content">
                        <div class="modal-body">
                            <div class="model-header">
                                <img class="th-logo" src="<?php echo esc_url(THWMSCF_URL .'assets/images/themehigh.svg'); ?>" alt="themehigh-logo">
                                <span><?php echo esc_html__('Quick Feedback', 'woo-multistep-checkout'); ?></span>
                            </div>

                            <!-- <div class="get-support-version-b">
                                <p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s,</p>
                                <a class="thwmscf-link thwmscf-right-link thwmscf-active" target="_blank" href="https://help.themehigh.com/hc/en-us/requests/new"><?php echo esc_html__('Get Support', 'woo-multistep-checkout'); ?></a>
                            </div> -->

                            <main class="form-container main-full">
                                <p class="thwmscf-title-text"><?php echo esc_html__('If you have a moment, please let us know why you want to deactivate this plugin', 'woo-multistep-checkout'); ?></p>
                                <ul class="deactivation-reason" data-nonce="<?php echo esc_attr(wp_create_nonce('thwmscf_deactivate_nonce') ); ?>">
                                    <?php 
                                    if($deactivation_reasons){
                                        foreach($deactivation_reasons as $key => $reason){
                                            $reason_type = isset($reason['reason_type']) ? $reason['reason_type'] : '';
                                            $reason_placeholder = isset($reason['reason_placeholder']) ? $reason['reason_placeholder'] : '';
                                            ?>
                                            <li data-type="<?php echo esc_attr($reason_type); ?>" data-placeholder="<?php echo esc_attr($reason_placeholder); ?> ">
                                                <label>
                                                    <input type="radio" name="selected-reason" value="<?php echo esc_attr($key); ?>">
                                                    <span><?php echo esc_html($reason['radio_label']); ?></span>
                                                </label>
                                            </li>
                                            <?php
                                        }
                                    }
                                    ?>
                                </ul>
                                <p class="thwmscf-privacy-cnt"><?php echo esc_html__('This form is only for getting your valuable feedback. We do not collect your personal data. To know more read our ', 'woo-multistep-checkout'); ?> <a class="thwmscf-privacy-link" target="_blank" href="<?php echo esc_url('https://www.themehigh.com/privacy-policy/');?>"><?php echo esc_html__('Privacy Policy', 'woo-multistep-checkout'); ?></a></p>
                            </main>
                            <footer class="modal-footer">
                                <div class="thwmscf-left">
                                    <a class="thwmscf-link thwmscf-left-link thwmscf-deactivate" href="#"><?php echo esc_html__('Skip & Deactivate', 'woo-multistep-checkout'); ?></a>
                                </div>
                                <div class="thwmscf-right">
                                    <a class="thwmscf-link thwmscf-right-link thwmscf-active" target="_blank" href="https://help.themehigh.com/hc/en-us/requests/new"><?php echo esc_html__('Get Support', 'woo-multistep-checkout'); ?></a>
                                    <a class="thwmscf-link thwmscf-right-link thwmscf-active thwmscf-submit-deactivate" href="#"><?php echo esc_html__('Submit and Deactivate', 'woo-multistep-checkout'); ?></a>
                                    <a class="thwmscf-link thwmscf-right-link thwmscf-close" href="#"><?php echo esc_html__('Cancel', 'woo-multistep-checkout'); ?></a>
                                </div>
                            </footer>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <style type="text/css">
            .th-logo{
                margin-right: 10px;
            }
            .thpladmin-modal-mask{
                position: fixed;
                background-color: rgba(17,30,60,0.6);
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                z-index: 9999;
                overflow: scroll;
                transition: opacity 250ms ease-in-out;
            }
            .thpladmin-modal-mask{
                display: none;
            }
            .thpladmin-modal .modal-container{
                position: absolute;
                background: #fff;
                border-radius: 2px;
                overflow: hidden;
                left: 50%;
                top: 50%;
                transform: translate(-50%,-50%);
                width: 50%;
                max-width: 960px;
                /*min-height: 560px;*/
                /*height: 80vh;*/
                /*max-height: 640px;*/
                animation: appear-down 250ms ease-in-out;
                border-radius: 15px;
            }
            .model-header {
                padding: 21px;
            }
            .thpladmin-modal .model-header span {
                font-size: 18px;
                font-weight: bold;
            }
            .thpladmin-modal .model-header {
                padding: 21px;
                background: #ECECEC;
            }
            .thpladmin-modal .form-container {
                margin-left: 23px;
                clear: both;
            }
            .thpladmin-modal .deactivation-reason input {
                margin-right: 13px;
            }
            .thpladmin-modal .thwmscf-privacy-cnt {
                color: #919191;
                font-size: 12px;
                margin-bottom: 31px;
                margin-top: 18px;
                max-width: 75%;
            }
            .thpladmin-modal .deactivation-reason li {
                margin-bottom: 17px;
            }
            .thpladmin-modal .modal-footer {
                padding: 20px;
                border-top: 1px solid #E7E7E7;
                float: left;
                width: 100%;
                box-sizing: border-box;
            }
            .thwmscf-left {
                float: left;
            }
            .thwmscf-right {
                float: right;
            }
            .thwmscf-link {
                line-height: 31px;
                font-size: 12px;
            }
            .thwmscf-left-link {
                font-style: italic;
            }
            .thwmscf-right-link {
                padding: 0px 20px;
                border: 1px solid;
                display: inline-block;
                text-decoration: none;
                border-radius: 5px;
            }
            .thwmscf-right-link.thwmscf-active {
                background: #0773AC;
                color: #fff;
            }
            .thwmscf-title-text {
                color: #2F2F2F;
                font-weight: 500;
                font-size: 15px;
            }
            .reason-input {
                margin-left: 31px;
                margin-top: 11px;
                width: 70%;
            }
            .reason-input input {
                width: 100%;
                height: 40px;
            }
            .reason-input textarea {
                width: 100%;
                min-height: 80px;
            }
            input.th-snooze-checkbox {
                width: 15px;
                height: 15px;
            }
            input.th-snooze-checkbox:checked:before {
                width: 1.2rem;
                height: 1.2rem;
            }
            .th-snooze-select {
                margin-left: 20px;
                width: 172px;
            }

            /* Version B */
            .get-support-version-b {
                width: 100%;
                padding-left: 23px;
                clear: both;
                float: left;
                box-sizing: border-box;
                background: #0673ab;
                color: #fff;
                margin-bottom: 20px;
            }
            .get-support-version-b p {
                font-size: 12px;
                line-height: 17px;
                width: 70%;
                display: inline-block;
                margin: 0px;
                padding: 15px 0px;
            }
            .get-support-version-b .thwmscf-right-link {
                background-image: url(<?php echo esc_url(THWMSCF_URL .'assets/css/get_support_icon.svg'); ?>);
                background-repeat: no-repeat;
                background-position: 11px 10px;
                padding-left: 31px;
                color: #0773AC;
                background-color: #fff;
                float: right;
                margin-top: 17px;
                margin-right: 20px;
            }
            .thwmscf-privacy-link {
                font-style: italic;
            }
        </style>

        <script type="text/javascript">
            (function($){
                var popup = $("#thwmscf_deactivation_form");
                var deactivation_link = '';

                $('.thwmscf-deactivate-link').on('click', function(e){
                    e.preventDefault();
                    deactivation_link = $(this).attr('href');
                    popup.css("display", "block");
                    popup.find('a.thwmscf-deactivate').attr('href', deactivation_link);
                });

                popup.on('click', 'input[type="radio"]', function () {
                    var parent = $(this).parents('li:first');
                    popup.find('.reason-input').remove();

                    var type = parent.data('type');
                    var placeholder = parent.data('placeholder');

                    var reason_input = '';
                    if('text' == type){
                        reason_input += '<div class="reason-input">';
                        reason_input += '<input type="text" placeholder="'+ placeholder +'">';
                        reason_input += '</div>';
                    }else if('textarea' == type){
                        reason_input += '<div class="reason-input">';
                        reason_input += '<textarea row="5" placeholder="'+ placeholder +'">';
                        reason_input += '</textarea>';
                        reason_input += '</div>';
                    }else if('checkbox' == type){
                        reason_input += '<div class="reason-input ">';
                        reason_input += '<input type="checkbox" id="th-snooze" name="th-snooze" class="th-snooze-checkbox">';
                        reason_input += '<label for="th-snooze">Snooze this panel while troubleshooting</label>';
                        reason_input += '<select name="th-snooze-time" class="th-snooze-select" disabled>';
                        reason_input += '<option value="<?php echo esc_attr(HOUR_IN_SECONDS) ?>">1 Hour</option>';
                        reason_input += '<option value="<?php echo esc_attr(12*HOUR_IN_SECONDS) ?>">12 Hour</option>';
                        reason_input += '<option value="<?php echo esc_attr(DAY_IN_SECONDS) ?>">24 Hour</option>';
                        reason_input += '<option value="<?php echo esc_attr(WEEK_IN_SECONDS) ?>">1 Week</option>';
                        reason_input += '<option value="<?php echo esc_attr(MONTH_IN_SECONDS) ?>">1 Month</option>';
                        reason_input += '</select>';
                        reason_input += '</div>';
                    }else if('reviewlink' == type){
                    	reason_input += '<div class="reason-input wmsc-review-link">';
                    	/*
                    	reason_input += '<?php esc_html_e('Deactivate and ', 'woo-multistep-checkout');?>'
                    	reason_input += '<a href="#" target="_blank" class="thwmsc-review-and-deactivate">';
                    	reason_input += '<?php esc_html_e('leave a review', 'woo-multistep-checkout'); ?>';
                    	reason_input += '<span class="wmscf-rating-link"> &#9733;&#9733;&#9733;&#9733;&#9733; </span>';
                    	reason_input += '</a>';
                    	*/
                    	reason_input += '<input type="hidden" value="<?php esc_html_e('Upgraded', 'woo-multistep-checkout');?>">';
                    	reason_input += '</div>';
                    }

                    if(reason_input !== ''){
                        parent.append($(reason_input));
                    }
                });

                popup.on('click', '.thwmscf-close', function () {
                    popup.css("display", "none");
                });

                popup.on('click', '.thwmscf-submit-deactivate', function (e) {
                    e.preventDefault();
                    var button = $(this);
                    if (button.hasClass('disabled')) {
                        return;
                    }
                    var radio = $('.deactivation-reason input[type="radio"]:checked');
                    var parent_li = radio.parents('li:first');
                    var parent_ul = radio.parents('ul:first');
                    var input = parent_li.find('textarea, input[type="text"], input[type="hidden"]');
                    var wmscf_deacive_nonce = parent_ul.data('nonce');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'thwmscf_deactivation_reason',
                            reason: (0 === radio.length) ? 'none' : radio.val(),
                            comments: (0 !== input.length) ? input.val().trim() : '',
                            security: wmscf_deacive_nonce,
                        },
                        beforeSend: function () {
                            button.addClass('disabled');
                            button.text('Processing...');
                        },
                        complete: function () {
                            window.location.href = deactivation_link;
                        }
                    });
                });

                popup.on('click', '#th-snooze', function () {
                    if($(this).is(':checked')){
                        popup.find('.th-snooze-select').prop("disabled", false);
                    }else{
                        popup.find('.th-snooze-select').prop("disabled", true);
                    }
                });

            }(jQuery))
        </script>

        <?php 
    }

    private function get_deactivation_reasons(){
        return array(
        	'upgraded_to_pro' => array(
				'radio_val'          => 'upgraded_to_pro',
				'radio_label'        => __('Upgraded to premium.', 'woo-multistep-checkout'),
				'reason_type'        => 'reviewlink',
				'reason_placeholder' => '',
			),

            'found_better_plugin' => array(
                'radio_val'          => 'found_better_plugin',
                'radio_label'        => __('I found a better Plugin', 'woo-multistep-checkout'),
                'reason_type'        => 'text',
                'reason_placeholder' => __('Could you please mention the plugin?', 'woo-multistep-checkout'),
            ),

            'hard_to_use' => array(
                'radio_val'          => 'hard_to_use',
                'radio_label'        => __('It was hard to use', 'woo-multistep-checkout'),
                'reason_type'        => 'text',
                'reason_placeholder' => __('How can we improve your experience?', 'woo-multistep-checkout'),
            ),

            'feature_missing'=> array(
                'radio_val'          => 'feature_missing',
                'radio_label'        => __('A specific feature is missing', 'woo-multistep-checkout'),
                'reason_type'        => 'text',
                'reason_placeholder' => __('Type in the feature', 'woo-multistep-checkout'),
            ),

            'not_working_as_expected'=> array(
                'radio_val'          => 'not_working_as_expected',
                'radio_label'        => __('The plugin didn’t work as expected', 'woo-multistep-checkout'),
                'reason_type'        => 'text',
                'reason_placeholder' => __('Specify the issue', 'woo-multistep-checkout'),
            ),

            'temporary' => array(
                'radio_val'          => 'temporary',
                'radio_label'        => __('It’s a temporary deactivation - I’m troubleshooting an issue', 'woo-multistep-checkout'),
                'reason_type'        => 'checkbox',
                'reason_placeholder' => __('Could you please mention the plugin?', 'woo-multistep-checkout'),
            ),

            'other' => array(
                'radio_val'          => 'other',
                'radio_label'        => __('Not mentioned here', 'woo-multistep-checkout'),
                'reason_type'        => 'textarea',
                'reason_placeholder' => __('Kindly tell us your reason, so that we can improve', 'woo-multistep-checkout'),
            ),
        );
    }

    public function thwmscf_deactivation_reason(){
        global $wpdb;

        check_ajax_referer('thwmscf_deactivate_nonce', 'security');

        if(!isset($_POST['reason'])){
            return;
        }

        if($_POST['reason'] === 'temporary'){

            $snooze_period = isset($_POST['th-snooze-time']) && $_POST['th-snooze-time'] ? $_POST['th-snooze-time'] : MINUTE_IN_SECONDS ;
            $time_now = time();
            $snooze_time = $time_now + $snooze_period;

            update_user_meta(get_current_user_id(), 'thwmscf_deactivation_snooze', $snooze_time);

            return;
        }
        
        $data = array(
            'plugin'        => 'wmsc',
            'reason'        => sanitize_text_field($_POST['reason']),
            'comments'      => isset($_POST['comments']) ? sanitize_textarea_field(wp_unslash($_POST['comments'])) : '',
            'date'          => gmdate("M d, Y h:i:s A"),
            'software'      => $_SERVER['SERVER_SOFTWARE'],
            'php_version'   => phpversion(),
            'mysql_version' => $wpdb->db_version(),
            'wp_version'    => get_bloginfo('version'),
            'wc_version'    => (!defined('WC_VERSION')) ? '' : WC_VERSION,
            'locale'        => get_locale(),
            'multisite'     => is_multisite() ? 'Yes' : 'No',
            'plugin_version'=> THWMSCF_VERSION
        );

        $response = wp_remote_post('https://feedback.themehigh.in/api/add_feedbacks', array(
            'method'      => 'POST',
            'timeout'     => 45,
            'redirection' => 5,
            'httpversion' => '1.0',
            'blocking'    => false,
            'headers'     => array( 'Content-Type' => 'application/json' ),
            'body'        => json_encode($data),
            'cookies'     => array()
                )
        );

        wp_send_json_success();
    }

    public function review_banner_custom_css(){

        ?>
        <style>
        	.thwmsc-notice .logo {
			    float: right;
			}
			.thwmsc-notice .logo img {
			    height: 18px;
			    margin-top: 12px;
			}
			.thwmsc-review-wrapper {
			    padding: 0px 28px 21px 0px !important;
			    margin-top: 35px;
                background: linear-gradient(92.32deg, #6E55FF 37.32%, #845DE2 101.65%);
                border: none;
                border-radius: 10px
			}
			.thwmsc-review-image {
			    float: left;
                position: relative;
                left: -1px;
			}
			.thwmsc-review-content p {
                color: #ffffff;
                line-height: 21.5px;
			}
            .thwmsc-review-content-text {
                padding-bottom: 14px !important;
                font-style: italic;
            }
            .thwmsc-review-content h3 {
                color: #ffffff;
                padding-top: 19px;
                margin: 0px;
            }
			.thwmsc-review-content {
			    padding-right: 5px;
                padding-left: 160px;
			}
            .thwmsc-review-wrapper .notice-dismiss::before {
                color: #ffffff;
                font-size: 19px;
            }
			.thwmsc-notice-action{ 
			    padding: 8px 18px 8px 18px;
			    background: #6A43E6;
			    color: #ffffff;
			    border-radius: 2px;
			    border: none;
			}
			.thwmsc-notice-action.thwmsc-yes {
			    background-color: #ffffff;
			    color: #5647BE;
			}
			.thwmsc-notice-action.thwmsc-yes:hover {
			    background: #393176;
                color: #ffffff;
			}
            .thwmsc-notice-action:hover {
                color: #ffffff;
                background: #5630CC;
            }
			.thwmsc-notice-action .dashicons{
			    display: none;
			}
			.thwmsc-themehigh-logo {
                position: absolute;
                background: white;
                padding: 8px 20px 6px 20px;
                right: 0px;
                border-radius: 15px 0px 10px 0px;
                top: calc(100% - 35px);
			}
            .thwmsc-review-star {
                position: absolute;
                padding: 3px;
                right: 152px;
                top: calc(100% - 35px);
            }
            .thwmsc-review-image img {
                height: 149px;
            }
            @media only screen and (max-width: 1436px) {
                .thwmsc-review-image img {
                    height: 171px;
                }
            }
            .thwmsc-notice button.notice-dismiss {
                right: 10px;
                top: 10px;
            }
        </style>
        <?php    
    }



	public function get_tabs(){
		return $this->tabs; 
	}

	function get_current_tab(){
		return isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'msc_settings';
	}

	public function get_settings_fields(){
		$tab_postion = array(
			'align-left' 	=> __('Left','woo-multistep-checkout'),
			'align-center' 	=> __('Center','woo-multistep-checkout')
		);

		$layout_options = array(			
			'thwmscf_horizontal_box' => array('name' => __('Horizontal Box Layout', 'woo-multistep-checkout'), 'layout_image' => 'horizontal_box.svg'),
			'thwmscf_time_line_step' 	 => array('name' => __('Time Line Layout', 'woo-multistep-checkout'), 'layout_image' => 'timeline.svg'),
            'thwmscf_vertical_box'   => array('name' => __('Vertical Box Layout', 'woo-multistep-checkout'), 'layout_image' => 'vertical_box.svg'),
			'thwmscf_accordion_step' 	 => array('name' => __('Accordion Layout', 'woo-multistep-checkout'), 'layout_image' => 'accordion.svg'),
		);

		$layout_field = array(
			'enable_wmsc' => array(
				'name'=>'enable_wmsc', 'label'=>__('Activate Multi-Step', 'woo-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1	
			),	
            'title_display_general_sttng' => array('title'=>__('General Settings', 'woo-multistep-checkout'), 'type'=>'separator', 'colspan'=>'6'),
			'title_display_texts' => array('title'=>__('Step Display Texts', 'woo-multistep-checkout'), 'type'=>'separator', 'colspan'=>'6'),
			'enable_login_step' => array(
				'name'=>'enable_login_step', 'label'=>__('Display Login Step', 'woo-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0, 'hint_text'=>__("Enable 'Allow customers to log into an existing account during checkout' under woocommerce Accounts & Privacy settings tab.",'woo-multistep-checkout' ), 'onchange'=>'thwmscfDisplayLogin(this)',
			),
			'enable_step_validation' => array(
				'name'=>'enable_step_validation', 'label'=>__('Activate Step Validation', 'woo-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>1
			),
			'coupon_form_above_payment' => array(
				'name'=>'coupon_form_above_payment', 'label'=>__('Show Coupon form above Payment', 'woo-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0,
			),
			
			'make_billing_shipping_together' => array(
				'name'=>'make_billing_shipping_together', 'label'=>__('Combine Billing Step and Shipping Step', 'woo-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0, 'onchange'=>'thwmscfShippingTitle(this)'
			),
			'make_order_review_separate' => array(
				'name'=>'make_order_review_separate', 'label'=>__('Show Order Review and Payment in Separate Steps', 'woo-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0, 'onchange'=>'thwmscfOrderReview(this)'
			),
			'show_order_review_right' => array(
				'name'=>'show_order_review_right', 'label'=>__('Show Order Review and Payment on Right side', 'woo-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0
			),
			'title_login' => array(
				'name'=>'title_login', 'label'=>__('Login', 'woo-multistep-checkout'), 'type'=>'text', 'value'=>'Login', 'post_sanitize'=>1,
			),
			'title_billing' => array(
				'name'=>'title_billing', 'label'=>__('Billing Details', 'woo-multistep-checkout'), 'type'=>'text', 'value'=>'Billing details', 'post_sanitize'=>1,
			),
			'title_shipping' => array(
				'name'=>'title_shipping', 'label'=>__('Shipping Details', 'woo-multistep-checkout'), 'type'=>'text', 'value'=>'Shipping details', 'post_sanitize'=>1,
			),
			'title_order_review' => array(
				'name'=>'title_order_review', 'label'=>__('Order Review', 'woo-multistep-checkout'), 'type'=>'text', 'value'=>'Order review', 'post_sanitize'=>1,
			),
			'title_confirm_order' => array(
				'name'=>'title_confirm_order', 'label'=>__('Payment', 'woo-multistep-checkout'), 'type'=>'text', 'value'=>'Payment', 'post_sanitize'=>1,
			),
			'title_layouts' => array('title'=>__('Layouts', 'woo-multistep-checkout'), 'type'=>'separator', 'colspan'=>'6'),
			'tab_align' => array(  
				'name'=>'tab_align', 'label'=>__('Tab Position', 'woo-multistep-checkout'), 'type'=>'select', 'value'=>'center', 'hint_text'=>__('For the vertical layout, this will be treated as text alignment.', 'woo-multistep-checkout'), 'options'=> $tab_postion										
			),
			'tab_panel_bg_color' => array( 
				'name'=>'tab_panel_bg_color', 'label'=>__('Content Background Color', 'woo-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#FFFFFF'
			),
			'step_bg_color' => array( 
				'name'=>'step_bg_color', 'label'=>__('Step Background Color', 'woo-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#F4F4F4'
			),  
			'step_text_color' => array(
				'name'=>'step_text_color', 'label'=>__('Step Text Color', 'woo-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#8B8B8B'
			),
			'step_bg_color_active' => array(       
				'name'=>'step_bg_color_active', 'label'=>__('Step Background Color - Active', 'woo-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#41359F' 
			),
			'step_text_color_active' => array(    
				'name'=>'step_text_color_active', 'label'=>__('Step Text Color - Active', 'woo-multistep-checkout'), 'type'=>'colorpicker', 'value'=>'#FFFFFF'
			),

			'thwmscf_layout' => array( 
				'name'=>'thwmscf_layout', 'label'=>__('Multistep Layout', 'woo-multistep-checkout'), 'type'=>'radio', 'value'=>'thwmscf_horizontal_box', 'options'=> $layout_options, 'onchange'=>'thwmscLayoutChange(this)',
			),
			'next_previuos_button' => array('title'=>__('Button Settings', 'woo-multistep-checkout'), 'type'=>'separator', 'colspan'=>'6'),
			'button_prev_text' => array(
				'name'=>'button_prev_text', 'label'=>__('Previous Button Text', 'woo-multistep-checkout'), 'type'=>'text', 'value'=>'Previous', 'placeholder'=>'',
			),
			'button_next_text' => array(
				'name'=>'button_next_text', 'label'=>__('Next Button Text', 'woo-multistep-checkout'), 'type'=>'text', 'value'=>'Next', 'placeholder'=>'',
			),
			'back_to_cart_button' => array(
				'name'=>'back_to_cart_button', 'label'=>__('Activate Back to Cart Button', 'woo-multistep-checkout'), 'type'=>'checkbox', 'value'=>'yes', 'checked'=>0, 'onchange'=>'thwmscfBackToCart(this)',
			),
			'back_to_cart_button_text' => array(
				'name'=>'back_to_cart_button_text', 'label'=>__('Back to Cart Button Text', 'woo-multistep-checkout'), 'type'=>'text', 'value'=>'Back to cart', 'post_sanitize'=>1,
			),
		);

		return $layout_field;  
	}

	public function get_wmsc_settings(){
		$settings = get_option(self::WMSC_SETTINGS);
		return empty($settings) ? false : $settings;
	}
	
	public function update_settings($settings){
		$result = update_option(self::WMSC_SETTINGS, $settings);
		return $result;
	}

	public function reset_settings(){
		check_admin_referer( 'manage_msc_settings', 'manage_msc_nonce' );

		if(!current_user_can('manage_woocommerce')){
			wp_die();
		}

		delete_option(self::WMSC_SETTINGS);

		return '<div class="updated thwmscf-update-msg"><p>'. __('Settings successfully reset', 'woo-multistep-checkout') .'</p></div>';
	}

	public function render_tabs_and_details(){
		$tabs = $this->get_tabs();
		$tab  = $this->get_current_tab();
		
		echo '<h2 class="nav-tab-wrapper woo-nav-tab-wrapper thwmscf-admin-tab-wrapper">';
		foreach( $tabs as $key => $value ) {
			$active = ( $key == $tab ) ? 'nav-tab-active' : '';
			echo '<a class="nav-tab thwmscf-admin-tab '.esc_attr( $active ).'" href="'. esc_url(admin_url('admin.php?page=woo_multistep_checkout&tab='.$key)) .'"><span class="thwmscf-tab-icon thwmscf-icon-'.esc_attr( $key ).'"></span><label>'.esc_html( $value ).'</label></a>';
		}
		echo '</h2>';
		
		// $this->output_premium_version_notice();		
	}

	public function output_premium_version_notice(){
		?>
        <div id="message" class="wc-connect updated thpladmin-notice">
            <div class="squeezer">
            	<table>
                	<tr>
                    	<td width="70%">
                        	<p><strong><i>WooCommerce Multi-Step Checkout</i></strong> premium version provides more features to customise checkout page step layout & design.</p>
                            <ul>
                            	<li>More layout options.</li>
                            	<li>More styling options.</li>
                            	<li>Option to enable validations at each step.</li>
                            	<li>Option to add custom step and display custom sections & fields created using our WooCommerce Checkout Field Editor plugin.</li>
                            	<li>Supports customization made with other checkout field editors and deeply integrated with our highly rated WooCommerce Checkout Field Editor plugin.</li>
                            </ul>
                        </td>
                        <td>
                        	<a target="_blank" href="https://www.themehigh.com/product/woocommerce-multi-step-checkout/" class="">
                            	<img src="<?php echo esc_url(plugins_url( '../assets/css/upgrade-btn.png', __FILE__ )); ?>" />
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <?php
	}

	public function wmsc_design(){
		$this->render_tabs_and_details();

		echo '<div class="wrap woocommerce"><div class="icon32 icon32-attributes" id="icon-woocommerce"><br /></div>';
		$tab  = $this->get_current_tab();

		if($tab == 'msc_settings'){
			$this->general_settings();
		}if($tab == 'premium_features'){
            $this->render_premium_tab();
        }
		
		echo '</div>';
	}

    function render_premium_tab() {
        $thwmscf_since = get_option('thwmscf_since');
        $now = time();
        $render_time  = apply_filters('thwmscf_get_pro_button_offer', 6 * MONTH_IN_SECONDS);
        $render_time = $thwmscf_since + $render_time;

        if($now > $render_time){
            $url = "https://www.themehigh.com/?edd_action=add_to_cart&download_id=20&cp=lyCDSy_wmsc&utm_source=free&utm_medium=premium_tab&utm_campaign=wmsc_upgrade_link";
        }else{
            $url = "https://www.themehigh.com/product/woocommerce-multi-step-checkout/?utm_source=free&utm_medium=premium_tab&utm_campaign=wmsc_upgrade_link";
        }

        ?>
        <div class="th-nice-box">
            <div class="th-ad-banner">
                <div class="th-ad-content">
                    <div class="th-ad-content-container">
                        <div class="th-ad-content-desc">
                            <p>Unlock more features & create an organized and seamless checkout experience for your customers.</p>  
                        </div>
                        <div class="upgrade-pro-btn-div">
                            <a class="btn-upgrade-pro" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" onclick="this.classList.add('clicked')">Upgrade to Pro</a>
                        </div>  
                    </div>
                </div>
                <div class="th-ad-terms">
                    <div class="th-ad-guarantee">
                        <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/guarantee.svg'); ?>">
                    </div>
                    <p class="th-ad-term-head">30 DAYS MONEY BACK GUARANTEE<span class="th-ad-term-desc">100% Refund, if you are not satisfied with your purchase.</span></p>
                </div>
            </div>
            <div class="th-wrapper-main">
                <div class="th-try-demo">
                    <h3 class="trydemo-heading">Exclusive Perks of Upgrading to the Best</h3>
                    <p class="try-demo-desc">With the premium version of the Multi-Step Checkout plugin, choose the most appropriate layout from the 14 different layouts and customize it to match the standard of world-class WooCommerce stores.</p>
                    <div class="th-pro-btn"><a class="btn-get-pro" onclick="this.classList.add('clicked')" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" >Get Pro</a><a class="btn-try-demo" href="https://flydemos.com/wmsc/" target="_blank" rel="noopener noreferrer" onclick="this.classList.add('clicked')" >Try Demo</a></div>
                    <!-- <img class="vedio" src="" alt="no img">  ADD vedio tutorial-->
                </div>
                <section class="th-wmsc-key-feature">
                    
                    <h3 class="th-feautre-head">Key Features Of Multi-Step Checkout Pro</h3>
                
                    <p class="th-feautre-desc">Some of the advanced features in the Multi-Step Checkout premium plugin are listed below.</p>
                    <div class="th-wmsc-feature-list-ul">
                        <ul class="th-wmsc-feature-list">
                            <li>14 different Multistep Layouts</li>
                            <li>Add New Checkout Steps</li>
                            <li>Add Login, Coupon and Cart Steps to Your Checkout Steps</li>
                            <li>Show/Hide the Next & Previous Buttons</li>
                            <li>Accordion Layout For Mobile Responsiveness</li>
                            <li>Hide Optional Fields as a Link</li>
                            <li>Different Progress Bar Layouts to Indicate the Step Progress</li>
                            <li>Review Step Details Option to Verify the Details Before Checkout</li>
                            <li class="column-break">Compatibility with Popular Plugins</li>
                            <li>Add Image Icons for Checkout Steps</li>
                            <li>AJAX Validation</li>
                            <li>Add Custom Content for Checkout Steps</li>
                            <li>Rearrange Step Position</li>
                            <li>Enable/Disable Steps</li>
                            <li>Customize Completed Checkout Tabs</li>
                            <li>Easy Navigation Between the Steps</li>
                            <li>Customize Place Order Button</li>
                        </ul>   
                    </div>
                    <div class="th-get-pro">
                        <div class="th-get-pro-img">
                            <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/rocket.png'); ?>">
                        </div>
                        <div class='th-wrapper-get-pro'>
                            <div class="th-get-pro-desc">
                                <p class="th-get-pro-desc-head">Switch to the Pro version and be a part of our limitless features<span class="th-get-pro-desc-contnt">Switch to a world of seamless checkout with an ocean of possibilities to customize.</span>
                                </p>
                            </div>
                            <div class="th-get-pro-btn">
                                <a class="btn-upgrade-pro" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" onclick="this.classList.add('clicked')" >Get Pro</a>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="th-star-support">
                    <div class="th-user-star">
                        <p class="th-user-star-desc">2 Million+ Customer Base  </p>
                        <div class="th-user-star-img">
                            <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/star.png'); ?>">
                        </div>
                    </div>
                    <div class="th-pro-support">
                        <div class="th-pro-support-img">
                            <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/support.svg'); ?>">
                        </div>
                        <p class="th-pro-support-desc">Enjoy the <em>premium support</em> experience with our dedicated support team.</p>
                    </div>
                    <div class="th-hor-line"></div>
                </div>
                
                <section class="th-field-types">
                    <h3 class="th-field-types-head">14 different Multistep Layouts</h3>
                    <p class="th-field-types-desc">Following are the different checkout layouts you can choose from the Multi-Step Checkout Pro version.</p>
                    <div class="th-wmsc-field-type-img">
                        <div class="th-fields">
                            <ul class="th-wmsc-field-list">
                                <h4 class="th-field-types-h4" style="margin-left: -1.5rem">Horizontal Layouts</h4>
                                <li>Horizontal Box</li>
                                <li>Horizontal Arrow<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                                <li>Closed Arrow<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                                <li>Time Line</li>
                                <li>Simple Dot Format<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                                <li>Looped Box<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                                <li>Tab Format<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                                <li class="column-break">Custom Separator<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                            
                                <h4 class="th-field-types-h4" style="margin-left: -1.5rem">Vertical Layouts</h4>

                                <li>Vertical Box</li>
                                <li>Vertical Arrow<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                                <li>Vertical Box with Border<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                                <li>Vertical Arrow with Border<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>

                                <h4 class="th-field-types-h4" style="margin-left: -1.5rem">Accordion Layouts</h4>

                                <li>Classic Accordion </li>
                                <li>Accordion with Icons<span class="th-crown"><img src="<?php echo esc_url(THWMSCF_URL .'assets/css/crown.svg'); ?>"></span></li>
                            </ul>
                        </div>
                        <div class="th-fields-img">
                            <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/pr_tab_wmsc_layout.png'); ?>">
                        </div>
                    </div>
                </section>
                <div class="th-fields-section-function">
                    <div class="th-section-function">   
                        <section class="th-display-rule-section" style="margin-right: 6px;">
                            <div class="th-wmsc-pro">
                                <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/pro.svg'); ?>">
                            </div>
                            <div class="th-display-rule-section-head">Button Customization</div>
                            <p class="th-display-rule-section-desc">Customize every aspect of the navigation buttons using the Multi-Step Checkout plugin. Some of the customization options include:</p>
                            <ul class="th-display-section-list">
                                <li>Show/Hide the Next & Previous Buttons from the First & Last Steps</li>
                                <li>Edit Next/Previous Button Text</li>
                                <li>Choose the Button Position & Alignment from the List of Options</li>
                                <li>Edit Font Details Like Font Size, Color, & Font-Color Hover</li>
                                <li>Customize the Background Color, Border Color, Border Style, Padding and Much More</li>
                                <li>Even Customize the Place Order button that Matches your Brand</li>
                            </ul>
                            <div class="display-section-img">
                                <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/pr_tab_button_cstmzn.png'); ?>">
                            </div>
                        </section>
                    </div>
                    <div class="th-fields-function">
                        <section class="th-display-rule-section th-right-box">
                            <div class="th-wmsc-pro">
                                <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/pro.svg'); ?>">
                            </div>
                            <div class="th-display-rule-section-head text-clr-black">Customize Progress Bar</div>
                            <p class="th-display-rule-section-desc text-clr-black">Show the progress bar below the chosen Multi-Step layout and customize them as per your preference.</p>
                            <ul class="th-display-section-list text-clr-black">
                                <h4 class="th-field-types-h4" style="margin-left: -1.5rem">Progress Bar Layouts</h4>
                                <li>Progress Bar Showing Percentage</li>
                                <li>Progress Bar Showing Gradual Progress</li>
                                <li>Progress Bar Showing Step Progression</li>
                            </ul>
                            <div class="display-section-img">
                                <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/pr_tab_progress_bar.png'); ?>">
                            </div>
                        </section>

                        <!-- <section class="th-price-fields">
                            <div class="th-wmsc-pro">
                                <img src="<?php //echo esc_url(THWMSCF_URL .'assets/images/pro.svg'); ?>">
                            </div>
                            <h3 class="th-price-fields-head">Add price fields and choose the price type </h3>
                            <p class="th-price-fields-desc">With the premium version of the Checkout Page Editor Plugin, add an extra price value to the total price by creating a field with price into the checkout form. The available price types that can be added to WooCommerce checkout fields are:</p>
                            <div class="th-price-field-list">
                                <ul class="th-price-list">
                                    <li>Fixed Price</li>
                                    <li>Custom Price</li>
                                    <li>Percentage of Cart Total</li>
                                    <li>Percentage of Subtotal</li>
                                    <li>Percentage of Subtotal excluding tax</li>
                                    <li>Dynamic Price</li>
                                </ul>
                            </div>
                        </section> -->
                    </div>
                </div>
                <div class="th-review-section">
                    <div class="review-image-section">
                        <div class="review-quote-img">
                            <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/reviewquotes.png'); ?>" style="max-width: 100%;">
                        </div>
                    </div>
                    <div id="indicator" class="th-review-navigator" style="text-align:center">
                        <a class="prev" onclick='plusSlides(-1)'></a>
                        <a class="next" onclick='plusSlides(1)'></a>
                        <span class="dot th-review-nav-btn" onclick="currentSlide(1)"></span>
                        <span class="dot th-review-nav-btn" onclick="currentSlide(2)"></span>
                        <span class="dot th-review-nav-btn" onclick="currentSlide(3)"></span>
                        <span class="dot th-review-nav-btn" onclick="currentSlide(4)"></span>
                        <span class="dot th-review-nav-btn" onclick="currentSlide(5)"></span>
                    </div>
                    <div class="th-user-review-section">
                        <div class="th-review-quote">
                        <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/quotes.svg'); ?>">
                        </div>
                        <div class="th-user-review">
                            <h3 class="th-review-heading">Great support</h3>
                            <p class="th-review-content">I used the pro version of the plugin in a project where I needed to add several customizations.The support was really nice and answered every one of my questions with a clear neat response and some snippets that resolve my issue every time I contact them.</p>
                            <p class="th-review-user-name">Dexter0015</p>
                        </div>
                    </div>
                </div>
                <section class="th-faq-tab">
                    <div class="th-faq-desc">
                        <h3>FAQ's</h3>
                        <p class="th-faq-para">Don't worry! Here are the answer to your frequent doubt and questions. If you feel you haven't been answered relevantly, feel free to contact our efficient support team.</p>
                    </div>
                    <div class="th-faq-qstns" >
                        <button class="accordion" onclick="thwmscAccordionexpand(this)">
                            <div class="accordion-qstn">
                                <p>How to upgrade to the premium version of the plugin and how can I apply the license key to activate the pro plugin?</p>
                                <img class="accordion-img" src="<?php echo esc_url(THWMSCF_URL .'assets/images/blck-down-arrow.svg'); ?>">
                                <img class="accordion-img-opn" src="<?php echo esc_url(THWMSCF_URL .'assets/images/blue-down-arrow.svg'); ?>">
                            </div>
                            <div class="panel">
                                <p>Please follow the steps given in the below links to purchase the plugin and activate the license.</p>
                                <p>
                                    <a href="https://www.themehigh.com/docs/download-and-install-your-plugin/" target="_blank" rel="noopener noreferrer">https://www.themehigh.com/docs/download-and-install-your-plugin/</a><br>
                                </p>
                                <p class="th-faq-links">
                                    <a href="https://www.themehigh.com/docs/manage-license/" target="_blank" rel="noopener noreferrer">https://www.themehigh.com/docs/manage-license/</a><br>
                                </p>
                                <p class="th-faq-notes">Note: Please confirm whether all the fields that you had created in the free version have been migrated to the premium version after upgrading. If so you can safely deactivate and delete the free version from your site.</p>
                            </div>
                        </button>                   
                        <button class="accordion" onclick="thwmscAccordionexpand(this)">
                            <div class="accordion-qstn">
                                <p>Do I have to keep both the free version and the pro version after buying the pro version?</p>
                                <img class="accordion-img" src="<?php echo esc_url(THWMSCF_URL .'assets/images/blck-down-arrow.svg'); ?>">
                                <img class="accordion-img-opn" src="<?php echo esc_url(THWMSCF_URL .'assets/images/blue-down-arrow.svg'); ?>">
                            </div>
                            <div class="panel">
                                <p class="th-faq-answer">Please note that free and premium versions are different plugins entirely. So, you can deactivate and remove the free version of the plugin from your website, if you start using the premium version.</p>
                            </div>
                        </button>
                        
                        <button class="accordion" onclick="thwmscAccordionexpand(this)">
                            <div class="accordion-qstn">
                                <p>How to migrate our configuration from the free version to the pro version?</p>
                                <img class="accordion-img" src="<?php echo esc_url(THWMSCF_URL .'assets/images/blck-down-arrow.svg'); ?>">
                                <img class="accordion-img-opn" src="<?php echo esc_url(THWMSCF_URL .'assets/images/blue-down-arrow.svg'); ?>">
                            </div>
                            <div class="panel">
                                <p class="th-faq-answer">At the time when you upgrade the plugin from the free to the premium version, the free plugin settings will get automatically migrated to the premium version.Please confirm whether all the fields that you created in the free version have been migrated to the premium version after upgrading. If so you can safely deactivate and delete the free version from your site.</p>
                            </div>
                        </button>
                        <button class="accordion" onclick="thwmscAccordionexpand(this)">
                            <div class="accordion-qstn">
                                <p>Will I get a refund if the pro plugin doesn't meet my requirements?</p>
                                <img class="accordion-img" src="<?php echo esc_url(THWMSCF_URL .'assets/images/blck-down-arrow.svg'); ?>">
                                <img class="accordion-img-opn" src="<?php echo esc_url(THWMSCF_URL .'assets/images/blue-down-arrow.svg'); ?>">
                            </div>
                            
                            <div class="panel">
                                <p>Please note that as per our refund policy, we will provide a refund within one month from the date of purchase, if you are not satisfied with the product. Please refer to the below link for more details:</p>
                                <p class="th-faq-answer">
                                    <a href="https://www.themehigh.com/refund-policy/" target="_blank" rel="noopener noreferrer">https://www.themehigh.com/refund-policy/</a><br>
                                </p>
                            </div>
                        </button>
                        
                    </div>

                </section>
                <section class="switch-to-pro-tab">
                    <div class="th-switch-to-pro">
                        <h3 class="switch-to-pro-heading">Switch to Pro version and be a part of our limitless features</h3>
                        <p>Switch to pro and unlock access to few of the most sought after features in the checkout page and experience one of a kind convenience like never before.</p>
                        <!-- <div class="th-button-get-pro-link"> -->
                            <a class="button-get-pro" href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" onclick="this.classList.add('clicked')">Get Pro</a> 
                        <!-- </div> -->
                        
                    </div>
                </section>
            </div>
        </div>
        <?php
    }

	function general_settings(){
		if(isset($_POST['save_settings']))
			$this->save_settings();

		if(isset($_POST['reset_settings']))
			echo wp_kses_post( $this->reset_settings() );

		$fields = $this->get_settings_fields();
		$settings = $this->get_settings();
		
		foreach( $fields as $name => &$field ) { 
			if($field['type'] != 'separator'){
				if(is_array($settings) && isset($settings[$name])){
					if($field['type'] === 'checkbox'){
						if(isset($field['value']) && $field['value'] === $settings[$name]){
							$field['checked'] = 1;
						}else{
							$field['checked'] = 0;
						}
					}else{
						$field['value'] = $settings[$name];
					}
				}
			}
		}

		$back_to_cart_button = isset($settings['back_to_cart_button']) && $settings['back_to_cart_button'] ? wptexturize($settings['back_to_cart_button']) : '';
		$enable_login_step = isset($settings['enable_login_step']) && $settings['enable_login_step'] ? wptexturize($settings['enable_login_step']) : '';
		$billing_shipping_together = isset($settings['make_billing_shipping_together']) && $settings['make_billing_shipping_together'] ? wptexturize($settings['make_billing_shipping_together']) : '';

		$order_review_blur_class = isset($settings['make_order_review_separate']) && ($settings['make_order_review_separate'] == 'yes') ? 'wmsc-blur' : '';
		$order_review_separate = isset($settings['make_order_review_separate']) && $settings['make_order_review_separate'] ? wptexturize($settings['make_order_review_separate']) : '';

		$layout = isset($settings['thwmscf_layout']) && $settings['thwmscf_layout'] ? $settings['thwmscf_layout'] : '';

		$cart_text_display = $back_to_cart_button !== 'yes' ? 'display:none' : '';
        $display_login_step = $enable_login_step !== 'yes' ? 'wmsc-blur' : '';
		$step_style = $billing_shipping_together == 'yes' ? 'wmsc-blur' : '';
		$confirm_order_display_style =  $order_review_separate != 'yes' ? 'wmsc-blur' : '';
		$tab_style = $layout == 'thwmscf_time_line_step' || $layout == 'thwmscf_accordion_step' ? 'display:none' : '';
        $title_section_style = 'hide_top_border';

		?>		
		<div style="padding: 0px 35px;">               
		    <form id="wmsc_setting_form" method="post" action="">
		    	<?php wp_nonce_field( 'manage_msc_settings', 'manage_msc_nonce' ); ?>
				<table class="form-table thpladmin-form-table">
                    <?php $this->render_form_section_separator($fields['title_layouts'],$title_section_style); ?>
                    <tbody>
                        <tr>
                            <?php          
                            $this->render_form_field_element($fields['thwmscf_layout'], $this->cell_props_L);
                            ?>
                        </tr>
                        <tr class="display-tab-position" style="<?php echo esc_attr( $tab_style ); ?>;">
                            <?php
                            $cell_props = $this->cell_props_L;
                            // $cell_props['input_width'] = '182px';
                            $this->render_form_field_element($fields['tab_align'], $cell_props);
                            $this->render_form_field_blank(2);
                            ?>
                        </tr>
                        <tr>
                            <?php
                                echo '<th style="font-size:14px; padding: 24px 0px;">'.esc_html__('Display Styles', 'woo-multistep-checkout').'</th>';
                            ?>
                        </tr>
                        <tr>
                            <?php          
                            $this->render_form_field_element($fields['step_bg_color'], $this->cell_props_L);
                            $this->render_form_field_element($fields['step_bg_color_active'], $this->cell_props_L);
                            $this->render_form_field_element($fields['step_text_color'], $this->cell_props_R);
                            ?>
                        </tr>
                        <tr>
                            <?php          
                            $this->render_form_field_element($fields['step_text_color_active'], $this->cell_props_R);
                            $this->render_form_field_element($fields['tab_panel_bg_color'], $this->cell_props_L);
                            $this->render_form_field_blank(1);
                            ?>
                        </tr>
                    </tbody>
                    <tbody>
                        <?php $this->render_form_section_separator($fields['title_display_general_sttng']); ?>
						<tr>
							<?php          
							$this->render_form_field_element($fields['enable_wmsc'], $this->cell_props_L);
							$this->render_form_field_blank(1);
							?>
						</tr> 
						<tr>
							<?php          
							$this->render_form_field_element($fields['enable_step_validation'], $this->cell_props_L);
							$this->render_form_field_blank(1);
							?>
						</tr>
						<tr>
							<?php          
							$this->render_form_field_element($fields['coupon_form_above_payment'], $this->cell_props_L);
							$this->render_form_field_blank(1);
							?>
						</tr>
						<tr>
							<?php          
							$this->render_form_field_element($fields['make_billing_shipping_together'], $this->cell_props_L);
							$this->render_form_field_blank(1);
							?>
						</tr>
						<tr>
							<?php          
							$this->render_form_field_element($fields['make_order_review_separate'], $this->cell_props_L);
							$this->render_form_field_blank(1);
							?>
						</tr>
						<tr id="th-show-review-right" class="<?php echo esc_attr( $order_review_blur_class ); ?>">
							<?php          
							$this->render_form_field_element($fields['show_order_review_right'], $this->cell_props_L);
							$this->render_form_field_blank(1);
							?>
						</tr>
                        <tr>
                            <?php          
                            $this->render_form_field_element($fields['enable_login_step'], $this->cell_props_R);
                            $this->render_form_field_blank(1);
                            ?>
                        </tr>
						<?php $this->render_form_section_separator($fields['title_display_texts']); ?>
						<tr>
							<?php          
							$this->render_form_field_element($fields['title_billing'], $this->cell_props_L);

                            $cell_property = $this->cell_props_L;
                            $cell_property['label_cell_props'] = 'style="width: 23%;" class="titledesc '.$step_style.'" scope="row"';

                            $this->render_form_field_element($fields['title_shipping'], $cell_property);
                            $this->render_form_field_element($fields['title_order_review'], $this->cell_props_L);

							?>
						</tr>
                        <tr>
							<?php          
                            $cell_property['label_cell_props'] = 'style="width: 23%;" class="titledesc '.$confirm_order_display_style.'" scope="row"';
							$this->render_form_field_element($fields['title_confirm_order'], $cell_property);

                            $cell_property['label_cell_props'] = 'style="width: 23%;" class="titledesc '.$display_login_step.'" scope="row"';

                            $this->render_form_field_element($fields['title_login'], $cell_property);
							$this->render_form_field_blank(1);
							?>
						</tr>
						<?php $this->render_form_section_separator($fields['next_previuos_button']); ?>
						<tr>
							<?php          
							$this->render_form_field_element($fields['button_prev_text'], $this->cell_props_L);
							$this->render_form_field_element($fields['button_next_text'], $this->cell_props_R);
                            $this->render_form_field_blank(1);
							?>
						</tr>
						<tr>
							<?php          
							$this->render_form_field_element($fields['back_to_cart_button'], $this->cell_props_L);
							$this->render_form_field_blank(1);
							?>
						</tr>
						<tr class="back-to-cart-show" style="<?php echo esc_attr( $cart_text_display ); ?>">
							<?php          
							$this->render_form_field_element($fields['back_to_cart_button_text'], $this->cell_props_L);
							$this->render_form_field_blank(2);
							?>
						</tr>
                    </tbody>
                </table>
				                
                <p class="submit thwmscf-form-actions">
					<input type="submit" name="reset_settings" class="thwmscf-admin-button thwmscf-reset-btn" value="Reset to default"
					onclick="return confirm('Are you sure you want to reset to default settings? all your changes will be deleted.');">
                    <input type="submit" name="save_settings" class="thwmscf-admin-button thwmscf-save-btn" value="Save changes">
            	</p>
            </form>
    	</div>

	<?php }

	public function hide_checkout_coupon_form(){
		echo '<style>.woocommerce-form-coupon-toggle {display:none;}</style>';
	}

	public function woocommerce_checkout_coupon_form_custom(){
		?>
		<div class="checkout-coupon-toggle">
			<div class="woocommerce-info">
            <?php 
            echo wp_kses_post( apply_filters( 'woocommerce_checkout_coupon_message', esc_html__( 'Have a coupon?', 'woocommerce' ) . ' <a href="#" class="show-coupon">' . esc_html__( 'Click here to enter your code', 'woocommerce' ) . '</a>' ) );
            ?>
    		</div>
    	</div>
		<div class="coupon-form" style="margin-bottom:20px;" style="display:none!important;">
	        <p><?php esc_html_e("If you have a coupon code, please apply it below.", "woocommerce") ?></p>
	        <p class="form-row form-row-first woocommerce-validated">
	            <input type="text" name="coupon_code" class="input-text" placeholder="<?php esc_html_e("Coupon code", "woocommerce") ?>" id="coupon_code" value="">
	        </p>
	        <p class="form-row form-row-last">
	            <button type="button" class="button" name="apply_coupon" value="<?php echo esc_attr("Apply coupon") ?>"><?php esc_html_e("Apply coupon", "woocommerce") ?></button>
	        </p>
	        <div class="clear"></div>
	    </div>
	    <?php
	}

	public function thwmsc_make_order_review_on_right($steps){
		
		if(array_key_exists('show_order_review_right', $steps)){
			unset($steps['show_order_review_right']);
		}
		return $steps;
	}


	// Adding the Order review section in the right side
	public function add_review_order_on_right_side(){

        $display_prop = $this->get_settings();
        $order_review_right = isset($display_prop['show_order_review_right']) && $display_prop['show_order_review_right'] == 'yes' ? true : false;
		$coupon_form_above_payment =  isset($display_prop['coupon_form_above_payment']) ? $display_prop['coupon_form_above_payment'] : false;
		?>
		<div class="thwmscf-order-review-right">
			<?php do_action( 'woocommerce_checkout_before_order_review' ); ?>

			<div id="order_review" class="woocommerce-checkout-review-order">
				<?php //do_action( 'woocommerce_checkout_order_review' ); ?>
				<?php do_action( 'thwmscf_woocommerce_checkout_review_order' ); ?>

				<?php if ($order_review_right && $coupon_form_above_payment) {
                	do_action('thwmscf_woocommerce_review_order_before_payment');
				} ?>

                <?Php remove_action( 'woocommerce_checkout_order_review', 'woocommerce_order_review', 10 ); ?>
				<?php do_action( 'woocommerce_checkout_order_review' ); ?>

			</div>
			<?php do_action( 'woocommerce_checkout_after_order_review' ); ?>
		</div>
		<?php 
	}
	
	public function save_settings(){
		check_admin_referer( 'manage_msc_settings', 'manage_msc_nonce' );

		if(!current_user_can('manage_woocommerce')){
			wp_die();
		}

		$settings = array();
		$settings_fields = $this->get_settings_fields();
		
		foreach( $settings_fields as $name => $field ) {
			$type = $field['type'];
			if($type != 'separator'){
				$value = '';
				
				if($field['type'] === 'checkbox'){
					$value = !empty( $_POST['i_'.$name] ) ? 'yes' : '';

				}else if($field['type'] === 'multiselect_grouped'){
					$value = !empty( $_POST['i_'.$name] ) ? wc_clean(wp_unslash($_POST['i_'.$name])) : '';
					$value = is_array($value) ? implode(',', $value) : $value;

				}else if($field['type'] === 'textarea'){
					$value = !empty( $_POST['i_'.$name] ) ? sanitize_textarea_field(wp_unslash($_POST['i_'.$name])) : '';

				}else{
					if(isset($field['post_sanitize']) && $field['post_sanitize']){
						$value = !empty( $_POST['i_'.$name] ) ? wp_unslash(wp_filter_post_kses($_POST['i_'.$name])) : '';
					}else{
						$value = !empty( $_POST['i_'.$name] ) ? wc_clean(wp_unslash($_POST['i_'.$name])) : '';
					}
				}
				
				$settings[$name] = $value;
			}
		}
				
		$result = $this->update_settings($settings);
		if ($result == true) {
			echo '<div class="updated thwmscf-update-msg"><p>'. esc_html__('Your changes were saved.', 'woo-multistep-checkout') .'</p></div>';
		} else {
			echo '<div class="error thwmscf-update-msg"><p>'. esc_html__('Your changes were not saved due to an error (or you made none!).', 'woo-multistep-checkout') .'</p></div>';
		}
	}

	public function render_form_section_separator($props,$section_title='',$atts=array()){
        $section_title_style = isset($section_title) && $section_title === 'hide_top_border' ? 'border: none; padding-top: 3px;' : '';
		?>
		<tr valign="top"><td colspan="<?php echo esc_attr( $props['colspan'] ); ?>" style="height:10px;"></td></tr>
		<tr valign="top"><td colspan="<?php echo esc_attr( $props['colspan'] ); ?>" style="<?php echo esc_attr( $section_title_style ); ?>" class="thpladmin-form-section-title" ><?php echo esc_html( $props['title'] ); ?></td></tr>
		<tr valign="top"><td colspan="<?php echo esc_attr( $props['colspan'] ); ?>" style="height:0px;"></td></tr>
		<?php
	}

	public function render_form_field_element($field, $atts=array(), $render_cell=true){
		if($field && is_array($field)){
			$ftype = isset($field['type']) ? $field['type'] : 'text';
            $field_html = '';
			
			if($ftype == 'checkbox'){
				$atts['input_cell_props'] = ' style="width: 25%;" class="forminp thwmscf_checkbox"';
				$field_html .= $this->render_form_field_element_checkbox($field, $atts, $render_cell);
                if(!(isset($atts['render_label_cell']) && $atts['render_label_cell'])){   
                    $flabel = '&nbsp;';  
                }
			}
		
			$args = shortcode_atts( array(   
				'label_cell_props' => '',
				'input_cell_props' => '',
				'label_cell_th' => false,
				'input_width' => '',
				'rows' => '5',
				'cols' => '100',
				'input_name_prefix' => 'i_'
			), $atts );
			
			$fname  = $args['input_name_prefix'].$field['name'];						
			$flabel = __($field['label'], 'woo-multistep-checkout');
			$fvalue = isset($field['value']) ? $field['value'] : '';
			
			if($ftype == 'multiselect' && is_array($fvalue)){  
				$fvalue = !empty($fvalue) ? implode(',', $fvalue) : $fvalue;
			}
			/*if($ftype == 'multiselect' || $ftype == 'multiselect_grouped'){
				$fvalue = !empty($fvalue) ? explode(',', $fvalue) : $fvalue;
			}*/
						
			$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
			$field_props  = 'name="'. $fname .'" value="'. esc_attr($fvalue) .'" style="'. $input_width .'"';
			$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';

			$tooltip   = isset($field['hint_text']) && !empty($field['hint_text']) ? $field['hint_text'] : '';
			
			$required_html = ( isset($field['required']) && $field['required'] ) ? '<abbr class="required" title="required">*</abbr>' : '';
			
			if(isset($field['onchange']) && !empty($field['onchange'])){
				$field_props .= ' onchange="'.$field['onchange'].'"';
			}
			
			if($ftype == 'text'){
				$field_html = '<input type="text" class="thwmscf-input-text" '. $field_props .' />';
				
			}else if($ftype == 'number'){
				$field_html = '<input type="number" class="thwmsc_number" '. $field_props .' />';
				
			}else if($ftype == 'textarea'){
				$field_props  = 'name="'. $fname .'" style=""';
				$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';
				$field_html = '<textarea '. $field_props .' rows="'.$args['rows'].'" cols="'.$args['cols'].'" >'. esc_textarea($fvalue) .'</textarea>';
				
			}else if($ftype == 'select'){
				$field_props .= 'class="thwmscf_select"';
				$field_html = '<select class="thwmscf-input-select" '. $field_props .' >';

				foreach($field['options'] as $value => $label){
					$selected = $value == $fvalue ? 'selected' : '';
					$field_html .= '<option value="'. trim($value) .'" '.$selected.'>'. __($label, 'woo-multistep-checkout') .'</option>';
				}

				$field_html .= '</select>';
				
			}else if($ftype == 'colorpicker'){
				$field_html = $this->render_form_field_element_colorpicker($field, $args);
			}else if($ftype == 'radio'){
				$args['input_cell_props'] = 'colspan="3" style="width: 100%;" class="forminp thwmscf_layout_wrap"';
				$field_html = $this->render_form_field_element_radio($field, $atts);
			}
			
			$label_cell_props = !empty($args['label_cell_props']) ? ' '.$args['label_cell_props'] : '';
			$input_cell_props = !empty($args['input_cell_props']) ? ' '.$args['input_cell_props'] : '';
            $cell_html = '';

            if($field['name'] != 'thwmscf_layout') {    
                $style      = $field['type'] === 'checkbox' ? 'style="width: 33.3%;"'   : 'style="width: 33.3%;"';  
                $cell_html .= '<td '.$style.' '.$label_cell_props.' >';
                $cell_html .= '<label class="thpladmin-td-label">' . $flabel . '</label>';
                $cell_html .= $required_html;

                if(isset($field['sub_label']) && !empty($field['sub_label'])){
                    $sub_label = $this->_ewcfe($field['sub_label']);
                    $cell_html .= '<br/><span class="thpladmin-subtitle">'.$sub_label.'</span>';
                }

                if ($field['type'] != 'checkbox') {
                    $cell_html .= $this->render_tooltip_new_ui($tooltip, true);
                    $cell_html .= '<span class="thpladmin-td-input" '.$input_cell_props.'>'.$field_html.'</span>';
                }
                
                $cell_html .= '</td>';
            }

            if ($field['name'] === 'thwmscf_layout' || $field['type'] === 'checkbox') {
                $cell_html .= $field['name'] === 'thwmscf_layout' ? '<td '.$input_cell_props.'style="width: 100%;" >' : '<td '.$input_cell_props.'style="width: 33.3%;" >';

                if($tooltip){
                    $cell_html .= $this->render_tooltip_new_ui($tooltip, true);
                }elseif(!$tooltip && !($field['name'] === 'thwmscf_layout')){
                    $cell_html .= '<span class="thpladmin_tooltip thwmscf-tooltip" ></span>';
                }
                
                $cell_html .= $field_html;
                $cell_html .= '</td>';
            }

            $field_html = $cell_html;
            echo $field_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	}

	public function render_form_fragment_tooltip($tooltip = false, $return = false){
		$tooltip_html = '';
		
		if($tooltip){
			$tooltip_html .= '<td style="width: 26px; padding:0px;">';
			$tooltip_html .= '<a href="javascript:void(0)" title="'. esc_attr( $tooltip ) .'" class="thpladmin_tooltip"><img src="'. esc_url( THWMSCF_ASSETS_URL . 'images/help.svg' ) .'" title=""/></a>';
			$tooltip_html .= '</td>';
		}else{
			$tooltip_html .= '<td style="width: 26px; padding:0px;"></td>';
		}

		if($return){
			return $tooltip_html;
		}else{
			echo wp_kses_post( $tooltip_html );
		}
	}

    public function render_tooltip_new_ui($tooltip = false, $return = false){
        $tooltip_html = '';

        if($tooltip){
            $tooltip_html .= '<a href="javascript:void(0)" title="'. esc_attr( $tooltip ) .'" class="thpladmin_tooltip thwmscf-tooltip"><img src="'. esc_url( THWMSCF_ASSETS_URL . 'images/help.svg' ) .'" title=""/></a>';
        }

        if($return){
            return $tooltip_html;
        }else{
            echo wp_kses_post( $tooltip_html );
        }
    }

	public function render_form_field_element_checkbox($field, $atts=array(), $render_cell=false){
		$args = shortcode_atts( array( 'cell_props'  => '', 'input_props' => '', 'label_props' => '', 'name_prefix' => 'i_', 'id_prefix' => 'a_f', 'input_cell_props' => ''), $atts );
		
		$fid    = $args['id_prefix'].$field['name'];
		$fname  = $args['name_prefix'].$field['name'];
		$fvalue = isset($field['value']) ? $field['value'] : '';
		$flabel = __($field['label'], 'woo-multistep-checkout');
		
		$field_props  = 'id="'. $fid .'" name="'. $fname .'"';
		$field_props .= !empty($fvalue) ? ' value="'. esc_attr($fvalue) .'"' : '';
		$field_props .= $field['checked'] ? ' checked' : '';
		$field_props .= $args['input_props'];
		$field_props .= isset($field['onchange']) && !empty($field['onchange']) ? ' onchange="'.$field['onchange'].'"' : '';

		$input_cell_props = isset($args['input_cell_props']) ? $args['input_cell_props'] : '';
		$field_html = '';

		$tooltip = isset($field['hint_text']) && !empty($field['hint_text']) ? $field['hint_text'] : '';
		
		if($render_cell === 'inline'){
			$field_html = '<td colspan="3"><input type="checkbox" '. $field_props .' /><label for="'. $fid .'" '. $args['label_props'] .' > '. $flabel .'</label></td>';
        }else {
            $field_html  = '<input type="checkbox" id="'. $fid .'" '. $field_props .' />';
            $field_html .= '<label for="'. $fid .'"> '. $flabel .'</label>';
        }
		// }else{
		// 	$field_html = '<td><label for="'. $fid .'" '. $args['label_props'] .' > '. $flabel .'</label></td>';
		// 	// $field_html .= '<td style="width: 26px; padding:0px;"></td>';
		// 	$field_html .= $this->render_form_fragment_tooltip($tooltip, true);
		// 	$field_html .= '<td '. $input_cell_props .'><input type="checkbox" '. $field_props .' /><label for="' . $fid . '"class="thwmscf-checkbox-span"></label></td>';
		// }

        if(!$render_cell && $args['render_input_cell']){
            return '<td '. $args['cell_props'] .' >'. $field_html .'</td>';
        }else{
            return $field_html;
        }
	}

	private function render_form_field_element_radio($field, $atts = array()){
		$field_html = '';
		$args = shortcode_atts( array(
			'label_props' => '',
			'cell_props'  => 3,
			'render_input_cell' => false,
			'render_label_cell' => false,
			'input_cell_props'
		), $atts );

		// $cell_props_rd = $this->cell_props_CB;
		// $cell_props_rd['input_cell_props'] = 'class="forminp layout_wrap" colspan="4"';

		$atts = array(
			'input_width' => 'auto',
		);

		if($field && is_array($field)){
			
			$fvalue = isset($field['value']) ? $field['value'] : '';
			// $field_props = $this->prepare_form_field_props($field, $atts);			

			foreach($field['options'] as $value => $label){
				$checked ='';
				$img_layout = '';

				//$flabel = isset($label) && !empty($label) ? THWMSC_i18n::t($label) : '';
				$flabel = isset($label['name']) && !empty($label['name']) ? sprintf(__('%s', 'woocommerce-multistep-checkout'), $label['name']) : '';
				$onchange = ( isset($field['onchange']) && !empty($field['onchange']) ) ? ' onchange="'.$field['onchange'].'"' : '';
				$img_layout = isset($label['layout_image']) && !empty($label['layout_image']) ? $label['layout_image'] : '';

				$checked = $value === $fvalue ? 'checked' : '';				
				$field_html .='<label for="'. $value .'" '. $args['label_props'] .' > ';				

				$field_html .= '<input type="radio" name="i_' . $field['name'] . '" id="'. $value . '" value="'. trim($value) .'" ' . $checked . $onchange . '>';
				//$field_html .= '<span class ="layout-icon ' . $value . '"></span>';
				$field_html .= '<img class="thwmscf-layout" src= "'. THWMSCF_ASSETS_URL . 'images/' . $img_layout.'">';
				$field_html .= $flabel.'</label>';
			}
		}
		return $field_html;
	}

	private function render_form_field_element_colorpicker($field, $atts = array()){
		$field_html = '';
		if($field && is_array($field)){
			$args = shortcode_atts( array(
				'input_width' => '',
				'input_name_prefix' => 'i_'
			), $atts );
			
			$fname  = $args['input_name_prefix'].$field['name'];
			$fvalue = isset($field['value']) ? $field['value'] : '';
			
			$input_width  = $args['input_width'] ? 'width:'.$args['input_width'].';' : '';
			$field_props  = 'name="'. $fname .'" value="'. esc_attr($fvalue) .'" style="'. $input_width .'"';
			$field_props .= ( isset($field['placeholder']) && !empty($field['placeholder']) ) ? ' placeholder="'.$field['placeholder'].'"' : '';

			$field_html  = '<div class="thwmscf-clrpicker-wrapper">';
			$field_html .= '<span class="thwmscf-colorpreview thpladmin-colorpickpreview '.$field['name'].'_preview" style=""></span>';
            $field_html .= '<input type="text" '. $field_props .' class="thwmscf-colorpicker thpladmin-colorpick"/> </div>';
		}
		return $field_html;
	}
	
	public function init() {
		if(!is_admin() || (defined( 'DOING_AJAX' ) && DOING_AJAX)){
			if(is_array($this->settings) && isset($this->settings['enable_wmsc']) && $this->settings['enable_wmsc'] == 'yes'){
				$this->frontend_design();
			}
		}
	}

	public function frontend_design(){
		$thwmscf_settings = get_option('THWMSC_SETTINGS');
		$enable_login_step = isset($thwmscf_settings['enable_login_step']) ? $thwmscf_settings['enable_login_step'] : false;

		add_action( 'wp_enqueue_scripts', array( $this, 'thwmsc_frontend_scripts' ) );	
	    add_filter( 'woocommerce_locate_template', array( $this, 'wmsc_multistep_template' ), 10, 3 );
	    if($enable_login_step){
	        remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 );
			add_action('thwmscf_before_checkout_form', 'woocommerce_checkout_login_form');
		}

		$current_theme = wp_get_theme();
		$theme_template = $current_theme->get_template();

		if($theme_template === 'astra'){
			$astra_priority = apply_filters('thwmscf_astra_theme_priority', 20);
			// add_filter('astra_woo_shop_product_structure_override', '__return_true');
			
			add_action( 'wp', array($this, 'astra_remove_shipping_from_billing'));
			add_action( 'woocommerce_checkout_shipping', array( WC()->checkout(), 'checkout_form_shipping' ), $astra_priority);
		}
	}

	public function astra_remove_shipping_from_billing(){
		remove_action('woocommerce_checkout_billing', array(WC()->checkout(), 'checkout_form_shipping'));
	}
	
	public function before_checkout_form(){
		if(!is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder')){
			echo '<div class="thwmscf-tab-panel" id="thwmscf-tab-panel-0">';
			do_action( 'woocommerce_checkout_login_form' );
			echo '</div>';
		}
	}

	public function thwmsc_frontend_scripts(){
		if(!is_checkout()){
			return;
		}
		
		$in_footer = apply_filters( 'thwmscf_enqueue_script_in_footer', true );

        wp_register_style( 'thwmscf-checkout-css', THWMSCF_ASSETS_URL . 'css/thwmscf-frontend.css', array(), THWMSCF_VERSION );
        wp_register_script('thwmscf-frontend-js', THWMSCF_ASSETS_URL.'js/thwmscf-frontend.js', array(), THWMSCF_VERSION, $in_footer);  

        wp_enqueue_style('thwmscf-checkout-css');    

        $display_prop = $this->get_settings();

        if($display_prop){      
			$tab_panel_style = '';
			$tab_style = '';
			$tab_style_active = '';
			
			$tab_align = isset($display_prop['tab_align']) && $display_prop['tab_align'] ? 'text-align:'.$display_prop['tab_align'].';' : '';
			
			if(isset($display_prop['tab_panel_bg_color']) && $display_prop['tab_panel_bg_color']){
				$tab_panel_style = 'background:'.$display_prop['tab_panel_bg_color'].' !important;';
			}
			
			if(isset($display_prop['step_bg_color']) && $display_prop['step_bg_color']){
				$tab_style = 'background:'.$display_prop['step_bg_color'].' !important;';
			}
			if(isset($display_prop['step_text_color']) && $display_prop['step_text_color']){
				$tab_style .= $tab_style ? ' color:'.$display_prop['step_text_color'].'' : 'color:'.$display_prop['step_text_color'].'';
				$tab_style .= ' !important';
			}
			
			if(isset($display_prop['step_bg_color_active']) && $display_prop['step_bg_color_active']){
				$tab_style_active = 'background:'.$display_prop['step_bg_color_active'].' !important;';
			}
			if(isset($display_prop['step_text_color_active']) && $display_prop['step_text_color_active']){
				$tab_style_active .= $tab_style_active ? ' color:'.$display_prop['step_text_color_active'].'' : 'color:'.$display_prop['step_text_color_active'].'';
				$tab_style_active .= ' !important';
			}

            $plugin_style = "
                    ul.thwmscf-tabs{ $tab_align }
                    li.thwmscf-tab a{ $tab_style }
                    li.thwmscf-tab a.active { $tab_style_active }
					.thwmscf-tab-panels,
					.thwmscf-tab-panel,
					.thwmscf-wrapper #order_review,
					.thwmscf-wrapper table.shop_table,
					.thwmscf-wrapper table.shop_table th,
					.thwmscf-wrapper table.shop_table td,
					.thwmscf-wrapper #payment,
					.thwmscf-wrapper #payment ul.payment_methods,
					.thwmscf-wrapper #payment div.payment_box,
					.thwmscf-wrapper #payment div.form-row{ $tab_panel_style }";

			if(isset($display_prop['thwmscf_layout']) && $display_prop['thwmscf_layout'] == 'thwmscf_time_line_step') {
		        $enable_login_step = isset($display_prop['enable_login_step']) ? $display_prop['enable_login_step'] : false;
		        $billing_shipping_together =  isset($display_prop['make_billing_shipping_together']) ? $display_prop['make_billing_shipping_together'] : false;
		        $order_review_separate =  isset($display_prop['make_order_review_separate']) ? $display_prop['make_order_review_separate'] : false;
		        $order_review_right = !$order_review_separate && isset($display_prop['show_order_review_right']) && $display_prop['show_order_review_right'] == 'yes' ? true : false;
		        $line_border_color = isset($display_prop['step_bg_color']) && $display_prop['step_bg_color'] ? 'border-top :4px solid '.$display_prop['step_bg_color'].';' : '';
		        $line_border_color_active = isset($display_prop['step_bg_color_active']) && $display_prop['step_bg_color_active'] ? 'border-top :4px solid '.$display_prop['step_bg_color_active'].';' : '';

		        if ($order_review_separate && $billing_shipping_together) {
		        	$step_count = $enable_login_step && !is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder') ? 4 : 3;
		        }if ($billing_shipping_together && !$order_review_separate && $order_review_right) {
		        	$step_count = $enable_login_step && !is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder') ? 2 : 1;
		        }if ($billing_shipping_together && !$order_review_separate && !$order_review_right) {
		        	$step_count = $enable_login_step && !is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder') ? 3 : 2;
		        }if (!$billing_shipping_together && $order_review_separate) {
		        	$step_count = $enable_login_step && !is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder') ? 5 : 4;
		        }if (!$billing_shipping_together && !$order_review_separate && $order_review_right) {
		        	$step_count = $enable_login_step && !is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder') ? 3 : 2;
		        }if (!$billing_shipping_together && !$order_review_separate && !$order_review_right) {
		        	$step_count = $enable_login_step && !is_user_logged_in() && 'yes' === get_option( 'woocommerce_enable_checkout_login_reminder') ? 4 : 3;
		        }
		        $width_time_line = 'width:'. 100/$step_count .'%';

		        $plugin_style .= "
		        	.thwmscf_time_line_step ul.thwmscf-tabs li{ $width_time_line }
					.thwmscf_time_line_step ul.thwmscf-tabs li a {  $line_border_color }
		        	.thwmscf_time_line_step ul.thwmscf-tabs li a.active { $line_border_color_active }";
			}

			if(isset($display_prop['thwmscf_layout']) && $display_prop['thwmscf_layout'] == 'thwmscf_accordion_step') {
				$accordion_style = 'display:block;';
				$plugin_style .= "
					.thwmscf-accordion-label{ $accordion_style }
					.thwmscf-accordion-label.active{ $tab_style_active }
					.thwmscf-accordion-label{ $tab_style }
					.thwmscf_accordion_step .thwmscf-content{ $tab_panel_style }
				";
			}

		    $order_review_separate =  isset($display_prop['make_order_review_separate']) ? $display_prop['make_order_review_separate'] : false;
			$order_review_right = !$order_review_separate && isset($display_prop['show_order_review_right']) && $display_prop['show_order_review_right'] == 'yes' ? true : false;
		    $coupon_form_above_payment =  isset($display_prop['coupon_form_above_payment']) ? $display_prop['coupon_form_above_payment'] : false;


			if($order_review_right && !wp_is_mobile()){
				$plugin_style .= ".thwmscf-tabs { width: 100%; }
				.thwmscf-tab-panels { position: relative; }
				.thwmscf-tab-panel { float: left;width: 61%; }
				div#order_review { width: 100%!important; } 
				.thwmscf-wrapper .thwmsc-buttons { text-align: left; }
				.thwmscf-order-review-right { width: 38%;float: right; }
				.thwmscf_accordion_step .thwmscf-content { width: 61%; margin-right: 7px; }
				";
			}
		    
            wp_add_inline_style( 'thwmscf-checkout-css', $plugin_style );  
        }        

        if(is_array($this->settings) && isset($this->settings['enable_wmsc']) && $this->settings['enable_wmsc'] == 'yes'){
       		wp_enqueue_script('thwmscf-frontend-js');
			$enable_validation = isset($this->settings['enable_step_validation']) ? $this->settings['enable_step_validation'] : true;
			$validation_msg = __('Invalid or data missing in the required field(s)', 'woo-multistep-checkout');
			
       		$script_var = array(
	    	    'enable_validation' => apply_filters('thwmscf_enable_step_validation', $enable_validation),
	    		'validation_msg' => apply_filters('thwmscf_validation_error', $validation_msg),
  	 			'coupon_form_above_payment' => apply_filters('thwmsc_coupon_form_above_payment', $coupon_form_above_payment),
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'ajax_nonce' => wp_create_nonce( 'ajax-nonce' ),
                'validation_type' => apply_filters( 'thwmscf_step_validation_type', 'js' ),

	    	);
	    	wp_localize_script('thwmscf-frontend-js', 'thwmscf_script_var', $script_var);
	    }

	} 

	public function wmsc_multistep_template( $template, $template_name, $template_path ){
        if('checkout/form-checkout.php' == $template_name ){         
        	if(is_array($this->settings) && isset($this->settings['enable_wmsc']) && $this->settings['enable_wmsc'] == 'yes'){  	
        		$template = THWMSCF_TEMPLATE_PATH . 'checkout/form-checkout.php';   
        	}
        }
        return $template;
    }

    public function thwmsc_notice_actions(){

		if( !(isset($_GET['thwmsc_remind']) || isset($_GET['thwmsc_dissmis']) || isset($_GET['thwmsc_reviewed'])) ) {
			return;
		}

		$nonse = isset($_GET['thwmsc_review_nonce']) ? $_GET['thwmsc_review_nonce'] : false;
		if(!wp_verify_nonce($nonse, 'thwmscf_notice_security')){
			die();
		}
		$now = time();
		$thwmsc_remind = isset($_GET['thwmsc_remind']) ? sanitize_text_field( wp_unslash($_GET['thwmsc_remind'])) : false;
		if($thwmsc_remind){
			update_user_meta( get_current_user_id(), 'thwmsc_review_skipped', true );
			update_user_meta( get_current_user_id(), 'thwmsc_review_skipped_time', $now );
		}

		$thwmsc_dissmis = isset($_GET['thwmsc_dissmis']) ? sanitize_text_field( wp_unslash($_GET['thwmsc_dissmis'])) : false;
		if($thwmsc_dissmis){
			update_user_meta( get_current_user_id(), 'thwmsc_review_dismissed', true );
			update_user_meta( get_current_user_id(), 'thwmsc_review_dismissed_time', $now );
		}

		$thwmsc_reviewed = isset($_GET['thwmsc_reviewed']) ? sanitize_text_field( wp_unslash($_GET['thwmsc_reviewed'])) : false;
		if($thwmsc_reviewed){
			update_user_meta( get_current_user_id(), 'thwmsc_reviewed', true );
			update_user_meta( get_current_user_id(), 'thwmsc_reviewed_time', $now );
		}
	}

	public function output_review_request_link(){

		if(!apply_filters('thwmscf_show_dismissable_admin_notice', true)){
			return;
		}
		if ( !current_user_can( 'manage_options' ) ) {
           return;
        }

		$current_screen = get_current_screen();
		if($current_screen->id !== 'woocommerce_page_woo_multistep_checkout'){
			return;
		}

		$thwmsc_reviewed = get_user_meta( get_current_user_id(), 'thwmsc_reviewed', true );
		if($thwmsc_reviewed){
			return;
		}

		$now = time();
		// $dismiss_life  = apply_filters('thwmscf_dismissed_review_request_notice_lifespan', 3 * MONTH_IN_SECONDS);
		// $reminder_life = apply_filters('thwmscf_skip_review_request_notice_lifespan', 1 * DAY_IN_SECONDS);

		$dismiss_life  = apply_filters('thwmscf_dismissed_review_request_notice_lifespan', 6 * MONTH_IN_SECONDS);
		$reminder_life = apply_filters('thwmscf_skip_review_request_notice_lifespan', 7 * DAY_IN_SECONDS);

		$is_dismissed   = get_user_meta( get_current_user_id(), 'thwmsc_review_dismissed', true );
		$dismisal_time  = get_user_meta( get_current_user_id(), 'thwmsc_review_dismissed_time', true );
		$dismisal_time  = $dismisal_time ? $dismisal_time : 0;
		$dismissed_time = $now - $dismisal_time;

		if( $is_dismissed && ($dismissed_time < $dismiss_life) ){
			return;
		}

		$is_skipped = get_user_meta( get_current_user_id(), 'thwmsc_review_skipped', true );
		$skipping_time = get_user_meta( get_current_user_id(), 'thwmsc_review_skipped_time', true );
		$skipping_time = $skipping_time ? $skipping_time : 0;
		$remind_time = $now - $skipping_time;

		if($is_skipped && ($remind_time < $reminder_life) ){
			return;
		}

		$thwmscf_since = get_option('thwmscf_since');
		if(!$thwmscf_since){
			$now = time();
			update_option('thwmscf_since', $now, 'no' );
		}

		// $this->render_review_request_notice();

		$thwmscf_since = $thwmscf_since ? $thwmscf_since : $now;
        $render_time  = apply_filters('thwmscf_show_review_banner_render_time' , 3 * DAY_IN_SECONDS);
        $render_time  = $thwmscf_since + $render_time;
        if($now > $render_time ){
            $this->render_review_request_notice();
        }

	}


	private function render_review_request_notice(){
		
		// $admin_url  = 'admin.php?page=woo_multistep_checkout';
		$remind_url   = add_query_arg(array('thwmsc_remind' => true , 'thwmsc_review_nonce' => wp_create_nonce('thwmscf_notice_security')));
        $dismiss_url  = add_query_arg(array('thwmsc_dissmis' => true, 'thwmsc_review_nonce' => wp_create_nonce( 'thwmscf_notice_security')));
        $reviewed_url = add_query_arg(array('thwmsc_reviewed' => true , 'thwmsc_review_nonce' => wp_create_nonce( 'thwmscf_notice_security')));

		// $remind_url  = $admin_url . '&thwmsc_remind=true&thwmsc_review_nonce=' . wp_create_nonce( 'thwmscf_notice_security');
		// $dismiss_url = $admin_url . '&thwmsc_dissmis=true&thwmsc_review_nonce=' . wp_create_nonce( 'thwmscf_notice_security');
		// $reviewed_url= $admin_url . '&thwmsc_reviewed=true&thwmsc_review_nonce=' . wp_create_nonce( 'thwmscf_notice_security');
		?>

		<div class="notice thwmsc-notice is-dismissible thwmsc-review-wrapper" data-nonce="<?php echo esc_attr( wp_create_nonce( 'thwmscf_notice_security') ); ?>">
			<div class="thwmsc-review-image">
				<img src="<?php echo esc_url(THWMSCF_URL .'assets/images/review-left.svg'); ?>" alt="themehigh">
			</div>
			<div class="thwmsc-review-content">
				<h3><?php esc_html_e('We have a quick favour to ask ', 'woo-multistep-checkout'); ?>&#128519;</h3>
				<p style="margin-bottom: -11px"><?php esc_html_e("We're excited to launch a fresh new look for our Multi-step Checkout plugin! We've put in a lot of time and effort to make your experience better than before.", 'woo-multistep-checkout'); ?></p><p class="thwmsc-review-content-text"><?php esc_html_e("If you like it, please show us some love with a ", 'woo-multistep-checkout'); ?><b>5-star </b>rating  &#10084;</p>
				<div class="action-row"> 
			        <a class="thwmsc-notice-action thwmsc-yes" onclick="window.open('https://wordpress.org/support/plugin/woo-multistep-checkout/reviews/', '_blank')" style="margin-right:16px; text-decoration: none; cursor: pointer;">
			        	<?php esc_html_e("Yes, today", 'woo-multistep-checkout'); ?>
			        </a>

			        <a class="thwmsc-notice-action thwmsc-done" href="<?php echo esc_url($reviewed_url); ?>" style="margin-right:16px; text-decoration: none">
			        	<?php esc_html_e('Already, Did', 'woo-multistep-checkout'); ?>
			        </a>

			        <a class="thwmsc-notice-action thwmsc-remind" href="<?php echo esc_url($remind_url); ?>" style="margin-right:16px; text-decoration: none">
			        	<?php esc_html_e('Maybe later', 'woo-multistep-checkout'); ?>
			        </a>

			        <a class="thwmsc-notice-action thwmsc-dismiss" href="<?php echo esc_url($dismiss_url); ?>" style="margin-right:16px; text-decoration: none">
			        	<?php esc_html_e("Nah, Never", 'woo-multistep-checkout'); ?>
			        </a>
				</div>
			</div>
            <div class="thwmsc-review-star">
                <span class="logo" style="float: right">
                    <img src="<?php echo esc_url(THWMSCF_URL .'assets/images/stars.svg'); ?>" style="height:19px;margin-top:4px;" alt="themehigh"/>
                </span>
            </div>
			<div class="thwmsc-themehigh-logo">
				<span class="logo" style="float: right">
            		<a target="_blank" href="https://www.themehigh.com">
                		<img src="<?php echo esc_url(THWMSCF_URL .'assets/images/logo.svg'); ?>" style="height:15px;margin-top:4px;" alt="themehigh"/>
                	</a>
                </span>
			</div>
	    </div>

		<?php
	}

    public function quick_links(){
        $current_screen = get_current_screen();
        if($current_screen->id !== 'woocommerce_page_woo_multistep_checkout'){
            return;
        }
        
        ?>
        <div class="th_quick_widget-float">
            <div id="myDIV" class="th_quick_widget">
                <div class="th_whead">
                    <div class="th_whead_close_btn" onclick="thwmscfwidgetClose()">
                        <img src="<?php echo esc_url( THWMSCF_URL.'assets/images/th-icon-cross.svg' ); ?>" alt="" class="">
                    </div>
                    <!-- -----------------------------Widget head icon ----------------------------->
                    <div class="th_whead_icon">
                        <img src="<?php echo esc_url( THWMSCF_URL.'assets/images/th-icon-purple.svg' ); ?>" alt="" class="">
                    </div>
                    <!--------------------------Whidget heading section ---------------------------->
                    <div class="th_quick_widget_heading">
                        <div class="th_whead_t1"><p>Welcome, we're</p><p><b style="font-size: 28px;">themehigh</b></p></div>
                        </div>
                    </div>
                    <!-- --------------------Widget Body--------------------------------------- -->
                    <div class="th_quick_widget_body">
                        <ul>
                            <li>
                                <div class="list_icon" style="background-color: rgba(199, 0, 255, 0.15);">
                                    <img src="<?php echo esc_url( THWMSCF_URL.'assets/images/th-icon-bulb.svg' ); ?>" alt="" class="">
                                </div>
                                <a href="https://app.loopedin.io/multi-step-checkout-for-woocommerce/ideas" target="_blank" class="quick-widget-doc-link">Request a feature</a>
                            </li>
                            <li>
                                <div class="list_icon" style="background-color: rgb(30 194 229 / 11%);">
                                    <img src="<?php echo esc_url( THWMSCF_URL.'assets/images/upgrade-icon.svg' ); ?>" alt="" class="">
                                </div>
                                <a href="https://www.themehigh.com/product/woocommerce-multi-step-checkout/" target="_blank" class="quick-widget-doc-link">Upgrade to Premium</a>
                            </li>
                            <li>    
                                <div class="list_icon" style="background-color: rgba(255, 245, 235, 1);">
                                    <img src="<?php echo esc_url( THWMSCF_URL.'assets/images/th-icon-join.svg' ); ?>" alt="" class="">
                                </div><a href="https://www.facebook.com/groups/740534523911091" target="_blank" class="quick-widget-community-link">Join our Community</a>
                            </li>
                            <li>
                                <div class="list_icon" style="background-color: rgba(238, 240, 255, 1);">
                                    <img src="<?php echo esc_url( THWMSCF_URL.'assets/images/th-icon-speaker.svg' ); ?>" alt="" class="">
                                </div><a href="https://wordpress.org/support/plugin/woo-multistep-checkout/" target="_blank" class="quick-widget-support-link">Get support</a>
                            </li>
                            <li>
                                <div class="list_icon" style="background-color: rgba(255, 0, 0, 0.15);">
                                    <img src="<?php echo esc_url( THWMSCF_URL.'assets/images/demo-icon.svg' ); ?>" alt="" class="">
                                </div><a href="https://flydemos.com/wmsc/" target="_blank" class="quick-widget-support-link">Try demo</a>
                            </li>
                        </ul>
                    </div>
                </div>
            <div id="myWidget" class="widget-popup" onclick="thwmscfwidgetPopUp()">
                <span id="th_quick_border_animation"></span>
                <div class="widget-popup-icon" id="th_arrow_head">
                    <img src="<?php echo esc_url( THWMSCF_URL.'assets/images/th-icon-white.svg' ); ?>" alt="" class="">
                </div>
            </div>
            </div>
        <?php
    }

	public function hide_thwmscf_admin_notice(){

		check_ajax_referer('thwmscf_notice_security', 'thwmsc_review_nonce');

		$now = time();
		update_user_meta( get_current_user_id(), 'thwmsc_review_skipped', true );
		update_user_meta( get_current_user_id(), 'thwmsc_review_skipped_time', $now );
	}

	public function admin_notice_js_snippet(){

		if(!apply_filters('thwmsc_dismissable_admin_notice_javascript', true)){
			return;
		}		
		?>
	    <script>
			var thwmsc_dismissable_notice = (function($, window, document) {
				'use strict';

				$( document ).on( 'click', '.thwmsc-notice .notice-dismiss', function() {
					var wrapper = $(this).closest('div.thwmsc-notice');
					var nonce = wrapper.data("nonce");
					var data = {
						thwmsc_review_nonce: nonce,
						action: 'hide_thwmscf_admin_notice',
					};
					$.post( ajaxurl, data, function() {

					});
				});

			}(window.jQuery, window, document));	
	    </script>
	    <?php
	}

    public function render_form_field_blank($colspan = 3){
        ?>
        <td width="<?php echo esc_attr($colspan * 33.3); ?>%" colspan="<?php echo esc_attr($colspan); ?>">&nbsp;</td>  
        <?php
    }

    public function render_buttons_after_tab_panels(){
        $thwmscf_settings = get_option('THWMSC_SETTINGS');
        $button_prev_text = !empty($thwmscf_settings['button_prev_text']) ? wptexturize($thwmscf_settings['button_prev_text']) : "Previous";
        $button_next_text = !empty($thwmscf_settings['button_next_text']) ? wptexturize($thwmscf_settings['button_next_text']) : "Next";
        $back_to_cart_button = isset($thwmscf_settings['back_to_cart_button']) && $thwmscf_settings['back_to_cart_button'] ? wptexturize($thwmscf_settings['back_to_cart_button']) : '';
        $back_to_cart_button_text = isset($thwmscf_settings['back_to_cart_button_text']) && $thwmscf_settings['back_to_cart_button_text'] ? wptexturize($thwmscf_settings['back_to_cart_button_text']) : 'Back to cart';

        ?>
        <div class="thwmscf-buttons">
            <input type="button" class="button-prev action-prev" value="<?php echo esc_attr(__( $button_prev_text, 'woo-multistep-checkout' )); ?>">
            <input type="button" class="button-next action-next" value="<?php echo esc_attr(__( $button_next_text, 'woo-multistep-checkout' )); ?>">
            <?php 
            if($back_to_cart_button == 'yes'){
                ?>
                <a class="button thwmscf-cart-url" href="<?php echo esc_url(wc_get_cart_url()); ?>"><?php echo wp_kses_post(__( $back_to_cart_button_text, 'woo-multistep-checkout' )); ?></a>
                <?php
            } ?>
        </div>
        <?php
    }

    public function thwmscf_step_validation(){
        $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : false;
        if ( ! wp_verify_nonce( $nonce, 'ajax-nonce' ) ) {
            die();
        }
        try{
            $request     = $_POST;
            $posted_data = $this->get_posted_data( $request );

            $errors = new WP_Error();
            $result = $this->validate_checkout( $posted_data, $errors );

            $error_msg = $errors->get_error_messages();
            if (empty($error_msg)) {
                $this->send_ajax_success_response();
            } else {
                foreach ( $errors->get_error_messages() as $message ) {
                    wc_add_notice( $message, 'error' );
                }
                $this->send_ajax_failure_response();
            }
        } catch ( Exception $e ) {
            wc_add_notice( $e->getMessage(), 'error' );
            $this->send_ajax_failure_response();
        }
    }

    /**
     * Validates that the checkout has enough info to proceed.
     *
     * @since  3.0.0
     * @param  array    $data An array of posted data.
     * @param  WP_Error $errors
     */
    protected function validate_checkout( &$data, &$errors ) {
        $this->validate_posted_data( $data, $errors );
        if ( apply_filters( 'thwmscf_enable_check_cart_items_on_step_validation', false ) ) {
            WC()->checkout()->check_cart_items();
        }

        if ( isset( $data['terms'] ) ) {
            if ( empty( $data['woocommerce_checkout_update_totals'] ) && empty( $data['terms'] ) && apply_filters( 'woocommerce_checkout_show_terms', wc_get_page_id( 'terms' ) > 0 ) ) {
                $errors->add( 'terms', __( 'You must accept our Terms &amp; Conditions.', 'woocommerce' ) );
            }
        }

        do_action( 'woocommerce_after_checkout_validation', $data, $errors );
    }

    /**
     * Validates the posted checkout data based on field properties.
     *
     * @since  3.0.0
     * @param  array    $data An array of posted data.
     * @param  WP_Error $errors
     */
    protected function validate_posted_data( &$data, &$errors ) {
        $checkout_fields = WC()->checkout()->get_checkout_fields();
        $checkout_fields = apply_filters( 'thwcfe_remove_disabled_fields_and_sections', $checkout_fields, $data );
        if ( ! $checkout_fields ) {
            $checkout_fields = WC()->checkout()->get_checkout_fields();
        }

        foreach ( $checkout_fields as $fieldset_key => $fieldset ) {
            $validate_fieldset = true;
            if ( $this->maybe_skip_fieldset( $fieldset_key, $data ) ) {
                $validate_fieldset = false;
            }

            foreach ( $fieldset as $key => $field ) {
                if ( ! isset( $data[ $key ] ) ) {
                    continue;
                }
                $required    = ! empty( $field['required'] );
                $format      = array_filter( isset( $field['validate'] ) ? (array) $field['validate'] : array() );
                $field_label = isset( $field['label'] ) ? $field['label'] : '';

                switch ( $fieldset_key ) {
                    case 'shipping':
                        /* translators: %s: field name */
                        $field_label = sprintf( _x( 'Shipping %s', 'checkout-validation', 'woocommerce' ), $field_label );
                        break;
                    case 'billing':
                        /* translators: %s: field name */
                        $field_label = sprintf( _x( 'Billing %s', 'checkout-validation', 'woocommerce' ), $field_label );
                        break;
                }

                if ( in_array( 'postcode', $format, true ) ) {
                    $country      = isset( $data[ $fieldset_key . '_country' ] ) ? $data[ $fieldset_key . '_country' ] : WC()->customer->{"get_{$fieldset_key}_country"}();
                    $data[ $key ] = wc_format_postcode( $data[ $key ], $country );

                    if ( $validate_fieldset && '' !== $data[ $key ] && ! WC_Validation::is_postcode( $data[ $key ], $country ) ) {
                        switch ( $country ) {
                            case 'IE':
                                /* translators: %1$s: field name, %2$s finder.eircode.ie URL */
                                $postcode_validation_notice = sprintf( __( '%1$s is not valid. You can look up the correct Eircode <a target="_blank" href="%2$s">here</a>.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>', 'https://finder.eircode.ie' );
                                break;
                            default:
                                /* translators: %s: field name */
                                $postcode_validation_notice = sprintf( __( '%s is not a valid postcode / ZIP.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' );
                        }
                        $errors->add( $key . '_validation', apply_filters( 'woocommerce_checkout_postcode_validation_notice', $postcode_validation_notice, $country, $data[ $key ] ), array( 'id' => $key ) );
                    }
                }

                if ( in_array( 'phone', $format, true ) ) {
                    if ( $validate_fieldset && '' !== $data[ $key ] && ! WC_Validation::is_phone( $data[ $key ] ) ) {
                        /* translators: %s: phone number */
                        $errors->add( $key . '_validation', sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), array( 'id' => $key ) );
                    }
                }

                if ( in_array( 'email', $format, true ) && '' !== $data[ $key ] ) {
                    $email_is_valid = is_email( $data[ $key ] );
                    $data[ $key ]   = sanitize_email( $data[ $key ] );

                    if ( $validate_fieldset && ! $email_is_valid ) {
                        /* translators: %s: email address */
                        $errors->add( $key . '_validation', sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), array( 'id' => $key ) );
                        continue;
                    }
                }

                if ( '' !== $data[ $key ] && in_array( 'state', $format, true ) ) {
                    $country      = isset( $data[ $fieldset_key . '_country' ] ) ? $data[ $fieldset_key . '_country' ] : WC()->customer->{"get_{$fieldset_key}_country"}();
                    $valid_states = WC()->countries->get_states( $country );

                    if ( ! empty( $valid_states ) && is_array( $valid_states ) && count( $valid_states ) > 0 ) {
                        $valid_state_values = array_map( 'wc_strtoupper', array_flip( array_map( 'wc_strtoupper', $valid_states ) ) );
                        $data[ $key ]       = wc_strtoupper( $data[ $key ] );

                        if ( isset( $valid_state_values[ $data[ $key ] ] ) ) {
                            // With this part we consider state value to be valid as well, convert it to the state key for the valid_states check below.
                            $data[ $key ] = $valid_state_values[ $data[ $key ] ];
                        }

                        if ( $validate_fieldset && ! in_array( $data[ $key ], $valid_state_values, true ) ) {
                            /* translators: 1: state field 2: valid states */
                            $errors->add( $key . '_validation', sprintf( __( '%1$s is not valid. Please enter one of the following: %2$s', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>', implode( ', ', $valid_states ) ), array( 'id' => $key ) );
                        }
                    }
                }

                if ( $validate_fieldset && $required && '' === $data[ $key ] ) {
                    /* translators: %s: field name */
                    $errors->add( $key . '_required', apply_filters( 'woocommerce_checkout_required_field_notice', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), $field_label ), array( 'id' => $key ) );
                }
            }
        }
    }

    /**
     * See if a fieldset should be skipped.
     *
     * @since 3.0.0
     *
     * @param string $fieldset_key
     * @param array  $data
     *
     * @return bool
     */
    protected function maybe_skip_fieldset( $fieldset_key, $data ) {
        if ( 'shipping' === $fieldset_key && ( ( isset( $data['ship_to_different_address'] ) && ! $data['ship_to_different_address'] ) || ! WC()->cart->needs_shipping_address() ) ) {
            return true;
        }
        if ( 'account' === $fieldset_key && ( is_user_logged_in() || ( ! WC()->checkout()->is_registration_required() && empty( $data['createaccount'] ) ) ) ) {
            return true;
        }
        return false;
    }

    protected function send_ajax_failure_response() {
        if ( is_ajax() ) {
            // only print notices if not reloading the checkout, otherwise they're lost in the page reload
            if ( ! isset( WC()->session->reload_checkout ) ) {
                ob_start();
                wc_print_notices();
                $messages = ob_get_clean();
            }

            $response = array(
                'result'   => 'failure',
                'messages' => isset( $messages ) ? $messages : '',
                'refresh'  => isset( WC()->session->refresh_totals ),
                'reload'   => isset( WC()->session->reload_checkout ),
            );

            unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

            wp_send_json( $response );
        }
    }

    protected function send_ajax_success_response() {
        if ( is_ajax() ) {
            wp_send_json(
                array(
                    'result' => 'success',
                )
            );
        }
    }

    public function get_posted_data( $request ) {
        $posted_data = $request['posted'];

        if ( apply_filters( 'thwmsc_clean_and_sanitize_step_data', true ) ) {
            $sanitized = array();

            foreach ( $posted_data as $key => $field ) {
                $value             = isset( $posted_data[ $key ] ) ? wc_clean( wp_unslash( $field ) ) : '';
                $sanitized[ $key ] = $value;
            }

            return $sanitized;
        }

        return $posted_data;
    }
}

endif;
