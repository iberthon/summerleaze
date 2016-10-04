<?php
/*

Theme Name: The Hood Arms
Author: Ian Berthon
Version: 3.2
License: GNU General Public License v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Author URI: http://www.summerleaze.biz

Copyright 2014 Ian Berthon (email: ian@summerleaze.biz)

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.

*/

/**
 * Handler for the Link shortcode
 * Ensures that the script is only loaded on pages that contain the shortcode
 *
 * v1.0.0 2012-05-20 Initial Version
 * v1.0.1 2012-08-16 Added do_shortcode() to $content
 * v1.0.2 2013-05-15 Tidied code
 * v1.0.3 2013-07-01 Added 'Name' parameter
 * v1.0.4 2013-07-02 Added 'External' parameter
 * v1.0.5 2013-07-24 Added 'param' parameter
 * v1.0.6 2013-07-24 Added 'page' parameter
 * v1.1.0 2013-07-24 Rewritten get_links() and renamed shortcode to 'bookmarks'
 * v1.1.1 2013-08-05 Changed Media to use title or slug (ignoring any trailing numbers...)
 * v1.2.0 2013-08-13 Integrated Media shortcode
 * v1.2.1 2013-10-30 Added NextGen v1.9 code to Media Shortcode
 * v1.3.0 2014-01-23 Added email option
 * v1.3.1 2014-01-23 Fixed error in cross slider handler
 * v1.3.2 2014-03-24 Moved cross-slider shortcode to it's own module
 * v1.3.3 2014-09-17 Added 'style' option to media shortcode and refactored slightly
 *
 * Copyright 2012-2013 Summerleaze Computer Services
 */

class Link_Shortcode {
	static function init() {
		add_shortcode('link', array(__CLASS__, 'handle_anchor_shortcode'));
		add_shortcode('link', array(__CLASS__, 'handle_link_shortcode'));
		add_shortcode('link2', array(__CLASS__, 'handle_link2_shortcode'));
		add_shortcode('media', array(__CLASS__, 'handle_media_shortcode'));
		add_shortcode('media-box', array(__CLASS__, 'handle_media_box_shortcode'));
		add_shortcode('bookmarks', array(__CLASS__, 'get_links'));

		//add_action('init', array(__CLASS__, 'register_script'));
		//add_action('wp_footer', array(__CLASS__, 'print_script'));
	}

	static function handle_anchor_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'name'	=> '',
		), $atts ) );

	 	//$backtrace = wp_debug_backtrace_summary();
		//$_debug = ExportVar(home_url($ian), );

		$html = sprintf('<a id="%s"></a>', $name);

		//$html .= $_debug;
		//$html .= '<pre>' . $backtrace . '</pre>'

		return $html;
	}

	static function handle_link2_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'anchor'		=> '',
			'class'			=> '',
			'email'			=> '',
			'target'		=> '',
			'title'			=> '',
			'type'			=> '',
			'url'				=> '',
		), $atts ) );

		$options = '';

	 	//$backtrace = wp_debug_backtrace_summary();
		//$_debug = ExportVar(home_url($ian), );

		if(!empty($email)) {
			$url = 'mailto:' . $email;
			$label = (empty($content) ? $email : $content);
		}
		elseif(!empty($anchor)) {
			$url .= '#' . $anchor;
		}
		else {
			$types = array('page', 'post');
			if($type) {
				$types = array_merge($types, explode('|', $type));
			}

			$post = get_page_by_path($url, OBJECT, $types);

			//DumpVar($types, $url, $post);

			if($post) {
				$label = (empty($content) ? $post->post_title : $content);
				$url = get_permalink($post->ID);
			}
		 	else {
				// Assume title is an external url
				$label = $content;
				if(empty($target)) {
					$target = '_blank';
				}
		 	}
		}

		if(!empty($class)) {
			$options .= sprintf(' class="%s"', $class);
		}
		if(!empty($target)) {
			$options .= sprintf(' target="%s"', $target);
		}

		$html = sprintf('<a href="%s"%s>%s</a>', $url, $options, $label);

		//$html .= $_debug;
		//$html .= '<pre>' . $backtrace . '</pre>'

		return $html;
	}

	static function handle_link_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'bookmark'	=> '',
			'class'			=> '',
			'email'			=> '',
			'external'	=> '',
			'media'			=> '',
			'name'			=> '',
			'page'			=> '',
			'param'			=> '',
			'span'			=> '',
			'target'		=> '',
			'title'			=> '',
			'type'			=> '',
			'url'				=> '',
		), $atts ) );

		$output = '';
		$options = '';

		if(!empty($url)) {
			$url = home_url($url);
			$content = (empty($content) ? 'here' : $content);
		}
		elseif(!empty($title)) {
			$page = get_page_by_title($title);
			$url = get_permalink($page);
			$content = (empty($content) ? $page->post_title : $content);
		}
		elseif(!empty($bookmark)) {
			$bookmarks = get_bookmarks();
			foreach($bookmarks as $bm) {
				if(strtolower($bm->link_name) == strtolower($bookmark)) {
					$url = $bm->link_url;
					$content = (empty($content) ? $bm->link_name : $content);
					break;
				}
			}
		}
		elseif(!empty($media)) {
		  $args = array(
				'post_type'      => 'attachment',
				'post_mime_type' => $type,
				'post_status'    => 'inherit',
				'posts_per_page' => -1,
		  );
		  $query_images = new WP_Query( $args );
		  $images = array();

		  $id = '';
		  foreach ( $query_images->posts as $image) {
				$image_slug = preg_replace('/(-\d+)$/', '', $image->post_name);
				//echo ExportVar($media, $image_slug);
		  	if( (strtolower($media) == strtolower($image->post_title)) || (strtolower($media) == strtolower($image_slug)) ) {
				  $url = wp_get_attachment_url($image->ID);
					$content = (empty($content) ? 'here' : $content);
					//echo ExportVar($media, $image_slug, $url);
		  		break;
		  	}
		  }
		}
		elseif(!empty($external)) {
			$url = $external;
			$content = (empty($content) ? 'here' : $content);
			$target = (empty($target) ? '_blank' : $target);
		}
		elseif(!empty($page)) {
			$url = '#';
			$class = 'page-link';
			$options .= sprintf(' data-page="%d"', $page);
		}
		elseif(!empty($email)) {
			$url = 'mailto:' . $email;
			$content = (empty($content) ? $email : $content);
		}

		if(isset($url)) {
			if(!empty($param)) {
				$url .= '#' . $param;
			}
			if(!empty($class)) {
				$options .= sprintf(' class="%s"', $class);
			}
			if(!empty($target)) {
				$options .= sprintf(' target="%s"', $target);
			}
			if(!empty($span)) {
				$span = sprintf("<span>%s</span>", $span);
			}
			$output .= sprintf('<a href="%s"%s>%s%s</a>', $url, $options, $span, do_shortcode($content));
		}

		//$output .= $_debug;

		return $output;
	}

	static function handle_media_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'align'					=> '',
			'name'					=> '',
			'size'					=> '',
			'link'					=> false,
			'show_caption'	=> false,
			'show_title' 		=> false,
		), $atts ) );

		$image = self::get_media($name);

  	$url = '';
  	if($link) {
  		$url = wp_get_attachment_url($image->ID);
  	}

		$caption = '';
		if($show_caption) {
			$caption = $image->post_excerpt;
		}
		$title = '';
		if($show_title) {
			$title = $image->post_title;
		}

		$html = sl_image_filter($html, $image->ID, $caption, $title, $align, $url, '', $title);

		return $html;
	}

	static function handle_media_box_shortcode($atts, $content = null) {
		extract( shortcode_atts( array(
			'caption'   => '',
			'class'     => '',
			'id'        => '',
			'img_class' => '',
			'lightbox'  => false,
			'name'      => '',
			'size'      => '',
			'title'     => '',
		), $atts ) );

		$output = '';

		$names = explode(',', $name);
		$output .= sprintf('<div class="%s">', $class);
		foreach($names as $name) {
			$output .= self::handle_media_shortcode(array('caption' => $caption, 'img_class' => $img_class, 'lightbox' => $lightbox, 'name' => trim($name), 'size' => $size));
		}
		$output .= '</div>';

		return $output;
	}

	static function get_links($atts, $content = null) {
		extract( shortcode_atts( array(
			'category' => '',
			'class'    => 'links-list',
			'sort'		 => '',
		), $atts ) );

		$args = array();
		if(!empty($category)) {
			$args['category_name'] = $category;
		}
		if(!empty($sort)) {
			$args['orderby'] = $sort;
		}
		$bookmarks = get_bookmarks($args);

		$output = '';
		if(!empty($bookmarks)) {
			//DumpVar($bookmarks);
			if(!empty($class)) {
				$output .= sprintf('<ul class="%s">', $class);
			}
			else {
				$output .= '<ul>';
			}
			foreach ($bookmarks as $bookmark) {
  	  	$output .= '<li>';
  	  	$output .= sprintf('<a href="%s">%s</a>', $bookmark->link_url, $bookmark->link_name);
  	  	if(!empty($bookmark->link_description)) {
  	  		$output .= '<br />' . $bookmark->link_description;
  	  	}
  	  	$output .= '</li>';
			}
			$output .= '</ul>';
		}
		return $output;
	}

	// Helper Functions...

	private static function set_attr($attr, $value) {
		$result = '';
		if(!empty($value)) {
			$result = sprintf('%s="%s" ', $attr, $value);
		}
		return $result;
	}

	private static function get_media_id($title) {
		$image = self::get_media($title);

	  $id = '';
	  if($image) {
  		$id = $image->ID;
	  }
	  return $id;
	}

	private static function get_media($title) {
	  $args = array(
			'post_type' => 'attachment',
			'post_mime_type' =>'image',
			'post_status' => 'inherit',
			'posts_per_page' => -1,
	  );
	  $images = new WP_Query( $args );

	  $media = null;
	  foreach ($images->posts as $image) {
	  	if((strtolower($title) == strtolower($image->post_title)) || (strtolower($title) == strtolower($image->post_name))) {
	  		$media = $image;
	  		break;
	  	}
	  }
		wp_reset_query();
	  return $media;
	}

	static function register_script() {
	}

	static function print_script() {
	}

}
Link_Shortcode::init();

?>
