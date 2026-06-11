<?php if ( !defined( 'ABSPATH' ) ) exit();

// Get post id
$id = get_the_ID();

// Get data
$data_destination = $_GET;

// Template
$template = 'single-destination-template1.php';

// Get single destination template
$single_destination_template = get_theme_mod( 'single_destination_template', 'template1' );
if ( $single_destination_template == 'template2' ) {
	$template = 'single-destination-template2.php';
}

if ( isset( $data_destination['destination_template'] ) && $data_destination['destination_template'] ) {
	if ( $data_destination['destination_template'] == 'template2' ) {
		$template = 'single-destination-template2.php';
	} elseif( $data_destination['destination_template'] == 'template1' ) {
	    $template = 'single-destination-template1.php';
	}
}

// Get header
get_header();

?>
<div class="row_site">
	<div class="container_site">
		<div class="ova_destination_single">
			<?php ovadestination_get_template( $template, [ 'id' => $id ] ); ?>
		</div>
	</div>
</div>
<div class="ova_destination_single_description">
	<?php the_content(); ?>
</div>
<?php get_footer();