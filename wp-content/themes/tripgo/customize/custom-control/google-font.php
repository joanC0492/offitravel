<?php if (!defined( 'ABSPATH' )) { exit; }
if ( ! class_exists( 'WP_Customize_Control' ) ) return NULL;
/**
 * Googe Font Select Custom Control
 *
 * @author Anthony Hortin <http://maddisondesigns.com>
 * @license http://www.gnu.org/licenses/gpl-2.0.html
 * @link https://github.com/maddisondesigns
 */
class Tripgo_Google_Font_Select_Custom_Control extends WP_Customize_Control {
	/**
	 * The type of control being rendered
	 */
	public $type = 'google_fonts';
	/**
	 * The list of Google Fonts
	 */
	private $fontList = false;
	/**
	 * The saved font values decoded from json
	 */
	private $fontValues = [];
	/**
	 * The index of the saved font within the list of Google fonts
	 */
	private $fontListIndex = 0;
	/**
	 * The number of fonts to display from the json file. Either positive integer or 'all'. Default = 'all'
	 */
	private $fontCount = 'all';
	/**
	 * The font list sort order. Either 'alpha' or 'popular'. Default = 'alpha'
	 */
	private $fontOrderBy = 'alpha';
	/**
	 * Get our list of fonts from the json file
	 */
	public function __construct( $manager, $id, $args = array(), $options = array() ) {
		parent::__construct( $manager, $id, $args );
		// Get the font sort order
		if ( isset( $this->input_attrs['orderby'] ) && strtolower( $this->input_attrs['orderby'] ) === 'popular' ) {
			$this->fontOrderBy = 'popular';
		}
		// Get the list of Google fonts
		if ( isset( $this->input_attrs['font_count'] ) ) {
			if ( 'all' != strtolower( $this->input_attrs['font_count'] ) ) {
				$this->fontCount = ( abs( (int) $this->input_attrs['font_count'] ) > 0 ? abs( (int) $this->input_attrs['font_count'] ) : 'all' );
			}
		}
		$this->fontList = $this->ova_getGoogleFontsx( 'all' );

		$this->get_all_fonts = $this->ova_getGoogleFont( 'all' );

		// Decode the default json font value
		$this->fontValues = json_decode( $this->value() );
		// Find the index of our default font within our list of Google fonts
		$this->fontListIndex = $this->Ova_getFontIndex( $this->fontList, $this->fontValues->font );

	}

		

	/**
	 * Enqueue our scripts and styles
	 */
	public function enqueue() {
		
		wp_enqueue_script( 'ova-custom-controls', TRIPGO_URI . '/customize/assets/customizer.js', array( 'jquery' ), '1.0', true );
		wp_enqueue_style( 'ova-custom-controls', TRIPGO_URI . '/customize/assets/customizer.css', array(), '1.1', 'all' );
		

	}
	/**
	 * Export our List of Google Fonts to JavaScript
	 */
	public function to_json() {
		parent::to_json();
		$this->json['ovafontslist'] = $this->fontList;
	}
	/**
	 * Render the control in the customizer
	 */
	public function render_content() {
		$fontCounter = 0;
		$isFontInList = false;
		$fontListStr = '';


		if( !empty($this->fontList) ) {
			?>
			<div class="google_fonts_select_control">
				<?php if( !empty( $this->label ) ) { ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php } ?>
				<?php if( !empty( $this->description ) ) { ?>
					<span class="customize-control-description"><?php echo esc_html( $this->description ); ?></span>
				<?php } ?>
				<input type="hidden" id="<?php echo esc_attr( $this->id ); ?>" name="<?php echo esc_attr( $this->id ); ?>" value="<?php echo esc_attr( $this->value() ); ?>" class="customize-control-google-font-selection" <?php $this->link(); ?> />
				<div class="google-fonts">
					<select class="google-fonts-list" control-name="<?php echo esc_attr( $this->id ); ?>">
						<?php
							foreach( $this->get_all_fonts as $key => $value ) {
								$fontCounter++;
								$fontListStr .= '<option value="' . $value->family . '" ' . selected( $this->fontValues->font, $value->family, false ) . '>' . $value->family . '</option>';
								if ( $this->fontValues->font === $value->family ) {
									$isFontInList = true;
								}
								if ( is_int( $this->fontCount ) && $fontCounter === $this->fontCount ) {
									break;
								}
							}
							if ( !$isFontInList && $this->fontListIndex ) {
								// If the default or saved font value isn't in the list of displayed fonts, add it to the top of the list as the default font
								$fontListStr = '<option value="' . $this->get_all_fonts[$this->fontListIndex]->family . '" ' . selected( $this->fontValues->font, $this->get_all_fonts[$this->fontListIndex]->family, false ) . '>' . $this->get_all_fonts[$this->fontListIndex]->family . ' (default)</option>' . $fontListStr;
							}
							// Display our list of font options
							echo ''.$fontListStr;
						?>
					</select>
				</div>

				<div class="customize-control-description"><?php esc_html_e( 'Load Font Weight', 'tripgo' ); ?></div>
				<div class="weight-style">
					<select multiple="multiple" class="google-fonts-regularweight-style">
						<?php 
							foreach( $this->fontList[$this->fontListIndex]->variants as $key => $value ) {
								$selected = ( $this->fontValues->regularweight && strpos( $this->fontValues->regularweight, $value) !== false ) ? 'selected="selected"' : '';
								echo '<option value="' . $value . '" ' . $selected . '>' . $value . '</option>';
							}
						?>
					</select>
				</div>

				<input type="hidden" class="google-fonts-category" value="<?php echo esc_attr( $this->fontValues->category ); ?>">
			</div>
			<?php
		}
	}

	/**
	 * Find the index of the saved font in our multidimensional array of Google Fonts
	 */
	public function Ova_getFontIndex( $haystack, $needle ) {
		if ( !empty( $haystack ) && is_array( $haystack ) ) {
			foreach ( $haystack as $key => $value ) {
				if ( $value->family == $needle ) {
					return $key;
				}
			}
		}
		
		return false;
	}

	/**
	 * Return the list of Google Fonts from our json file. Unless otherwise specfied, list will be limited to 30 fonts.
	 */
	public function ova_getGoogleFontsx( $count = 30 ) {
		// Google Fonts json generated from https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=YOUR-API-KEY
		$fontFile = TRIPGO_URI . '/customize/custom-control/api/google-fonts-alphabetical.json';
		if ( $this->fontOrderBy === 'popular' ) {
			$fontFile =  TRIPGO_URI . '/customize/custom-control/api/google-fonts-popularity.json' ;
		}

		// Get fonts
		$request = wp_remote_get( $fontFile );
		if ( is_wp_error( $request ) ) return [];

		// Get body
		$body 		= wp_remote_retrieve_body( $request );
		$content 	= json_decode( $body );

		// Check font items
		if ( !isset( $content->items ) || !is_array( $content->items ) ) return [];

		// All fonts
		$all_fonts = $content->items;

		// Custom font
        if ( '' != get_theme_mod( 'ova_custom_font', '["HK Grotesk", "300:400:500:600:700:800:900"]' ) ) {
        	$list_custom_font = explode( '|', get_theme_mod( 'ova_custom_font', '["HK Grotesk", "300:400:500:600:700:800:900"]' ) );

        	foreach ( $list_custom_font as $key => $font ) {
        		$cus_font 			= json_decode( $font );
	            $cus_font_family 	= $cus_font['0'];
	            $cus_font_weight 	= explode( ':', $cus_font['1'] );

	            $all_fonts[] = json_decode( json_encode([
	            	"kind" 		=> "webfonts#webfont",
				   	"family" 	=> $cus_font_family,
				   	"category" 	=> "sans-serif",
				   	"variants" 	=> $cus_font_weight
				]));
        	}
        }

		if ( 'all' != $count ) return array_slice( $all_fonts, 0, $count );

		return $all_fonts;
	}

	public function ova_getGoogleFont( $count = 30 ) {
		// Google Fonts json generated from https://www.googleapis.com/webfonts/v1/webfonts?sort=popularity&key=YOUR-API-KEY
		$fontFile = TRIPGO_URI . '/customize/custom-control/api/google-fonts-alphabetical.json';
		if ( $this->fontOrderBy === 'popular' ) {
			$fontFile =  TRIPGO_URI . '/customize/custom-control/api/google-fonts-popularity.json' ;
		}

		// Get fonts
		$request = wp_remote_get( $fontFile );
		if ( is_wp_error( $request ) ) return [];

		// Get body
		$body 		= wp_remote_retrieve_body( $request );
		$content 	= json_decode( $body );

		// Check font items
		if ( !isset( $content->items ) || !is_array( $content->items ) ) return [];

		// Get all fonts
		$all_fonts = $content->items;

		// Custom font
        if ( '' != get_theme_mod( 'ova_custom_font', '["HK Grotesk", "300:400:500:600:700:800:900"]' ) ) {
        	$list_custom_font = explode( '|', get_theme_mod( 'ova_custom_font', '["HK Grotesk", "300:400:500:600:700:800:900"]' ) );

        	foreach ( $list_custom_font as $key => $font ) {
        		$cus_font 			= json_decode( $font );
	            $cus_font_family 	= $cus_font['0'];
	            $cus_font_weight 	= explode( ':', $cus_font['1'] );

	            $all_fonts[] = json_decode( json_encode([
	            	"family" => $cus_font_family
	            ]));
        	}
        }
        
        // Sort fonts
        sort( $all_fonts );

		if ( 'all' != $count ) return array_slice( $all_fonts, 0, $count );

		return $all_fonts;
	}
}