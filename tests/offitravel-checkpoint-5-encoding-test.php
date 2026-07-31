<?php
/**
 * Regression test for broken UTF-8 sequences in Checkpoint 5 production files.
 *
 * Run with: php tests/offitravel-checkpoint-5-encoding-test.php
 *
 * @package Offitravel
 */

$workspace = dirname( __DIR__ );
$files     = array(
	'wp-content/mu-plugins/offitravel-cabin-supplements.php',
	'wp-content/mu-plugins/offitravel-cabin-supplements-state.js',
	'wp-content/mu-plugins/offitravel-cabin-supplements-front.js',
	'wp-content/mu-plugins/offitravel-cabin-supplements-front.css',
	'wp-content/mu-plugins/offitravel-cabin-supplements.md',
	'wp-content/mu-plugins/offitravel-product-addons.php',
	'wp-content/mu-plugins/offitravel-product-addons-front.js',
);
$markers   = array(
	'UTF-8 U+00C3 mojibake marker' => "\xC3\x83",
	'UTF-8 U+00C2 mojibake marker' => "\xC3\x82",
	'UTF-8 replacement character'  => "\xEF\xBF\xBD",
);
$failures  = array();

foreach ( $files as $relative_path ) {
	$absolute_path = $workspace . DIRECTORY_SEPARATOR . str_replace( '/', DIRECTORY_SEPARATOR, $relative_path );
	$contents      = file_get_contents( $absolute_path );
	if ( false === $contents ) {
		$failures[] = $relative_path . ': could not be read';
		continue;
	}
	foreach ( $markers as $marker_name => $marker ) {
		$offset = strpos( $contents, $marker );
		if ( false !== $offset ) {
			$line       = substr_count( substr( $contents, 0, $offset ), "\n" ) + 1;
			$failures[] = sprintf( '%s:%d contains %s', $relative_path, $line, $marker_name );
		}
	}
}

if ( $failures ) {
	fwrite( STDERR, "[FAIL] Checkpoint 5 production files contain broken UTF-8 sequences.\n" . implode( "\n", $failures ) . "\n" );
	exit( 1 );
}

printf( "[PASS] %d Checkpoint 5 production files contain no broken UTF-8 markers.\n", count( $files ) );
