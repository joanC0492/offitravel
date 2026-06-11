<?php if (!defined('ABSPATH')) exit();

// Get categories
$categories = ovabrw_get_meta_data('categories', $args);

// Category ids
if ('yes' != ovabrw_get_meta_data('auto_sorted', $args)) {
	$categories = ovabrw_get_meta_data('category_ids', $args) ? explode('|', $args['category_ids']) : '';
}

// Posts per page
$posts_per_page = ovabrw_get_meta_data('posts_per_page', $args, 9);

// Order
$order = ovabrw_get_meta_data('order', $args, 'DESC');

// Orderby
$orderby = ovabrw_get_meta_data('orderby', $args, 'date');

// Layout
$layout = ovabrw_get_meta_data('layout', $args, 'grid');

// Get template
$grid_template = ovabrw_get_meta_data('grid_template', $args, 'template_1');

// Get column
$column = ovabrw_get_meta_data('column', $args, 'column4');

// Get thumbnail type
$thumbnail_type = ovabrw_get_meta_data('thumbnail_type', $args, 'image');

// Get pagination
$pagination = ovabrw_get_meta_data('pagination', $args, 'yes');

?>
<div class="ovabrw-category-ajax">
	<ul class="ovabrw-category-list">
		<?php if (ovabrw_array_exists($categories)): ?>
			<li class="category-item" data-term-id="0">
				<?php esc_html_e('Todos', 'ova-brw'); ?>
			</li>
			<?php foreach ($categories as $k => $term_id):
				$obj_term 	= get_term($term_id);
				$term_name 	= is_object($obj_term) ? $obj_term->name : '';
			?>
				<li class="category-item <?php echo $k === 0 ? esc_attr('active') : ''; ?>" data-term-id="<?php echo esc_attr($term_id); ?>">
					<?php echo esc_html($term_name); ?>
				</li>
			<?php endforeach;
		else: ?>
			<li class="category-item active" data-term-id="0">
				<?php esc_html_e('Todos', 'ova-brw'); ?>
			</li>
		<?php endif; ?>
	</ul>
	<div class="ovabrw-category-products"></div>
	<!-- Load more -->
	<div class="wrap-load-more">
		<svg class="loader" width="50" height="50">
			<circle cx="25" cy="25" r="10" />
			<circle cx="25" cy="25" r="20" />
		</svg>
	</div>
	<input
		type="hidden"
		name="category-ajax-input"
		data-posts-per-page="<?php echo esc_attr($posts_per_page); ?>"
		data-paged="1"
		data-order="<?php echo esc_attr($order); ?>"
		data-orderby="<?php echo esc_attr($orderby); ?>"
		data-layout="<?php echo esc_attr($layout); ?>"
		data-grid_template="<?php echo esc_attr($grid_template); ?>"
		data-column="<?php echo esc_attr($column); ?>"
		data-thumbnail-type="<?php echo esc_attr($thumbnail_type); ?>"
		data-pagination="<?php echo esc_attr($pagination); ?>" />
</div>