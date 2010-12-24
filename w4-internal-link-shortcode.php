<?php
/*
Plugin Name: W4 Internal Link Shortcode
Plugin URI: http://w4dev.com/w4-plugin/w4-internal-link-shortcode/
Description: Lets you embed links in a post/page/category description of any post,page,category and author url.
Version: 1.1
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'W4ILS_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4ILS_URL', plugin_dir_url(__FILE__)) ;
define( 'W4ILS_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4ILS_VERSION', '1.1' ) ;
define( 'W4ILS_NAME', 'W4 Internal Link Shortcode' ) ;
define( 'W4ILS_SLUG', strtolower(str_replace(' ', '-', W4ILS_NAME ))) ;


class W4ILS{
	
	function W4ILS(){
		add_filter('the_content', array(&$this, 'w4ils_replace_callback'));
		add_filter('the_excerpt', array(&$this, 'w4ils_replace_callback'));
		add_filter('get_the_content', array(&$this, 'w4ils_replace_callback'));
		add_filter('get_the_excerpt', array(&$this, 'w4ils_replace_callback'));
		add_filter('category_description', array(&$this, 'w4ils_replace_callback'));
		add_action('plugin_action_links_'.W4ILS_BASENAME, array(&$this, 'w4ils_plugin_action_links'));
	}
	
	function w4ils_plugin_action_links( $links ){
		$readme_link['readme'] = '<a href="http://w4dev.com/w4-plugin/w4-internal-link-shortcode/">'.esc_html( __( 'How to use' )).'</a>';
		return array_merge( $links, $readme_link );
	}

	//Retrive and replace links
	function w4ils_replace( $matches ){
		//Supported attributes
		$w4ils_default_attr = array(
							'type' 		=> 'post',
							'id' 		=> '',
							'name'	 	=> '',
							'slug' 		=> '',
							'text' 		=> '',
							'class' 	=> 'w4ils_link',
							'before' 	=> '',
							'after' 	=> ''
							);
		//Parsing attributes from shortcode by wp method
		$w4ils_attr = shortcode_parse_atts($matches[1]);
		$w4ils_attr = wp_parse_args( $w4ils_attr, $w4ils_default_attr);
		
		//Supported link types
		$w4ils_default_types = array(
								'post' => array('p','post','posts','page','pages'),
								'cat' => array('c','cat','cats','category'),
								'author' => array('a','auth','aothor','author')
								);
		$w4ils_all_types = array_merge($w4ils_default_types['post'], $w4ils_default_types['cat'], $w4ils_default_types['author']);
		
		
		extract($w4ils_attr, EXTR_SKIP) ;

		if(!in_array($type, $w4ils_all_types))
			$type = 'post' ;
		
		$type = strtolower($type);

		if($name)
			$name = stripslashes($name);

		if(in_array($type, $w4ils_default_types['post'])){
			if(!$w4post = $this->get_post_by_field($id, 'ID')){
				//Get post/page by title
				$w4post = $this->get_post_by_field($name);
	
				//Get post/page by name/slug
				if(!$w4post)
					$w4post = $this->get_post_by_field($slug, 'post_name');
				
				$id = $w4post->ID;
				if(!get_post($id))
					return false ;
			}

			$link = get_permalink($id);
			if(!$text)
				$text = get_the_title($id) ;
		}
		
		if(in_array($type, $w4ils_default_types['cat'])){
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
			if(!$text)
				$text = get_cat_name($id);
		}
		
		if(in_array($type, $w4ils_default_types['author'])){
			if(!get_userdata($id)){
				//Get author by display name
				$author = $this->get_author_by_field($name);
				
				//Get author by nicename
				if($author)
					$author = $this->get_author_by_field($slug, 'user_nicename');
	
				$id = $author->ID;
				if(!$id)
					return false ;
			}
			$link = get_author_posts_url($id);

			if(!$text)
				$text = get_the_author_meta('display_name', $id);
		}
		
		
		$this->w4il = sprintf(__('<a class="%3$s" href="%1$s">%2$s</a>'), $link, $text, $class);
		if($before)
			$this->w4il = $before.$this->w4il;
		
		if($after)
			$this->w4il = $this->w4il.$after;

		return $this->w4il;
		return false ;
	}


	//Retrive and Replace shortcode
	function w4ils_replace_callback($text){
		$pattern = '/\[\s*intlink\s*(.*?)\s*\]/sm' ;
		return preg_replace_callback( $pattern, array(&$this, 'w4ils_replace'), $text ) ;
	}
	
	//Custom post query function
	function get_post_by_field($val = '', $key = 'post_title', $output = OBJECT) {
		global $wpdb;
			$post = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE $key = %s AND post_type IN ('post','page') AND post_status = 'publish' LIMIT 1", $val ));
			if ( $post )
				return get_post($post, $output);
	
		return false;
	}
	
	//Custom author query function
	function get_author_by_field($val = '', $key = 'display_name', $output = OBJECT) {
		global $wpdb;
			$author_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE $key = %s LIMIT 1", $val ));
			if ( $author_id )
				return get_userdata($author_id);
	
		return false;
	}

}//class W4ILS ends
$W4ILS = new W4ILS();
?>