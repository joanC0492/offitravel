/**
 * Currency Selector Block
 *
 * @package woo-multi-currency
 */

( function ( blocks, element, blockEditor, i18n, components, apiFetch ) {
	'use strict';

	const { registerBlockType } = blocks;
	const { useBlockProps, InspectorControls } = blockEditor;
	const { createElement: el, Fragment, useState, useEffect } = element;
	const { __ } = i18n;
	const { SelectControl, TextControl, PanelBody, Spinner, Placeholder } = components;

	// Layout options matching Elementor widget
	const layoutOptions = [
		{ value: '', label: 'Default' },
		{ value: 'plain_horizontal', label: 'Plain Horizontal' },
		{ value: 'plain_vertical', label: 'Plain Vertical' },
		{ value: 'plain_vertical_2', label: 'Listbox currency code' },
		{ value: 'layout3', label: 'List Flag Horizontal' },
		{ value: 'layout4', label: 'List Flag Vertical' },
		{ value: 'layout5', label: 'List Flag + Currency Code' },
		{ value: 'layout6', label: 'Horizontal Currency Symbols' },
		{ value: 'layout9', label: 'Horizontal Currency Slide' },
		{ value: 'layout7', label: 'Vertical Currency Symbols' },
		{ value: 'layout8', label: 'Vertical Currency Symbols (circle)' },
	];

	// Track loaded assets
	let assetsLoaded = {
		styles: false,
		scripts: false
	};

	// Load external assets
	const loadAssets = function( assets ) {
		// Load styles
		if ( assets.styles && assets.styles.length > 0 && ! assetsLoaded.styles ) {
			assets.styles.forEach( function( url ) {
				const link = document.createElement( 'link' );
				link.rel = 'stylesheet';
				link.href = url;
				document.head.appendChild( link );
			} );
			assetsLoaded.styles = true;
		}

		// Load scripts
		if ( assets.scripts && assets.scripts.length > 0 && ! assetsLoaded.scripts ) {
			assets.scripts.forEach( function( url ) {
				const script = document.createElement( 'script' );
				script.src = url;
				script.async = false;
				document.head.appendChild( script );
			} );
			assetsLoaded.scripts = true;
		}
	};

	registerBlockType( 'villatheme/woo-multi-currency-selector', {
		title: __( 'Currency Selector', 'woo-multi-currency' ),
		description: __( 'Display currency selector for WooCommerce Multi Currency', 'woo-multi-currency' ),
		icon: 'money-alt',
		category: 'woocommerce',
		keywords: [ 'currency', 'multi currency', 'switcher' ],
		supports: {
			html: false,
			align: [ 'wide', 'full' ]
		},
		attributes: {
			layout: {
				type: 'string',
				default: ''
			},
			flagSize: {
				type: 'number',
				default: 0.6
			},
			direction: {
				type: 'string',
				default: 'bottom'
			},
			previewHtml: {
				type: 'string',
				default: ''
			}
		},
		edit: function ( props ) {
			const { attributes, setAttributes } = props;
			const [ isLoading, setIsLoading ] = useState( false );
			const blockProps = useBlockProps( {
				className: 'wmc-block-editor'
			} );

			// Fetch preview when attributes change
			useEffect( function() {
				setIsLoading( true );

				const fetchPreview = apiFetch( {
					path: '/wmc/v1/preview',
					method: 'POST',
					data: {
						layout: attributes.layout,
						flagSize: attributes.flagSize,
						direction: attributes.direction
					}
				} );

				if ( fetchPreview && typeof fetchPreview.then === 'function' ) {
					fetchPreview.then( function( response ) {
						if ( response && response.html ) {
							// Load assets first
							if ( response.assets ) {
								loadAssets( response.assets );
							}

							setAttributes( { previewHtml: response.html } );
						}
						setIsLoading( false );
					} ).catch( function( error ) {
						console.error( 'WMC Block Preview Error:', error );
						setIsLoading( false );
					} );
				} else {
					setIsLoading( false );
				}
			}, [ attributes.layout, attributes.flagSize, attributes.direction ] );

			const previewContent = isLoading
				? el( 'div', { className: 'wmc-block-loading' },
					el( Spinner ),
					__( 'Loading preview...', 'woo-multi-currency' )
				)
				: attributes.previewHtml
					? el( 'div', {
						className: 'wmc-block-preview',
						dangerouslySetInnerHTML: { __html: attributes.previewHtml }
					} )
					: el( 'div', { className: 'wmc-block-placeholder' },
						el( Placeholder, {
							icon: 'money-alt',
							label: __( 'Currency Selector', 'woo-multi-currency' )
						},
							__( 'Configure the block settings to see the preview', 'woo-multi-currency' )
						)
					);

			return el(
				Fragment,
				null,
				el(
					InspectorControls,
					null,
					el(
						PanelBody,
						{ title: __( 'Currency Selector Settings', 'woo-multi-currency' ), initialOpen: true },
						el(
							SelectControl,
							{
								label: __( 'Layout', 'woo-multi-currency' ),
								value: attributes.layout,
								options: layoutOptions,
								onChange: function ( value ) {
									setAttributes( { layout: value } );
								}
							}
						),
						el(
							TextControl,
							{
								type: 'number',
								label: __( 'Flag Size', 'woo-multi-currency' ),
								value: attributes.flagSize,
								min: 0.1,
								max: 2,
								step: 0.1,
								onChange: function ( value ) {
									setAttributes( { flagSize: parseFloat( value ) || 0.6 } );
								}
							}
						),
						el(
							SelectControl,
							{
								label: __( 'Direction', 'woo-multi-currency' ),
								value: attributes.direction,
								options: [
									{ value: 'bottom', label: __( 'Bottom', 'woo-multi-currency' ) },
									{ value: 'top', label: __( 'Top', 'woo-multi-currency' ) }
								],
								onChange: function ( value ) {
									setAttributes( { direction: value } );
								}
							}
						)
					)
				),
				el( 'div', blockProps, previewContent )
			);
		},
		save: function () {
			// Static block - rendering handled by PHP
			return null;
		}
	} );
} )(
	window.wp.blocks,
	window.wp.element,
	window.wp.blockEditor,
	window.wp.i18n,
	window.wp.components,
	window.wp.apiFetch
);