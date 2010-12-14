<?php
/*
Plugin Name: W4 Internal Link Shortcode
Plugin URI: http://w4dev.com/w4-plugin/w4-internal-link-shortcode/
Description: Lets you embed links in a post/page/category description of any post,page,category and author url.
Version: 1.0
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'W4ILS_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4ILS_URL', plugin_dir_url(__FILE__)) ;
define( 'W4ILS_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4ILS_VERSION', '1.0' ) ;
define( 'W4ILS_NAME', 'W4 Internal Link Shortcode' ) ;
define( 'W4ILS_SLUG', strtolower(str_replace(' ', '-', W4ILS_NAME ))) ;


//HOOKS
add_filter('the_content', 'w4ils_replace_callback' ) ;
add_filter('the_excerpt', 'w4ils_replace_callback' ) ;
add_filter('get_the_content', 'w4ils_replace_callback' ) ;
add_filter('get_the_excerpt', 'w4ils_replace_callback' ) ;
add_filter('category_description',  'w4ils_replace_callback' ) ;
add_action( 'plugin_action_links_'.W4ILS_BASENAME, 'w4ils_plugin_action_links' ) ;


function w4ils_plugin_action_links( $links ){
	$readme_link['readme'] = '<a href="http://w4dev.com/w4-plugin/w4-internal-link-shortcode/">'.esc_html( __( 'How to use' )).'</a>';
	return array_merge( $links, $readme_link );
}
//Retrive and replace links
function w4ils_replace( $matches ){
	//print_r(shortcode_parse_atts($matches[1]));
	
	$w4ils_default = array( 'id' => null, 'type' => 'post', 'name' => '', 'slug' => '', 'text' => '', 'class' => 'w4ils_link' );
	$w4ils_args = shortcode_parse_atts($matches[1]);
	$w4ils_args = wp_parse_args( $w4ils_args, $w4ils_default);
	
	//print_r($w4ils_args);
	$w4ils_default_types = array( 'post', 'page', 'cat', 'author' ) ;
	extract($w4ils_args, EXTR_SKIP) ;
	if(!in_array($type, $w4ils_default_types)){
		$type = 'post' ;
	}
	
	if($name){
		$name = stripslashes( $name ) ;
	}
	
	if($text){
		$text = stripslashes( $text ) ;
	}
	//print_r($name);
	if($type == 'post' || $type == 'page'){
		if(!$w4post = w4ils_get_post_by_field($id, 'ID')){

			//Get post/page by title
			$w4post = w4ils_get_post_by_field($name);

			//Get post/page by name/slug
			if(!$w4post)
				$w4post = w4ils_get_post_by_field($slug, 'post_name');
			
			$id = $w4post->ID;
			if(!$id)
				return false ;
		}
		$link = get_permalink($id);
		if(!$text) $text = get_the_title($id) ;
	}
	
	if($type == 'cat'){
		//Get category by id
		$cat = get_term_by( 'id', $id, 'category' );

		//Get category by name
		if(!$cat->term_id)
			$cat = get_term_by( 'name', $name, 'category' );

		//Get category by slug
		if(!$cat->term_id)
			$cat = get_term_by( 'slug', $slug, 'category' );

			$id = $cat->term_id ;
			if(!$id)
				return false ;

		$link = get_category_link($id);
		if(!$text)	$text = get_cat_name($id);
	}
	
	if($type == 'author'){
		if(!get_userdata($id)){
			//Get author by display name
			$author = w4ils_get_author_by_field($name);
			
			//Get author by nicename
			if($author)
				$author = w4ils_get_author_by_field($slug, 'user_nicename');

			$id = $author->ID;
			if(!$id)
				return false ;
		}
		$link = get_author_posts_url($id);
		if(!$text)	$text = get_the_author_meta('display_name', $id);
	}

	if($link != '' && $text != '')
		return sprintf(__('<a class="%3$s" href="%1$s">%2$s</a>'), $link, $text, $class);
	return false ;
	
}

//Retrive and Replace shortcode
function w4ils_replace_callback($text){
	$pattern = '/\[\s*intlink\s*(.*?)\s*\]/sm' ;
	return preg_replace_callback( $pattern, 'w4ils_replace', $text ) ;
}

//Custom post query function
function w4ils_get_post_by_field($val = '', $key = 'post_title', $output = OBJECT) {
    global $wpdb;
        $post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE $key = %s AND post_type IN ('post','page') AND post_status = 'publish' LIMIT 1", $val ));
        if ( $post )
            return get_post($post, $output);

    return false;
}

//Custom author query function
function w4ils_get_author_by_field($val = '', $key = 'display_name', $output = OBJECT) {
    global $wpdb;
        $author_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE $key = %s LIMIT 1", $val ));
        if ( $author_id )
            return get_userdata($author_id);

    return false;
}
?>