<?php 

if( ! ( class_exists( 'woocommerce' ) && is_woocommerce() ) ){
    if( get_post_meta(  tripgo_get_current_id() ,'tripgo_met_show_breadcrumbs', true ) != 'no' ){

    		$separator = '<li class="li_separator"><span class="separator"><i class="ovaicon-next"></i></span></li>';

    		$html = '<div id="breadcrumbs">';
				$html .= '<ul class="breadcrumb">';

					global $post;
			        
			        $html .= '<li><a href="' . esc_url( home_url('/') ) . '" title="'.esc_attr__('Inicio', 'tripgo').'">' . esc_attr__('Inicio', 'tripgo') . '</a></li> ';
			        
			        
			        if ( is_category() ) {

			        	global $wp_query;
				        
				        $cat_obj = $wp_query->get_queried_object();
				        $thisCat = $cat_obj->term_id;
				        $thisCat = get_category($thisCat);
				        $parentCat = get_category($thisCat->parent);

				        if ($thisCat->parent != 0) $html .=  $separator.'<li>'.get_category_parents($parentCat, TRUE, ' ').'</li>';
				        
				        $html .= $separator.'<li>' . single_cat_title('', false) . '</li>';



			        } elseif ( is_day() ) {

				        $html .= $separator.'<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>';
				        $html .= $separator.'<li><a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a></li>';
				        $html .= $separator.'<li>'.esc_html__('Archive by date', 'tripgo').' '.get_the_time('d').'</li>';

			        } elseif ( is_month() ) {

				        $html .= $separator.'<li><a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a></li>';
				        $html .= $separator.'<li>' . esc_html__('Archive by month', 'tripgo').' ' . get_the_time('F') . '</li>';

			        } elseif ( is_year() ) {

			        	$html .= $separator.'<li>' . esc_html__('Archive by year', 'tripgo').' ' . get_the_time('Y') . '</li>';

			        } elseif ( is_single() && !is_attachment() ) {

				        if ( get_post_type() != 'post' ) {
					        
					        $post_type = get_post_type_object(get_post_type());

					        if( $post_type->rewrite && get_option('permalink_structure') ){
					        	
					        	$html .= $separator.'<li><a href="' . esc_url( home_url('/') ).$post_type->rewrite['slug'].'">' . $post_type->labels->singular_name . '</a></li>';	
					        }
					        
					        $html .= $separator.'<li>' . get_the_title() . '</li>';

				        } else {

				        	$cat = get_the_category(); 
					        $cat = isset( $cat[0] ) ? $cat[0] : '';
					        if( $cat ){
					        	$html .= $separator.'<li>'.get_category_parents( $cat, TRUE, '' ).'</li>';
					        	$html .=  $separator.'<li>' . get_the_title() . '</li>';	
					        }
					       
				        }

			        }elseif ( is_search()) {

			            $html .=  $separator.'<li>' . esc_html__('Search results for', 'tripgo').' ' . get_search_query() . '</li>';

			        }elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {

				        $post_type = get_post_type_object(get_post_type());

				        if ( is_archive() && single_term_title('', false) ) {
				        	$html .= single_term_title('', false) ? $separator.'<li>' . single_term_title('', false) . '</li>' : '';
				        } else {
				        	$html .= $post_type ?  $separator.'<li>' . $post_type->labels->singular_name . '</li>' : '';
				        }

			        } elseif ( is_attachment() ) {

				        $parent_id  = $post->post_parent;
				        $breadcrumbs = array();
				        while ($parent_id) {
					        $page = get_page($parent_id);
					        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
					        $parent_id    = $page->post_parent;
				        }
				        $breadcrumbs = array_reverse($breadcrumbs);

				        foreach ($breadcrumbs as $crumb) $html .=  $separator .'<li>'. $crumb.'</li>';

				        $html .=  $separator. '<li>' . get_the_title() . '</li>';

			        }elseif ( is_page() && !$post->post_parent ) {

			        	$html .=  $separator. '<li>' . get_the_title() . '</li>';

			        } elseif ( is_page() && $post->post_parent ) {

				        $parent_id  = $post->post_parent;
				        $breadcrumbs = array();
				        while ($parent_id) {
					        $page = get_page($parent_id);
					        $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
					        $parent_id    = $page->post_parent;
				        }
				        $breadcrumbs = array_reverse($breadcrumbs);

				        foreach ($breadcrumbs as $crumb) $html .=  $separator .'<li>'. $crumb.'</li>';

			        	$html .= $separator.'<li>' . get_the_title() . '</li>';

			        }elseif ( is_tag() ) {

			        	$html .= $separator.'<li>' . esc_html__('Archive by tag', 'tripgo').' ' . single_tag_title('', false) . '</li>';

			        } elseif ( is_author() ) {

			        	global $author;
			        	$userdata = get_userdata($author);
			        	$html .= $separator.'<li>' . esc_html__('Articles posted by', 'tripgo').' ' . $userdata->display_name . '</li>';

			        } elseif ( is_home() ){

			        	$html .= $separator.'<li>' . esc_html__('Blog', 'tripgo').'&nbsp;' . '</li>';

			        }elseif ( is_404() ) {

			        	$html .= $separator.'<li>' . esc_html__('Page not found', 'tripgo') . '</li>';

			        }

			       

				$html .= '</ul>';
					      
			$html .= '</div>';

			$args = array(
			    'a' => array(
			        'href' => array(),
			        'title' => array()
			    ),
			    'div'	=> array(
			    	'id'	=> array(),
			    	'class'	=> array(),
			    ),
			    'ul'	=> array(
			    	'id'	=> array(),
			    	'class'	=> array(),
			    ),
			    'li'	=> array(
			    	'id'	=> array(),
			    	'class'	=> array(),
			    ),
			    'span'	=> array(
			    	'id'	=> array(),
			    	'class'	=> array(),
			    ),
			    'i'	=> array(
			    	'class'	=> array(),
			    ),
			    'br' => array(),
			    'em' => array(),
			    'strong' => array(),
			   
			);

			echo wp_kses( $html, $args );
				        
				    }
}else{
    $args = array(
        'delimiter' => '<li><span class="separator"><i class="ovaicon-next"></i></span></li>',
        'wrap_before' => '<div id="breadcrumbs" ><ul class="breadcrumb">',
        'wrap_after' => '</ul></div>',
        'before' => '<li>',
        'after' => '</li>',
        'home' => esc_html__( 'Home', 'tripgo' )
    );
     woocommerce_breadcrumb( $args );
}

