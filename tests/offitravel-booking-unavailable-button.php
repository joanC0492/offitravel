<?php

$template = __DIR__ . '/../wp-content/themes/tripgo-child/woocommerce/rental/loop/forms.php';
$source   = file_get_contents( $template );
$css      = file_get_contents( __DIR__ . '/../wp-content/themes/tripgo-child/style.css' );

if ( false === $source || false === $css ) {
	fwrite( STDERR, "No se pudo leer el template de formularios o el CSS.\n" );
	exit( 1 );
}

$checks = array(
	'boton de presupuesto'       => 'Solicita ya tu presupuesto',
	'mensaje no disponible'      => 'La salida aún no está disponible.',
	'estilo de boton de reserva' => 'booking-form-submit offitravel-budget-request-button',
	'spinner del boton'          => 'ovabrw-submit-loading',
	'enlace a contacto'          => "home_url( '/contacto/' )",
);

foreach ( $checks as $label => $needle ) {
	if ( false === strpos( $source, $needle ) ) {
		fwrite( STDERR, "Falta {$label}: {$needle}\n" );
		exit( 1 );
	}
}

foreach ( array( '.offitravel-budget-request-button', 'background-color', 'display: none' ) as $needle ) {
	if ( false === strpos( $css, $needle ) ) {
		fwrite( STDERR, "Falta CSS para el boton de presupuesto: {$needle}\n" );
		exit( 1 );
	}
}

echo "OK: la salida no disponible muestra mensaje y boton de presupuesto.\n";
