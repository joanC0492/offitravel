jQuery(document).ready(function( $ ){
    $('#wpmc-main_color').wpColorPicker();

	/*
	 * Toggle the step title strings
	 */
    toggle_wpml();
    $('#t_wpml').on( 'change', toggle_wpml );

    function toggle_wpml() {
        var all_text = '#t_login, #t_billing, #t_shipping, #t_order, #t_payment, #t_back_to_cart, #t_skip_login, #t_previous, #t_next'; 
        if ($('#t_wpml').is(':checked') ) {
            $(all_text).prop('disabled', true);
        } else {
            $(all_text).prop('disabled', false);
        }
    }

	/*
	 * Toggle the "Hide Shipping step for virtual products" option
	 */
	toggle_shipping_virtual_products( $('#show_shipping_step').is(':checked') );

	$('input[name="show_shipping_step"]').change( function() {
		toggle_shipping_virtual_products( $(this).is(':checked') );
	});

	function toggle_shipping_virtual_products( enable = true ) {
		let form_group = $('#hide_shipping_step_virtual').closest('.form-group');
		if ( enable ) {
			form_group.show('slow');
		} else {
			form_group.hide('slow');
		}
	}


	/*
	 * Hide alerts, when the "dismiss" icon is clicked
	 */
	$('div.alert button.close').on('click', function() {
		$(this).parent().hide();
	} );
});
