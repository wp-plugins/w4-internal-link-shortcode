<?php
/*
Plugin Name: W4 Internal Link Shortcode
Plugin URI: http://w4dev.com/w4-plugin/w4-internal-link-shortcode/
Description: Lets you embed links in a post/page/category description of any post,page,category and author url.
Version: 1.3
Author: Shazzad Hossain Khan
Author URI: http://w4dev.com/
*/
define( 'W4ILS_DIR', plugin_dir_path(__FILE__)) ;
define( 'W4ILS_URL', plugin_dir_url(__FILE__)) ;
define( 'W4ILS_BASENAME', plugin_basename( __FILE__ )) ;
define( 'W4ILS_VERSION', '1.3' ) ;
define( 'W4ILS_NAME', 'W4 Internal Link Shortcode' ) ;
define( 'W4ILS_SLUG', strtolower(str_replace(' ', '-', W4ILS_NAME ))) ;


class W4ILS{
	function W4ILS(){
		foreach(array('the_content', 'the_excerpt', 'get_the_content', 'get_the_excerpt', 'category_description' ) as $hook)
			add_filter( $hook, array(&$this, 'w4_link_shortcode'));
		
		// Just put it so that other pllugin can hook.
		add_shortcode( 'intlink', array(&$this, 'do_link'));
		
		add_action('plugin_action_links_'.W4ILS_BASENAME, array(&$this, 'w4ils_plugin_action_links'));
		
		add_filter( 'w4_post_list_attr', array(&$this, 'sanitize_target'));
		add_filter( 'w4_post_list_attr', array(&$this, 'sanitize_type'));
		
		add_filter( 'w4_post_list_attr', array(&$this, 'post_link'));
		add_filter( 'w4_post_list_attr', array(&$this, 'cat_link'));
		add_filter( 'w4_post_list_attr', array(&$this, 'author_link'));
	}
	
	function w4ils_plugin_action_links( $links ){
		$readme_link['readme'] = '<a href="http://w4dev.com/w4-plugin/w4-internal-link-shortcode/">'.esc_html( __( 'How to use' )).'</a>';
		return array_merge( $links, $readme_link );
	}

/*==================================================================================*/
	// Do shortcode
	function w4_link_shortcode($text){
		$pattern = '/\[\s*intlink\s*(.*?)\s*\]/sm' ;
		return preg_replace_callback( $pattern, array(&$this, 'callback'), $text ) ;
	}
/*==================================================================================*/
	// Retrive and replace links
	private function callback( $matches ){
		return $this->do_link( shortcode_parse_atts($matches[1]));
	}
/*==================================================================================*/
	// Retrive and replace links
	function do_link( $attr ){
		$default = array(
							'type' 		=> 'post',
							'id' 		=> '',
							'name'	 	=> '',
							'slug' 		=> '',
							'text' 		=> '',
							'link_url'	=> '',
							'class' 	=> 'w4_internal_link',
							'before' 	=> '',
							'after' 	=> '',
							'target'	=> false,
							'blog_id'	=> null
							);

		//Parsing attributes from shortcode by shortcode_parse_atts()
		$attr = wp_parse_args( $attr, $default);
		$attr = apply_filters( 'w4_post_list_attr', $attr);
		return $this->the_link( $attr);
	}
/*==================================================================================*/
	function the_link( $attr){
		if(!$attr['found'] && ( is_admin() || is_super_admin()))
			return '<span style="color:red;">"Link not found. Please check your given attribute properly."</span>';
		
		extract( $attr, EXTR_SKIP);
		
		$link_attr = sprintf( __( 'class="%1$s" target="%2$s"'), $class, $target );
		$link_text = empty($text) ? $link_text : $text;
		
		$link = sprintf(__('%4$s<a %3$s href="%1$s">%2$s</a>%5$s'), $link_url, $link_text, $link_attr, $before, $after);
		return $link;
		
	}
/*==================================================================================*/
	function post_link( $attr){
		extract($attr, EXTR_SKIP);

		if( $attr['found'] || 'post' != $type )
			return $attr;

		if( $blog_id)
			$blog_id = $this->blog_exists($blog_id);
		
		if($blog_id){
			if( $w4post = get_blog_post( $blog_id, $id )){
				$attr['id'] 		= $w4post->ID;
				$attr['link_url'] 	= get_blog_permalink( $blog_id, $id);
				$attr['link_text'] 		= $w4post->post_title;
				$attr['found']		= true;
			}
		}
		else{
			$w4post = $this->get_post_by_field( $id, 'ID' );
			
			if(!$w4post)
				$w4post = ($name)? $this->get_post_by_field( $name): false;
	
			if(!$w4post)
				$w4post = ($slug)? $this->get_post_by_field( $slug, 'post_name'): false;
				
			if($w4post){
				$attr['id'] 		= $w4post->ID;
				$attr['link_url'] 	= get_permalink( $w4post->ID);
				$attr['link_text']		= $w4post->post_title;
				$attr['found']		= true;
			}
		}
		return $attr;
	}
/*==================================================================================*/
	function cat_link($attr){
		if( $attr['found'] || 'cat' != $attr['type'])
			return $attr;
		
		extract($attr, EXTR_SKIP);
		$cat = get_term_by( 'id', $id, 'category' );

		if(!$cat->term_id)
			$cat = ($name)? get_term_by( 'name', $name, 'category' ):false;
	
		if(!$cat->term_id)
			$cat = ($slug)? get_term_by( 'slug', $slug, 'category' ):false;
	
		if($cat){
			$attr['id'] 		= $cat->term_id ;
			$attr['link_url'] 	= get_category_link( $cat->term_id);
			$attr['link_text']		= get_cat_name( $cat->term_id);
			$attr['found']		= true;
		}
		return $attr;
	}
/*==================================================================================*/
	function author_link( $attr){
		if( $attr['found'] || 'author' != $attr['type'])
			return $attr;
		
		extract( $attr, EXTR_SKIP);

		$author = get_userdata( $id);
		
		if(!$author)
			$author = ($name)? $this->get_author_by_field( $name):false;
				
		//Get author by nicename
		if(!$author)
			$author = ($slug)? $this->get_author_by_field($slug, 'user_nicename'):false;
	
		if($author){
			$attr['id'] 		= $author->ID;
			$attr['link_url'] 	= get_author_posts_url($author->ID);
			$attr['link_text']		= get_the_author_meta('display_name', $author->ID);
			$attr['found']		= true;
		}
		return $attr;
	}

/*==================================================================================*/
	function sanitize_type($attr){
		$type = $attr['type'];
		$default_types = array(
			'post' => array('p','post','posts','page','pages'),
			'cat' => array('c','cat','cats','category'),
			'author' => array('a','auth','aothor','author')
								);
		$type = strtolower($type);
		if( in_array($type, $default_types['cat']))
			$type = 'cat';

		elseif( in_array($type, $default_types['author']))
			$type = 'author';

		else
			$type = 'post';
		
		$attr['type'] = $type;
		return $attr;
	}
/*==================================================================================*/
	function sanitize_target($attr){
		extract($attr, EXTR_SKIP);
		$target_keys = array('tr','Tr','tar','targat','_target','terget','openin','newpage');
		if(!$target){
			foreach($target_keys as $t){
				if(isset($$t))
					$target = $$t;
			}
		}
		
		if(in_array( $target, array('p','parent', 'own', 'this', 'self', 'no')))
			$target = '_parent';
		
		if(in_array( $target, array('b', 'blank', 'new', 'another', 'out', 'yes')))
			$target = '_blank';

		$w4ils_target_params = array('_blank', '_parent', '_self', '_top');
		if($target == false || !in_array( $target,$w4ils_target_params))
			$target = '_parent';
		
		$attr['target'] = $target;
		return $attr;
	}
/*==================================================================================*/
	// Custom post query function
	function get_post_by_field( $val = '', $key = 'post_title', $output = OBJECT ){
		global $wpdb;
		$post_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE $key = %s AND post_type IN ('post','page') AND post_status = 'publish' LIMIT 1",
		$val ));
		
		if( $post_id)
			return get_post($post_id, $output);
	
		return false;
	}
/*==================================================================================*/
	//Custom author query function
	function get_author_by_field($val = '', $key = 'display_name', $output = OBJECT) {
		global $wpdb;
			$author_id = $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->users WHERE $key = %s LIMIT 1", $val ));
			if ( $author_id )
				return get_userdata($author_id);
	
		return false;
	}
	function blog_exists( $blog_id = null){
		global $wpdb;
		return (int)$wpdb->get_var( $wpdb->prepare("SELECT blog_id FROM $wpdb->blogs WHERE
	blog_id = %s AND public = '1' AND archived = '0' AND mature = '0' AND spam = '0' AND deleted = '0' LIMIT 1", (int)$blog_id));
	}
}//class W4ILS ends

$W4ILS = new W4ILS();
?>