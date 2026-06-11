<!DOCTYPE html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="UTF-8">
	<meta http-equiv="Content-Type" content="text/html; charset=<?php bloginfo('charset'); ?>" />
	<link rel="profile" href="//gmpg.org/xfn/11">
	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">
	<script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
	<script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
	<?php wp_head(); ?>
</head>

<?php $type = '';
if (tripgo_is_woo_active()) {
	if (is_product()) {
		$id = get_the_ID();

		$product = wc_get_product($id);
		$type 	 = $product->get_type();
	}
}
?>

<body <?php if (($type == 'simple') || ($type == 'grouped') || ($type == 'variable') || ($type == 'external')) {
			body_class('single-product-not-rental');
		} else {
			body_class();
		}
		?>>
	<?php
	if (function_exists('wp_body_open')) {
		wp_body_open();
	}
	?>

	<div class="ovamegamenu_container_default"></div>
	<div class="wrap-fullwidth">
		<div class="inside-content">


			<?php echo apply_filters('tripgo_render_header', ''); ?>