<?php
/**
 * OFFITRAVEL - Panel "Guests" de OVA en el editor de tours.
 *
 * OVA renderiza este panel con un display:none inline y no incluye una acción
 * de administración que lo vuelva a mostrar. Este ajuste solo elimina ese
 * ocultamiento en el editor de productos; OVA conserva el guardado nativo.
 */

defined('ABSPATH') || exit;

/**
 * Muestra únicamente el panel que contiene el campo "Minimum adults".
 *
 * @param string $hook_suffix Página actual del administrador.
 * @return void
 */
function offitravel_show_ovabrw_guests_panel($hook_suffix)
{
	if (!in_array($hook_suffix, array('post.php', 'post-new.php'), true)) {
		return;
	}

	$screen = get_current_screen();

	if (!$screen || 'product' !== $screen->post_type) {
		return;
	}

	wp_enqueue_script('jquery');

	$script = <<<'JS'
jQuery(function ($) {
	// Mostrar solo el acordeón nativo de OVA que contiene "Minimum adults".
	$('.ovabrw_adults_min_field')
		.closest('.ovabrw-advanced-settings')
		.show();
});
JS;

	wp_add_inline_script('jquery', $script);
}
add_action('admin_enqueue_scripts', 'offitravel_show_ovabrw_guests_panel', 30);
