<?php
/**
 * Setup tripgo Child Theme's textdomain.
 *
 * Declare textdomain for this child theme.
 * Translations can be filed in the /languages/ directory.
 */
function tripgo_child_theme_setup()
{
	load_child_theme_textdomain('tripgo-child', get_stylesheet_directory() . '/languages');
}
add_action('after_setup_theme', 'tripgo_child_theme_setup');

add_action('wp_enqueue_scripts', 'tripgo_enqueue_styles');
function tripgo_enqueue_styles()
{
	$parenthandle = 'tripgo-style'; // This is 'twentyfifteen-style' for the Twenty Fifteen theme.
	$theme = wp_get_theme();
	wp_enqueue_style(
		$parenthandle,
		get_template_directory_uri() . '/style.css',
		array(),  // if the parent theme code has a dependency, copy it to here
		$theme->parent()->get('Version')
	);
	wp_enqueue_style(
		'child-style',
		get_stylesheet_uri(),
		array($parenthandle),
		$theme->get('Version') // this only works if you have Version in the style header
	);

	if (function_exists('is_checkout') && is_checkout()) {

		$checkout_summary_css = get_stylesheet_directory() . '/css/checkout-summary.css';

		wp_enqueue_style(
			'offitravel-checkout-summary',
			get_stylesheet_directory_uri() . '/css/checkout-summary.css',
			array('child-style'),
			file_exists($checkout_summary_css) ? filemtime($checkout_summary_css) : $theme->get('Version')
		);

		wp_enqueue_script(
			'offitravel-checkout-field-errors',
			get_stylesheet_directory_uri() . '/js/checkout-field-errors.js',
			array(),
			'1.0.0',
			true
		);

		$checkout_tracking_js = get_stylesheet_directory() . '/js/checkout-tracking.js';

		wp_enqueue_script(
			'offitravel-checkout-tracking',
			get_stylesheet_directory_uri() . '/js/checkout-tracking.js',
			array('jquery'),
			file_exists($checkout_tracking_js) ? filemtime($checkout_tracking_js) : '1.0.0',
			true
		);

		wp_localize_script(
			'offitravel-checkout-tracking',
			'offiCheckoutTracking',
			array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('offi_checkout_step'),
				'debug' => defined('WP_DEBUG') && WP_DEBUG,
				'eventName' => 'CheckoutStep1Completed',
			)
		);

		wp_add_inline_style(
			'child-style',
			'
			.wc-block-components-text-input.has-error input {
  			border-color: #cc1818 !important;
  			box-shadow: 0 0 0 1px #cc1818 !important;
			}
			.wc-block-components-text-input.has-error label {
				color: #cc1818 !important;
			}
			.thwmscf-tab-panel-1 .form-row.has-error input,
			.thwmscf-tab-panel-1 .form-row.has-error select,
			.thwmscf-tab-panel-1 .form-row.has-error textarea,
			.thwmscf-tab-panel-1 .form-row.woocommerce-invalid input,
			.thwmscf-tab-panel-1 .form-row.woocommerce-invalid select,
			.thwmscf-tab-panel-1 .form-row.woocommerce-invalid textarea {
				border-color: #cc1818 !important;
				box-shadow: 0 0 0 1px #cc1818 !important;
			}
			.thwmscf-tab-panel-1 .form-row.has-error label,
			.thwmscf-tab-panel-1 .form-row.woocommerce-invalid label {
				color: #cc1818 !important;
			}
			.thwmscf-tab-panel-1 .form-row.has-error .select2-selection--single,
			.thwmscf-tab-panel-1 .form-row.woocommerce-invalid .select2-selection--single {
				border-color: #cc1818 !important;
				box-shadow: 0 0 0 1px #cc1818 !important;
			}
			.thwmscf-tab-panel-1 .offi-step1-field-error {
				margin: 4px 0 0;
				color: #cc1818;
				font-size: 14px;
				line-height: 1.35;
			}'
		);
	}


	/**
	 * Botón flotante de reserva en páginas individuales de tours.
	 */
	if (
		function_exists('is_product') &&
		is_product()
	) {
		$tour_floating_booking_css = get_stylesheet_directory() . '/css/tour-floating-booking.css';
		$tour_floating_booking_js = get_stylesheet_directory() . '/js/tour-floating-booking.js';
		wp_enqueue_style(
			'offitravel-tour-floating-booking',
			get_stylesheet_directory_uri() . '/css/tour-floating-booking.css',
			array('child-style'),
			file_exists($tour_floating_booking_css)
			? filemtime($tour_floating_booking_css)
			: $theme->get('Version')
		);

		wp_enqueue_script(
			'offitravel-tour-floating-booking',
			get_stylesheet_directory_uri() . '/js/tour-floating-booking.js',
			array(),
			file_exists($tour_floating_booking_js)
			? filemtime($tour_floating_booking_js)
			: '1.0.0',
			true
		);
	}
}

add_filter('wp_mail_smtp_core_wp_mail_function_incorrect_location_notice', '__return_false');

// Export Custom Taxonomy and Custom Checkout Fields
add_action('rss2_head', function () {
	if (is_admin()) {
		// Custom Taxonomies
		$custom_taxonomies = recursive_array_replace('\\', '', get_option('ovabrw_custom_taxonomy', []));

		if (!empty($custom_taxonomies) && is_array($custom_taxonomies)) {
			foreach ($custom_taxonomies as $slug => $items) {
				echo "<ovabrw_custom_taxonomies>\n";
				if ($slug)
					echo "\t<slug>" . $slug . "</slug>\n";
				if ($items['name'])
					echo "\t<name>" . $items['name'] . "</name>\n";
				if ($items['singular_name'])
					echo "\t<singular_name>" . $items['singular_name'] . "</singular_name>\n";
				if ($items['label_frontend'])
					echo "\t<label_frontend>" . $items['label_frontend'] . "</label_frontend>\n";
				if ($items['enabled'])
					echo "\t<enabled>" . $items['enabled'] . "</enabled>\n";
				if ($items['show_listing'])
					echo "\t<show_listing>" . $items['show_listing'] . "</show_listing>\n";
				echo "</ovabrw_custom_taxonomies>\n";
			}
		}

		// Custom Checkout Fields
		$checkout_fields = recursive_array_replace('\\', '', get_option('ovabrw_booking_form', []));

		if (!empty($checkout_fields) && is_array($checkout_fields)) {
			foreach ($checkout_fields as $slug => $items) {
				// Select
				$options_key = isset($items['ova_options_key']) && $items['ova_options_key'] ? $items['ova_options_key'] : '';
				$options_text = isset($items['ova_options_text']) && $items['ova_options_text'] ? $items['ova_options_text'] : '';
				$options_price = isset($items['ova_options_price']) && $items['ova_options_price'] ? $items['ova_options_price'] : '';

				// Radio
				$radio_values = isset($items['ova_radio_values']) && $items['ova_radio_values'] ? $items['ova_radio_values'] : '';
				$radio_prices = isset($items['ova_radio_prices']) && $items['ova_radio_prices'] ? $items['ova_radio_prices'] : '';

				// Checkbox
				$checkbox_key = isset($items['ova_checkbox_key']) && $items['ova_checkbox_key'] ? $items['ova_checkbox_key'] : '';
				$checkbox_text = isset($items['ova_checkbox_text']) && $items['ova_checkbox_text'] ? $items['ova_checkbox_text'] : '';
				$checkbox_price = isset($items['ova_checkbox_price']) && $items['ova_checkbox_price'] ? $items['ova_checkbox_price'] : '';

				// File
				$max_file_size = isset($items['max_file_size']) && $items['max_file_size'] ? $items['max_file_size'] : '';

				echo "<ovabrw_custom_checkout_fields>\n";
				if ($slug)
					echo "\t<slug>" . $slug . "</slug>\n";
				if ($items['type'])
					echo "\t<type>" . $items['type'] . "</type>\n";
				if ($items['label'])
					echo "\t<label>" . $items['label'] . "</label>\n";
				if ($items['default'])
					echo "\t<default>" . $items['default'] . "</default>\n";
				if ($items['placeholder'])
					echo "\t<placeholder>" . $items['placeholder'] . "</placeholder>\n";
				if ($items['class'])
					echo "\t<class>" . $items['class'] . "</class>\n";
				if ($items['required'])
					echo "\t<required>" . $items['required'] . "</required>\n";
				if ($items['enabled'])
					echo "\t<enabled>" . $items['enabled'] . "</enabled>\n";

				// Select Keys
				if (!empty($options_key) && is_array($options_key)) {
					echo "\t<select_keys>" . implode('|', $options_key) . "</select_keys>\n";
				}
				// Select Texts
				if (!empty($options_text) && is_array($options_text)) {
					echo "\t<select_texts>" . implode('|', $options_text) . "</select_texts>\n";
				}
				// Select Prices
				if (!empty($options_price) && is_array($options_price)) {
					echo "\t<select_prices>" . implode('|', $options_price) . "</select_prices>\n";
				}
				// Radio Values
				if (!empty($radio_values) && is_array($radio_values)) {
					echo "\t<radio_values>" . implode('|', $radio_values) . "</radio_values>\n";
				}
				// Radio Prices
				if (!empty($radio_prices) && is_array($radio_prices)) {
					echo "\t<radio_prices>" . implode('|', $radio_prices) . "</radio_prices>\n";
				}
				// Checkbox Keys
				if (!empty($checkbox_key) && is_array($checkbox_key)) {
					echo "\t<checkbox_keys>" . implode('|', $checkbox_key) . "</checkbox_keys>\n";
				}
				// Checkbox Texts
				if (!empty($checkbox_text) && is_array($checkbox_text)) {
					echo "\t<checkbox_texts>" . implode('|', $checkbox_text) . "</checkbox_texts>\n";
				}
				// Checkbox Prices
				if (!empty($checkbox_price) && is_array($checkbox_price)) {
					echo "\t<checkbox_prices>" . implode('|', $checkbox_price) . "</checkbox_prices>\n";
				}
				// Max File Size
				if ($max_file_size) {
					echo "\t<max_file_size>" . $max_file_size . "</max_file_size>\n";
				}
				echo "</ovabrw_custom_checkout_fields>\n";
			}
		}
	}
});

function custom_focus_search_input()
{
	?>
	<script>
		document.addEventListener('DOMContentLoaded', function () {
			const searchFields = document.querySelectorAll('.search-field');

			searchFields.forEach(function (field) {
				field.addEventListener('click', function () {
					const input = document.querySelector('.select2-search__field');

					if (input) {
						input.focus();
					}
				});
			});
		});
	</script>
	<?php
}
add_action('wp_footer', 'custom_focus_search_input');


/**
 * Datepicker frontend OVA BRW: año actual + 2 siguientes.
 */
function offitravel_booking_min_year($year)
{
	return (int) gmdate('Y');
}
function offitravel_booking_max_year_plus_two($year)
{
	return (int) gmdate('Y') + 2;
}
add_filter('ovabrw_datepicker_min_year', 'offitravel_booking_min_year');
add_filter('ovabrw_datepicker_max_year', 'offitravel_booking_max_year_plus_two');

/**
 * Tour list: alternate months suggestions when search has 0 results (see shortcode below).
 */
require_once get_stylesheet_directory() . '/inc/tour-suggestions-fallback.php';

/**
 * Home: búsqueda vacía (destino cualquiera, sin mes) → ancla #categoria-viajes + tab Todos.
 */
require_once get_stylesheet_directory() . '/inc/home-search-empty-redirect.php';

/**
 * Checkout: aside custom de resumen del pedido.
 */
require_once get_stylesheet_directory() . '/inc/checkout-summary-sidebar.php';

/**
 * Checkout: guardado de leads (paso 1) y tracking de compra en thank you.
 */
require_once get_stylesheet_directory() . '/inc/checkout-step-leads.php';
require_once get_stylesheet_directory() . '/inc/checkout-purchase-tracking.php';
require_once get_stylesheet_directory() . '/inc/meta-purchase-enrichment.php';


require_once get_stylesheet_directory() . '/inc/checkout-legal-text.php';

/**
 * Límite de la descripción corta de productos (WooCommerce = campo excerpt).
 * Se cuenta texto plano (sin etiquetas HTML), igual en el editor visual y al guardar.
 *
 * Cambia solo el número de abajo (ej. 20 en lugar de 300).
 */
if (!defined('OFFITRAVEL_PRODUCT_EXCERPT_MAX_CHARS')) {
	define('OFFITRAVEL_PRODUCT_EXCERPT_MAX_CHARS', 80);
}

function offitravel_product_excerpt_max_chars()
{
	return (int) apply_filters('offitravel_product_excerpt_max_chars', OFFITRAVEL_PRODUCT_EXCERPT_MAX_CHARS);
}

/**
 * Si estamos editando/añadiendo un producto (admin).
 */
function offitravel_product_excerpt_limit_active_for_request()
{
	static $resolved = null;
	if (null !== $resolved) {
		return $resolved;
	}
	$resolved = false;
	if (!is_admin()) {
		return $resolved;
	}
	global $pagenow;
	if (empty($pagenow) || !in_array($pagenow, array('post.php', 'post-new.php'), true)) {
		return $resolved;
	}
	if ('post-new.php' === $pagenow) {
		$post_type = isset($_GET['post_type']) ? sanitize_key(wp_unslash($_GET['post_type'])) : 'post';
		$resolved = ('product' === $post_type);
		return $resolved;
	}
	$post_id = isset($_GET['post']) ? absint($_GET['post']) : 0;
	if ($post_id) {
		$resolved = ('product' === get_post_type($post_id));
	}
	return $resolved;
}

/**
 * Límite en TinyMCE: callback setup inyectado antes de tinymce.init (evita depender del global tinymce al cargar jQuery).
 *
 * @link https://developer.wordpress.org/reference/hooks/tiny_mce_before_init/
 */
function offitravel_product_excerpt_tinymce_before_init($mce_init, $editor_id)
{
	if ('excerpt' !== $editor_id || !offitravel_product_excerpt_limit_active_for_request()) {
		return $mce_init;
	}
	$max = absint(offitravel_product_excerpt_max_chars());
	if ($max < 1) {
		return $mce_init;
	}

	$mce_snippet = <<<'OFFITRV_MCE_SETUP'
function(ed){var maxChars=__OFFMAX__;if(ed.id!=="excerpt"){return;}
function excerptPlainRoom(editor){var text=editor.getContent({format:"text"})||"",sel=editor.selection.getContent({format:"text"})||"";return maxChars-text.length+sel.length;}
function trimEditorPlain(editor){var t=editor.getContent({format:"text"})||"";if(t.length<=maxChars){return;}editor.setContent(t.substring(0,maxChars));editor.save();}
function updateBar(editor){var n=document.getElementById("offitravel-excerpt-count");if(!n){return;}var len=(editor.getContent({format:"text"})||"").length;n.textContent=len+" / "+maxChars;n.style.color=len>maxChars?"#b32d2e":"";}
function sync(editor){trimEditorPlain(editor);updateBar(editor);}
ed.on("init",function(){updateBar(ed);});
ed.on("keydown",function(e){if(e.isComposing){return;}var k=e.keyCode||e.which;if(k===229){return;}if(e.ctrlKey||e.metaKey||e.altKey){return;}if(k===8||k===46){return;}if(k===9||k===27){return;}if(k<32&&k!==13){return;}if(k>=33&&k<=40){return;}if(excerptPlainRoom(ed)>=1){return;}e.preventDefault();e.stopImmediatePropagation();return false;});
ed.on("PastePreProcess",function(e){var room=excerptPlainRoom(ed);if(room<=0){e.content="";return;}var div=document.createElement("div");div.innerHTML=e.content||"";var plain=div.textContent||div.innerText||"";if(plain.length<=room){return;}plain=plain.substring(0,room);var esc=typeof ed.dom!=="undefined"&&ed.dom.encode?ed.dom.encode(plain):plain.replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");e.content="<p>"+esc.replace(/\r\n|\n|\r/g,"<br />")+"</p>";});
ed.on("keyup change Undo Redo NodeChange input SetContent",function(){sync(ed);});try{ed.on("PastePostProcess",function(){sync(ed);});}catch(err){}
ed.on("paste",function(){var w=typeof ed.getWin==="function"?ed.getWin():window;w.setTimeout(function(){sync(ed);},0);});
ed.on("drop",function(){var w=typeof ed.getWin==="function"?ed.getWin():window;w.setTimeout(function(){sync(ed);},0);});
ed.on("compositionend",function(){sync(ed);});}
OFFITRV_MCE_SETUP;

	$mce_init['setup'] = str_replace('__OFFMAX__', (string) $max, $mce_snippet);
	return $mce_init;
}
add_filter('tiny_mce_before_init', 'offitravel_product_excerpt_tinymce_before_init', 999, 2);
add_filter('teeny_mce_before_init', 'offitravel_product_excerpt_tinymce_before_init', 999, 2);

/**
 * Recorta el excerpt al guardar si supera el límite (texto plano).
 */
function offitravel_limit_product_excerpt_on_save($data, $postarr)
{
	if (($data['post_type'] ?? '') !== 'product') {
		return $data;
	}
	$max = offitravel_product_excerpt_max_chars();
	if ($max < 1 || empty($data['post_excerpt'])) {
		return $data;
	}
	$plain = wp_strip_all_tags($data['post_excerpt']);
	if (function_exists('mb_strlen') && function_exists('mb_substr')) {
		if (mb_strlen($plain) <= $max) {
			return $data;
		}
		$data['post_excerpt'] = mb_substr($plain, 0, $max);
	} else {
		if (strlen($plain) <= $max) {
			return $data;
		}
		$data['post_excerpt'] = substr($plain, 0, $max);
	}
	return $data;
}
add_filter('wp_insert_post_data', 'offitravel_limit_product_excerpt_on_save', 10, 2);

/**
 * Admin: contador bajo el excerpt y límite solo en el textarea (pestaña Código).
 * El modo Visual lo controla tiny_mce_before_init (setup de TinyMCE).
 */
function offitravel_product_excerpt_admin_scripts()
{
	if (!offitravel_product_excerpt_limit_active_for_request()) {
		return;
	}

	$max = offitravel_product_excerpt_max_chars();
	wp_enqueue_script('jquery');
	$js = <<<JS
(function($) {
	var maxChars = {$max};
	function plainLenFromHtml(html) {
		var d = document.createElement("div");
		d.innerHTML = html || "";
		return (d.textContent || d.innerText || "").length;
	}
	function plainTextFromHtml(html) {
		var d = document.createElement("div");
		d.innerHTML = html || "";
		return d.textContent || d.innerText || "";
	}
	function updateCount() {
		var n = document.getElementById("offitravel-excerpt-count");
		if (!n) return;
		var ed = typeof tinymce !== "undefined" ? tinymce.get("excerpt") : null;
		var len = 0;
		if (ed && !ed.isHidden()) {
			len = (ed.getContent({ format: "text" }) || "").length;
		} else {
			var ta = document.getElementById("excerpt");
			if (ta) len = plainLenFromHtml(ta.value);
		}
		n.textContent = len + " / " + maxChars;
		if (len > maxChars) n.style.color = "#b32d2e";
		else n.style.color = "";
	}
	function textareaPlainRoom(ta) {
		var plain = plainTextFromHtml(ta.value);
		var sel = ta.value.substring(ta.selectionStart, ta.selectionEnd);
		return maxChars - plain.length + plainTextFromHtml(sel).length;
	}
	$(function() {
		// var box = document.getElementById("postexcerpt");
		// if (!box) return;
		// var p = document.createElement("p");
		// p.className = "description";
		// p.style.marginTop = "6px";
		// p.innerHTML = "<strong id=\"offitravel-excerpt-count\">0 / " + maxChars + '</strong>'
		// 	+ '<br /><span style="font-weight:normal;opacity:.85">Texto visible (sin etiquetas HTML).</span>';
		// box.appendChild(p);
		// if (typeof tinymce !== "undefined") {
		// 	tinymce.on("AddEditor", function(e) {
		// 		if (e.editor && e.editor.id === "excerpt") {
		// 			updateCount();
		// 		}
		// 	});
		// }
		var ta = document.getElementById("excerpt");
		if (ta) {
			ta.addEventListener("keydown", function(e) {
				if (e.isComposing) return;
				var k = e.keyCode || e.which;
				if (k === 229) return;
				if (e.ctrlKey || e.metaKey || e.altKey) return;
				if (k === 8 || k === 46) return;
				if (k === 9 || k === 27) return;
				if (k < 32 && k !== 13) return;
				if (k >= 33 && k <= 40) return;
				if (textareaPlainRoom(ta) >= 1) return;
				e.preventDefault();
				e.stopImmediatePropagation();
			});
			ta.addEventListener("paste", function(e) {
				var room = textareaPlainRoom(ta);
				if (room <= 0) {
					e.preventDefault();
					return;
				}
				var clip = e.clipboardData && e.clipboardData.getData("text/plain");
				if (!clip || clip.length <= room) return;
				e.preventDefault();
				var head = clip.substring(0, room);
				var start = ta.selectionStart;
				var end = ta.selectionEnd;
				ta.value = ta.value.substring(0, start) + head + ta.value.substring(end);
				ta.selectionStart = ta.selectionEnd = start + head.length;
				ta.dispatchEvent(new Event("input", { bubbles: true }));
			});
			ta.addEventListener("compositionend", function() {
				var full = plainTextFromHtml(ta.value);
				if (full.length > maxChars) {
					ta.value = full.substring(0, maxChars);
				}
				updateCount();
			});
			ta.addEventListener("input", function() {
				var full = plainTextFromHtml(ta.value);
				if (full.length > maxChars) {
					ta.value = full.substring(0, maxChars);
				}
				updateCount();
			});
		}
		$(document).on("tinymce-editor-init", function(ev, ed) {
			if (ed && ed.id === "excerpt") {
				updateCount();
			}
		});
		updateCount();
		setTimeout(updateCount, 250);
		setTimeout(updateCount, 800);
	});
})(jQuery);
JS;
	wp_add_inline_script('jquery', $js);
}
add_action('admin_enqueue_scripts', 'offitravel_product_excerpt_admin_scripts', 20);


/**
 * OFFITRAVEL - Cambiar "Población" por "Ciudad"
 * en los campos de dirección de WooCommerce.
 */
function offitravel_change_city_field_label($fields)
{
	if (isset($fields['city'])) {
		$fields['city']['label'] = 'Ciudad';
		$fields['city']['placeholder'] = 'Ciudad';
	}

	return $fields;
}
add_filter('woocommerce_default_address_fields', 'offitravel_change_city_field_label');


/**
 * OFFITRAVEL - Compatibilidad con Cart Abandonment Recovery.
 *
 * El plugin "Cart Abandonment Recovery for WooCommerce" detecta
 * incorrectamente este checkout como WooCommerce Blocks, aunque actualmente
 * se utiliza el checkout clásico mediante shortcode y Woo Multistep Checkout.
 *
 * Debido a esa detección incorrecta, el plugin asigna:
 *
 * wcf_ca_vars._is_block_based_checkout = '1'
 *
 * y busca el correo electrónico en el campo:
 *
 * #email
 *
 * Sin embargo, el checkout clásico de OFFITRAVEL utiliza los campos estándar:
 *
 * #billing_email
 * #billing_phone
 * #billing_first_name
 * #billing_last_name
 *
 * Como #email no existe en este formulario, el JavaScript del plugin termina
 * antes de ejecutar la petición AJAX:
 *
 * cartflows_save_cart_abandonment_data
 *
 * Esto impedía que los nuevos carritos fueran registrados en la tabla:
 *
 * {prefix}_cartflows_ca_cart_abandonment
 *
 * La solución consiste en forzar el modo de checkout clásico estableciendo:
 *
 * wcf_ca_vars._is_block_based_checkout = false
 *
 * Se ejecuta en wp_footer con prioridad alta porque la variable wcf_ca_vars
 * es creada por el plugin al cargar sus scripts en el footer. Al intentar
 * modificarla antes mediante wp_add_inline_script(), la variable todavía no
 * estaba disponible y el ajuste no se aplicaba.
 *
 * No se modifica ningún archivo del plugin, por lo que esta corrección no se
 * pierde al actualizar Cart Abandonment Recovery.
 *
 * Validación realizada:
 * - El plugin empezó a leer correctamente #billing_email.
 * - Se ejecutó la acción AJAX cartflows_save_cart_abandonment_data.
 * - El carrito se registró correctamente con estado "normal".
 * - Posteriormente puede pasar a "abandoned" según el tiempo configurado.
 */
function offitravel_fix_cart_abandonment_checkout_type()
{
	if (
		!function_exists('is_checkout') ||
		!is_checkout() ||
		(function_exists('is_order_received_page') && is_order_received_page())
	) {
		return;
	} ?>
	<script id="offitravel-cart-abandonment-classic-checkout-fix">
		if (window.wcf_ca_vars) {
			window.wcf_ca_vars._is_block_based_checkout = false;
		}
	</script>
	<?php
}
add_action(
	'wp_footer',
	'offitravel_fix_cart_abandonment_checkout_type',
	999
);
