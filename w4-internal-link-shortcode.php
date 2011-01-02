<?php
/*
Plugin Name: W4 Internal Link Shortcode
Plugin URI: http://w4dev.com/w4-plugin/w4-internal-link-shortcode/
Description: Lets you embed links in a post/page/category description of any post,page,category and author url.
Version: 1.2
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'W4ILS_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4ILS_URL', plugin_dir_url(__FILE__)) ;
define( 'W4ILS_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4ILS_VERSION', '1.2' ) ;
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
							'after' 	=> '',
							'target'	=> false
							);
		//Parsing attributes from shortcode by wp method
		$w4ils_attr = shortcode_parse_atts($matches[1]);
		$w4ils_attr = wp_parse_args( $w4ils_attr, $w4ils_default_attr);
		
		//Supported link types
		$w4ils_default_types = array(
								'post' => array('p','P','post','posts','page','pages'),
								'cat' => array('c','C','cat','cats','category'),
								'author' => array('a','A','auth','aothor','author')
								);
		$w4ils_all_types = array_merge($w4ils_default_types['post'], $w4ils_default_types['cat'], $w4ils_default_types['author']);
		
		
		extract($w4ils_attr, EXTR_SKIP) ;

		if(!in_array($type, $w4ils_all_types))
			$type = 'post' ;
		
		$type = strtolower($type);

		if($name)
			$name = stripslashes($name);
		
		//Link target
		$w4ils_target_attrs = array('tr','Tr','tar','targat','_target','terget','openin','newpage');
		if(!$target){
			foreach($w4ils_target_attrs as $w4ils_target_attr){
				if(isset($$w4ils_target_attr))
					$target = $$w4ils_target_attr;
			}
		}
		
		
		if(in_array( $target, array('p','parent', 'own', 'this', 'self', 'no')))
			$target = '_parent';
		
		if(in_array( $target, array('b', 'blank', 'new', 'another', 'out', 'yes')))
			$target = '_blank';

		$w4ils_target_params = array('_blank', '_parent', '_self', '_top');
		if($target == false || !in_array( $target,$w4ils_target_params))
			$target = '_parent';

		
		
		if(in_array($type, $w4ils_default_types['post'])){
			if(!$w4post = $this->get_post_by_field($id, 'ID')){
				//Get post/page by title
				$w4post = ($name)? $this->get_post_by_field($name): false;
	
				//Get post/page by name/slug
				if(!$w4post)
					$w4post = ($slug)? $this->get_post_by_field($slug, 'post_name'): false;
				
				if(!$w4post)
					return false ;
				
				$id = $w4post->ID;
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
				$cat = ($name)? get_term_by( 'name', $name, 'category' ):false;
	
			//Get category by slug
			if(!$cat->term_id)
				$cat = ($slug)? get_term_by( 'slug', $slug, 'category' ):false;
	
			if(!$cat)
				return false ;

			$id = $cat->term_id ;
	
			$link = get_category_link($id);
			if(!$text)
				$text = get_cat_name($id);
		}
		
		if(in_array($type, $w4ils_default_types['author'])){
			if(!get_userdata($id)){
				//Get author by display name
				$author = ($name)? $this->get_author_by_field($name):false;
				
				//Get author by nicename
				if($author)
					$author = ($slug)? $this->get_author_by_field($slug, 'user_nicename'):false;
	
				if(!$author)
					return false ;
				
				$id = $author->ID;
			}
			$link = get_author_posts_url($id);

			if(!$text)
				$text = get_the_author_meta('display_name', $id);
		}
		
		//Link attributes
		$attr = sprintf(__('class="%1$s" target="%2$s"'), $class, $target);
		
		$this->w4il = sprintf(__('<a %3$s href="%1$s">%2$s</a>'), $link, $text, $attr);
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