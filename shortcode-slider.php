<?php
/*  Copyright 2013  Ian Berthon  (email : ian@summerleaze.biz)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


/**
 * Handler for the Slider shortcode
 * Ensures that the jQuery script is only loaded on pages that contain the shortcode
 *
 */

class Slider_Shortcode {
	static $add_script;

	static function init() {
		add_shortcode('cross-slider',   array(__CLASS__, 'handle_shortcode'));
		add_shortcode('gallery-slider', array(__CLASS__, 'handle_gallery_shortcode'));

		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
	}

	static function handle_shortcode($atts, $content = "Loading...") {
		self::$add_script = true;

		extract( shortcode_atts( array(
			'class' 	=> 'cross-slider',
			'field' 	=> 'slide',
			'id'    	=> 'banner',
			'slides'	=> '',
		), $atts ) );

		$df = get_stylesheet_directory_uri();

		$output = sprintf('<div id="%s" class="%s">', $id, $class);
		$output .= sprintf('	<div class="sliders" data-folder="%s">', $df);

		if($slides) {
			$my_slides = explode(',', trim(strip_tags($slides)));
			$output .= self::process_slides($my_slides);
		}
		elseif($field) {
			$my_slides = get_post_custom_values($field);
			$output .= self::process_slides($my_slides);
		}
		else {
			$output .= do_shortcode($content);
		}

		$output .= '	</div>';
		$output .= '	<div class="slider-caption"></div>';
		$output .= '</div>';

		return $output;
	}

	static function handle_gallery_shortcode($atts, $content = null) {
		self::$add_script = true;

		extract( shortcode_atts( array(
			'delay'   => 10,
			'id'      => 'front-page-slider',
			'gallery' => 1,
			'opacity' => 1,
		), $atts ) );

		$js_id = str_replace('-', '_', $id);

		$imagegallery = new nggdb();
		$images = $imagegallery->get_gallery($gallery);

		$slides = array();
		foreach ($images as $image) {
			//DumpVar($image);
			list($from, $to, $time) = explode('|', $image->alttext);

			if(empty($from)) {
				$from = '50% 50% 1x';
			}
			if(empty($to)) {
				$to = '50% 50% 1x';
			}
			if(empty($time)) {
				$time = $delay;
			}

			$slides[] = sprintf('{src:"%s", alt:"%s", title:"%s", from:"%s", to:"%s", time:%d},', $image->imageURL, $image->description, $image->description, $from, $to, $time);
		}
		//DumpVar($slides);

		$output = '';
		$output .= sprintf('<div id="%s-container" class="cross-slider">', $id);
		$output .= sprintf('<div id="%s" class="slides">Loading...</div>', $id);
		$output .= '<div class="caption"></div>';
		$output .= '</div>';
		$output .= '<script type="text/javascript">';
		$output .= sprintf('var %s_slides = [', $js_id);
		foreach($slides as $slide) {
			$output .= $slide;
		}
		$output .= '];';
		$output .= sprintf('jQuery(document).ready(function() { startSlider("#%s", %s, %s_slides); });', $id, $opacity, $js_id);
		$output .= '</script>';

		return $output;
	}

	// Helper Functions...

	private static function process_slides($slides) {
		$result = '';
		foreach($slides as $slide) {
			list($name, $caption, $from, $to, $time) = array_pad(explode('|', trim($slide), 5), 5, null);
			$image = self::get_media($name);
			//DumpVar($name, $caption, $from, $to, $time, $image);
	  	$href = wp_get_attachment_url($image->ID);

  		$result .= sprintf('		<img src="%s" data-from="%s" data-to="%s" data-time="%s" alt="%s" />', $href, $from, $to, $time, $caption);
		}
		return $result;
	}

	private static function set_attr($attr, $value) {
		if(!empty($value)) {
			$result = sprintf('%s="%s" ', $attr, $value);
		}
		return $result;
	}

	private static function get_media_id($title) {
	  $args = array(
			'post_type' => 'attachment',
			'post_mime_type' =>'image',
			'post_status' => 'inherit',
			'posts_per_page' => -1,
	  );
	  $query_images = new WP_Query( $args );

	  $id = '';
	  foreach ($query_images->posts as $image) {
	  	if((strtolower($title) == strtolower($image->post_title)) || (strtolower($title) == strtolower($image->post_name))) {
	  		$id = $image->ID;
	  		break;
	  	}
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
	  $query_images = new WP_Query( $args );

	  $media = null;
	  foreach ($query_images->posts as $image) {
	  	if((strtolower($title) == strtolower($image->post_title)) || (strtolower($title) == strtolower($image->post_name))) {
	  		$media = $image;
	  		break;
	  	}
	  }
	  return $media;
	}

	static function register_script() {
		wp_register_script('easing-lib', sl_relative_url('js/jquery.easing-1.3.pack.js', __FILE__), '', '1.0', true);
		wp_register_script('slider-lib', sl_relative_url('js/jquery.cross-slide.js', __FILE__), '', '1.0', true);
		wp_register_script('slider-script', sl_relative_url('js/start-slider.js', __FILE__), '', '1.0', true);
	}

	static function print_script() {
		if (self::$add_script) {
			wp_print_scripts('easing-lib');
			wp_print_scripts('slider-lib');
			wp_print_scripts('slider-script');
		}
	}
}
Slider_Shortcode::init();

?>
