<?php if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class Ova_Megamenu_Walker_Nav_Menu_Edit_Custom
 */
class Ova_Megamenu_Walker_Nav_Menu_Edit_Custom extends Walker_Nav_Menu {

	function start_lvl( &$output, $depth = 0, $args = [] ) {}
    function end_lvl( &$output, $depth = 0, $args = [] ) {}
	
	function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
        global $_wp_nav_menu_max_depth;

        $_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;

        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $item_id = esc_attr( $item->ID );
        $removed_args = [
        	'action',
            'customlink-tab',
            'edit-menu-item',
            'menu-item',
            'page-tab',
            '_wpnonce'
        ];
        ob_start();
        $original_title = '';
        if ( 'taxonomy' == $item->type ) {
            $original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
            if ( is_wp_error( $original_title ) )
                $original_title = false;
        } elseif ( 'post_type' == $item->type ) {
            $original_object = get_post( $item->object_id );
            $original_title = $original_object->post_title;
        }

        $classes = [
        	'menu-item menu-item-depth-' . $depth,
            'menu-item-' . esc_attr( $item->object ),
            'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive' )
        ];

        $title = $item->title;

        if ( !empty( $item->_invalid ) ) {
            $classes[] = 'menu-item-invalid';
            /* translators: %s: title of menu item which is invalid */
            $title = sprintf( '%s (Invalid)', $item->title );
        } elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
            $classes[] = 'pending';
            /* translators: %s: title of menu item in draft status */
            $title = sprintf( '%s (Pending)', $item->title );
        }
        $title = empty( $item->label ) ? $title : $item->label;

		?>
		<li id="menu-item-<?php echo esc_attr( $item_id ); ?>" class="<?php echo implode(' ', $classes ); ?>">
			<dl class="menu-item-bar">
			<dt class="menu-item-handle">
				<span class="item-title"><?php echo esc_html( $title ); ?></span>
				<span class="item-controls">
					<span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
					<span class="item-order hide-if-js">
						<a href="<?php
							echo wp_nonce_url(
								add_query_arg(
									[
										'action' 	=> 'move-up-menu-item',
										'menu-item' => $item_id
									],
									remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
								),
								'move-menu_item'
							);
							?>" class="item-move-up">
							<abbr title="<?php esc_html_e( 'Move up','ova-megamenu' ); ?>">&#8593;</abbr>
						</a>
						|
						<a href="<?php
							echo wp_nonce_url(
								add_query_arg(
									[
										'action' 	=> 'move-down-menu-item',
										'menu-item' => $item_id
									],
									remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
								),
								'move-menu_item'
							);
							?>" class="item-move-down">
							<abbr title="<?php esc_html_e('Move down','ova-megamenu'); ?> ">&#8595;</abbr>
						</a>
					</span>
					<a class="item-edit" id="edit-<?php echo esc_attr( $item_id ); ?>" title="<?php esc_html_e( 'Edit Menu Item', 'ova-megamenu' ); ?>" href="<?php
						echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] )
							? admin_url( 'nav-menus.php' )
							: add_query_arg( 'edit-menu-item', $item_id,
							remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
						?>">
						
					</a>
				</span>
			</dt>
			</dl>
			<div class="menu-item-settings ova-menu-item-settings" id="menu-item-settings-<?php echo esc_attr( $item_id ); ?>" style="width: 100%;">
				<?php if ( 'custom' == $item->type ): ?>
					<p class="description description-wide">
						<label for="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>">
							<?php esc_html_e( 'URL', 'ova-megamenu' ); ?><br />
							<input
								type="text"
								id="edit-menu-item-url-<?php echo esc_attr( $item_id ); ?>"
								class="widefat code edit-menu-item-url"
								name="menu-item-url[<?php echo esc_attr( $item_id ); ?>]"
								value="<?php echo esc_attr( $item->url ); ?>"
								data-name="menu-item-url[<?php echo esc_attr( $item_id ); ?>]"
							/>
						</label>
					</p>
				<?php endif; ?>
				<p class="description description-wide">
					<label for="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Navigation Label', 'ova-megamenu' ); ?><br />
						<input
							type="text"
							id="edit-menu-item-title-<?php echo esc_attr( $item_id ); ?>"
							class="widefat edit-menu-item-title"
							name="menu-item-title[<?php echo esc_attr( $item_id ); ?>]"
							value="<?php echo esc_attr( $item->title ); ?>"
							data-name="menu-item-title[<?php echo esc_attr( $item_id ); ?>]"
						/>
					</label>
				</p>
				<p class="description">
					<label for="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>">
						<input
							type="checkbox"
							id="edit-menu-item-target-<?php echo esc_attr( $item_id ); ?>"
							value="_blank"
							name="menu-item-target[<?php echo esc_attr( $item_id ); ?>]"
							data-name="menu-item-target[<?php echo esc_attr($item_id); ?>]"
							<?php checked( $item->target, '_blank' ); ?>
						/>
						<?php esc_html_e( 'Open link in a new window/tab', 'ova-megamenu' ); ?>
					</label>
				</p>
				<p class="description description-wide">
					<label for="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Title Attribute', 'ova-megamenu' ); ?><br />
						<input
							type="text"
							id="edit-menu-item-attr-title-<?php echo esc_attr( $item_id ); ?>"
							class="widefat edit-menu-item-attr-title"
							name="menu-item-attr-title[<?php echo esc_attr( $item_id ); ?>]"
							value="<?php echo esc_attr( $item->post_excerpt ); ?>"
							data-name="menu-item-attr-title[<?php echo esc_attr( $item_id ); ?>]"
						/>
					</label>
				</p>
				<p class="description description-thin">
					<label for="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'CSS Classes (optional)', 'ova-megamenu' ); ?><br />
						<input
							type="text"
							id="edit-menu-item-classes-<?php echo esc_attr( $item_id ); ?>"
							class="widefat code edit-menu-item-classes"
							name="menu-item-classes[<?php echo esc_attr( $item_id ); ?>]"
							data-name="menu-item-classes[<?php echo esc_attr($item_id); ?>]"
							value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>"
						/>
					</label>
				</p>
				<p class="description description-thin">
					<label for="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Link Relationship (XFN)', 'ova-megamenu' ); ?><br />
						<input
							type="text"
							id="edit-menu-item-xfn-<?php echo esc_attr( $item_id ); ?>"
							class="widefat code edit-menu-item-xfn"
							name="menu-item-xfn[<?php echo esc_attr( $item_id ); ?>]"
							data-name="menu-item-xfn[<?php echo esc_attr( $item_id ); ?>]"
							value="<?php echo esc_attr( $item->xfn ); ?>"
						/>
					</label>
				</p>
				<?php if ( $depth == 0 ): ?>	
					<p class="description description-wide">
						<label for="edit-menu-item-type-menu-<?php echo esc_attr( $item_id ); ?>">
							<?php esc_html_e( 'Menu Column (for mega menu)', 'ova-megamenu' ); ?><br />
							<select
								id="edit-menu-item-type-menu-<?php echo esc_attr( $item_id ); ?>"
								name="menu-item-menu_column[<?php echo esc_attr( $item_id ); ?>]"
								data-name="menu-item-menu_column[<?php echo esc_attr( $item_id ); ?>]"
								style="width: 100%;"
							>
								<option value=""<?php selected( $item->menu_column, '' ); ?>>
									<?php echo esc_html_e( 'Select', 'ova-megamenu' ); ?>
								</option>
								<option value="one-column"<?php selected( $item->menu_column, 'one-column' ); ?>>
									<?php esc_html_e( 'One Column', 'ova-megamenu' ); ?>
								</option>
								<option value="two-columns"<?php selected( $item->menu_column, 'two-columns' ); ?>>
									<?php esc_html_e( 'Two Columns', 'ova-megamenu' ); ?>
								</option>
								<option value="three-columns"<?php selected( $item->menu_column, 'three-columns' ); ?>>
									<?php esc_html_e( 'Three Columns', 'ova-megamenu' ); ?>
								</option>
								<option value="four-columns"<?php selected( $item->menu_column, 'four-columns' ); ?>>
									<?php esc_html_e( 'Four Columns', 'ova-megamenu' ); ?>
								</option>
								<option value="five-columns"<?php selected( $item->menu_column, 'five-columns' ); ?>>
									<?php esc_html_e( 'Five Columns', 'ova-megamenu' ); ?>
								</option>
							</select>
						</label>
					</p>
				<?php elseif ( $depth == 1 ): ?>
					<p class="description description-wide">
						<label for="edit-menu-item-menu_height-<?php echo esc_attr( $item_id ); ?>">
							<?php echo esc_html_e( 'Menu height (for mega menu). Ex: 830px', 'ova-megamenu' ); ?>	
							<input
								type="text"
								id="edit-menu-item-menu_height-<?php echo esc_attr( $item_id ); ?>"
								class="widefat code edit-menu-item-menu_height"
								name="menu-item-menu_height[<?php echo esc_attr( $item_id ); ?>]"
								data-name="menu-item-menu_height[<?php echo esc_attr( $item_id ); ?>]"
								value="<?php echo esc_attr( $item->menu_height ); ?>"
							/>
						</label>
					</p>
					<p class="shortcode shortcode-wide">
						<label for="edit-menu-item-shortcode_megamenu-<?php echo esc_attr( $item_id ); ?>">
							<?php echo esc_html_e( 'Shortcode', 'ova-megamenu' ); ?>	
							<input
								type="text"
								id="edit-menu-item-shortcode_megamenu-<?php echo esc_attr( $item_id ); ?>"
								class="widefat code edit-menu-item-shortcode_megamenu"
								name="menu-item-shortcode_megamenu[<?php echo esc_attr( $item_id ); ?>]"
								data-name="menu-item-shortcode_megamenu[<?php echo esc_attr( $item_id ); ?>]"
								value="<?php echo esc_attr( $item->shortcode_megamenu ); ?>"
							/>
						</label>
					</p>
					<p class="description">
						<label for="edit-menu-item-linkhide-<?php echo esc_attr( $item_id ); ?>">
							<input
								type="checkbox"
								id="edit-menu-item-linkhide-<?php echo esc_attr( $item_id ); ?>"
								class="code edit-menu-item-custom"
								value="linkhide"
								name="menu-item-linkhide[<?php echo esc_attr( $item_id ); ?>]"
								data-name="menu-item-linkhide[<?php echo esc_attr( $item_id ); ?>]"
								<?php checked( $item->linkhide, 'linkhide' ); ?>
							/>
							<?php esc_html_e( 'Show as a heading', 'ova-megamenu' ); ?>
						</label>
					</p>
				<?php endif; ?>
				<br/>
				<div class="menu-item-actions description-wide submitbox">
					<?php if ( 'custom' != $item->type && $original_title !== false ): ?>
						<p class="link-to-original">
							<?php printf( 'Original: %s', '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
						</p>
					<?php endif; ?>
					<a class="item-delete submitdelete deletion" id="delete-<?php echo esc_attr( $item_id ); ?>" href="<?php
						echo wp_nonce_url(
							add_query_arg(
								[
									'action' 	=> 'delete-menu-item',
									'menu-item' => $item_id
								],
								remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
							),
							'delete-menu_item_' . $item_id
						); ?>">
						<?php esc_html_e( 'Remove', 'ova-megamenu' ); ?>
					</a>
					<span class="meta-sep"> | </span>
					<a class="item-cancel submitcancel" id="cancel-<?php echo esc_attr( $item_id ); ?>" href="<?php
						echo esc_url(
							add_query_arg(
								[
									'edit-menu-item' 	=> $item_id,
									'cancel' 			=> time()
								],
								remove_query_arg( $removed_args, admin_url( 'nav-menus.php' ) )
							)
						);?>#menu-item-settings-<?php echo esc_attr( $item_id ); ?>">
						<?php esc_html_e( 'Cancel', 'ova-megamenu' ); ?>
					</a>
				</div>
				<input
					type="hidden"
					class="menu-item-data-db-id"
					name="menu-item-db-id[<?php echo esc_attr( $item_id ); ?>]"
					value="<?php echo esc_attr( $item_id ); ?>"
				/>
				<input
					type="hidden"
					class="menu-item-data-object-id"
					name="menu-item-object-id[<?php echo esc_attr( $item_id ); ?>]"
					value="<?php echo esc_attr( $item->object_id ); ?>"
				/>
				<input
					type="hidden"
					class="menu-item-data-object"
					name="menu-item-object[<?php echo esc_attr( $item_id ); ?>]"
					value="<?php echo esc_attr( $item->object ); ?>"
				/>
				<input
					type="hidden"
					class="menu-item-data-parent-id"
					name="menu-item-parent-id[<?php echo esc_attr( $item_id ); ?>]"
					value="<?php echo esc_attr( $item->menu_item_parent ); ?>"
				/>
				<input
					type="hidden"
					class="menu-item-data-position"
					name="menu-item-position[<?php echo esc_attr( $item_id ); ?>]"
					value="<?php echo esc_attr( $item->menu_order ); ?>"
				/>
				<input
					type="hidden"
					class="menu-item-data-type"
					name="menu-item-type[<?php echo esc_attr( $item_id ); ?>]"
					value="<?php echo esc_attr( $item->type ); ?>"
				/>
			</div>
			<ul class="menu-item-transport"></ul>
		</li>
		<?php $output .= ob_get_clean();
	}
}

/**
 * Class Ova_Megamenu_Walker_Nav_Menu
 */
if ( !class_exists( 'Ova_Megamenu_Walker_Nav_Menu' ) ) {
    class Ova_Megamenu_Walker_Nav_Menu extends Walker_Nav_Menu {

		public $column 			= '';
		public $hideli 			= false;
		public $hideul 			= false;
		public $hideul_close 	= false;

		function display_element( $element, &$children_elements, $max_depth, $depth=0, $args = [], &$output = '' ) {
			$id_field = $this->db_fields['id'];

			$this->column 				= $element->menu_column;	
			$this->menu_height 			= $element->menu_height;
			$this->shortcode_megamenu 	= $element->shortcode_megamenu;

			if ( is_object( $args[0] ) ) {
				$args[0]->has_children = !empty( $children_elements[$element->$id_field] );
			}

			return parent::display_element( $element, $children_elements, $max_depth, $depth, $args, $output );
		}
		
		function start_lvl( &$output, $depth = 0, $args = [] ) {
			$indent 	= str_repeat("\t", $depth);
			$out_div 	= '';

			if ( $this->hideul ) {
				$this->hideul_close = true;
			} else {
				if ( !empty( $this->column ) ) {
					$output .= "\n$indent$out_div<ul class=\"ova-mega-menu sub-menu ".esc_attr( $this->column )."  dropdown-menu\"  role=\"menu\">\n";
				} else {
					$output .= "\n$indent$out_div<ul class=\"sub-menu\" role=\"menu\">\n";
				}	
			}
		}
		
		function end_lvl( &$output, $depth = 0, $args = [] ) {
			if ( $this->hideul_close ) {
				$this->hideul_close = false;
			} else {
				$output .= '</ul>';	
			}
		}
		
		function start_el( &$output, $item, $depth = 0, $args = [], $id = 0 ) {
			$indent 	= ( $depth ) ? str_repeat( "\t", $depth ) : '';
			$classes 	= empty( $item->classes ) ? [] : (array)$item->classes;
			$classes[] 	= 'menu-item-' . $item->ID;

			if ( !empty( $item->menu_column ) ) {
				$classes[] = 'dropdown ova-megamenu';
			} else {
				$classes[] = 'dropdown';
			}

			$class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item, $args, $depth ) );

			if ( in_array( 'current-menu-item', $classes ) ) {
                $class_names .= ' active';
			}

			$class_names = $class_names ? ' class="' . esc_attr( $class_names ) . '"' : '';
			
			$id = apply_filters( 'nav_menu_item_id', 'menu-item-'. $item->ID, $item, $args, $depth );
			$id = $id ? ' id="' . esc_attr( $id ) . '"' : '';
			
			$atts = [];
			$atts['title']  = !empty( trim( $item->attr_title ) ) ? $item->attr_title : $item->title;
			$atts['target'] = !empty( $item->target ) ? $item->target : '';
			$atts['rel']    = !empty( $item->xfn ) ? $item->xfn : '';
			$atts['href']   = !empty( $item->url ) ? $item->url : '';
			$atts['class'] 	= !empty( $item->classes ) ? $item->classes[0] : '';
			
			$atts = apply_filters( 'nav_menu_link_attributes', $atts, $item, $args, $depth );
		 
			$attributes = '';
			foreach ( $atts as $attr => $value ) {
				if ( !empty( $value ) ) {
					$value 		= ( 'href' === $attr ) ? esc_url( $value ) : esc_attr( $value );
					$attributes .= ' ' . $attr . '="' . $value . '"';
				}
			}
			
			if ( !$this->hideul ) {
				if ( !( empty( $item->linkhide ) ) ) {
					$this->hideul = true;
					$this->hideli = true;

					$item_output = '';
					$item_output .= '<div>';
					$item_output .= '<h5 class="title">'.esc_html( $item->title ).'</h5>';
					$item_output .= $this->shortcode_megamenu ? do_shortcode( $this->shortcode_megamenu ) : '';

					if ( !empty( $this->menu_height ) ) {
						$output .= $indent . '<li '.$id. $class_names.' style="height: '.$this->menu_height.'">';
					} else {
						$output .= $indent . '<li '.$id. $class_names.'>';
					}
				} else {
					if ( !empty( $this->menu_height ) ) {
						$output .= $indent . '<li' . $id . $class_names .' style="height: '.$this->menu_height.'">';
					} else {
						$output .= $indent . '<li' . $id . $class_names .'>';
					}

					$item_output = $args->before;
					$item_output .= '<a'. $attributes .' >';
					$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
					$item_output .= '</a>';
					
					if ( !empty( $item->xfn ) ) {
						$item_output .= '<span class="focus">'.$item->xfn.'</span>';
					}
					
					$item_output .= $args->after;
				}
			} else {
				$mega_item_active = '';
				if ( in_array( 'current-menu-item', $classes ) )
                $mega_item_active = ' active';

				$item_output  = $args->before;
				$item_output .= '<a'. $attributes.' class="' .$mega_item_active.'" >';
				$item_output .= $args->link_before . apply_filters( 'the_title', $item->title, $item->ID ) . $args->link_after;
				$item_output .= '</a>';
				$item_output .= $args->after;
			}
			
			$output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
		}
		
		function end_el( &$output, $item, $depth = 0, $args = [] ) {
			if ( !( empty( $item->linkhide ) ) ) {
				$this->hideul = false;
				$this->hideli = false;
				$output .= '</div></li>';
			} elseif ( $this->hideli ) {
				
			} else {
				$output .= '</li>';	
			}				
		}
	}
}

/**
 * Get value custom menu
 */
add_filter( 'wp_setup_nav_menu_item', function( $menu_item ) {
	$menu_item->menu_column = get_post_meta( $menu_item->ID, '_menu_item_menu_column', true );

    // Sub menu
    $menu_item->menu_height 		= get_post_meta( $menu_item->ID, '_menu_item_menu_height', true );
    $menu_item->shortcode_megamenu 	= get_post_meta( $menu_item->ID, '_menu_item_shortcode_megamenu', true );
	$menu_item->linkhide 			= get_post_meta( $menu_item->ID, '_menu_item_linkhide', true );
    
	return $menu_item;
});

/**
 * Save value custom menu
 */
add_action( 'wp_update_nav_menu_item', function( $menu_id, $menu_item_db_id, $args ) {
	$check = [ 'menu_column',  'menu_height' , 'shortcode_megamenu', 'linkhide' ];

    foreach ( $check as $key ) {
        if ( !isset( $_POST['menu-item-'.$key][$menu_item_db_id] ) ) {
            if ( !isset( $args['menu-item-'.$key] ) ) {
                $value = '';
            } else {
                $value = $args['menu-item-'.$key];
            }
        } else {
            $value = $_POST['menu-item-'.$key][$menu_item_db_id];
        } 

        update_post_meta( $menu_item_db_id, '_menu_item_'.$key, $value );
    }
}, 10, 3 );

// Render HTML custom edit menu in backend
add_filter( 'wp_edit_nav_menu_walker', function( $walker,$menu_id ) {
	return 'Ova_Megamenu_Walker_Nav_Menu_Edit_Custom';
}, 10, 2 );

/**
 * If don't choose menu location
 */
if ( !function_exists( 'ova_megamenu_menu_editor' ) ) {
	function ova_megamenu_menu_editor( $args ) {
		if ( !current_user_can( 'manage_options' ) ) return;

		// See wp-includes/nav-menu-template.php for available arguments
		extract( $args );
		$link = $link_before . '<a href="' .admin_url( 'nav-menus.php' ) . '">' . $before . esc_html__( 'Add a menu', 'ova-megamenu' ) . $after . '</a>' . $link_after;

		// We have a list
		if ( FALSE !== stripos( $items_wrap, '<ul' ) || FALSE !== stripos( $items_wrap, '<ol' ) ) {
			$link = '<li>'. $link .'</li>';
		}

		$output = sprintf( $items_wrap, $menu_id, $menu_class, $link );
		if ( !empty ( $container ) ) {
			$output  = "<$container class='$container_class' id='$container_id'>$output</$container>";
		}
		if ( $echo ) {
			echo ''.$output;
		}

		return $output;
	}
}