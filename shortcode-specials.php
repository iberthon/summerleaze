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
 * Handler for the Specials shortcode
 * Ensures that the jQuery script is only loaded on pages that contain the shortcode
 *
 */

class Specials_Shortcode {
	static $add_script;

	static function init() {
		add_shortcode('specials', array(__CLASS__, 'handle_shortcode'));

		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
	}

	static function handle_shortcode($atts, $content = null) {
		self::$add_script = true;

		extract( shortcode_atts( array(
			'class'      => 'red15',
			'category'   => 'specials',
			'container'  => 'special-offers',
			'limit'      => 4,
			'title'      => "Special Offers for " . date('F Y'),
			'link_title' => FALSE,
		), $atts ) );

		$ribbon_cat = get_category_by_slug($category);
		$ribbon_id = $ribbon_cat->term_id;

		$query_args = array(
	    'category__in' => array($ribbon_id),
	    'orderby' => 'rand',
			'post_status' => 'publish',
			'post_type' => 'post',
			'posts_per_page' => $limit,
		);

		$my_posts = new WP_Query($query_args);

		$output = '';
		if ( $my_posts->have_posts() ) {
			$output .= '<div id="' . $container . '">';
			if($title) {
				$output .= '<h1>' . $title . '</h1>';
			}
			$output .= '<ul class="boxed-content">';
			while ( $my_posts->have_posts() ) {
				global $post;

				$my_posts->the_post();

				$title = the_title('','',false);

				$output .= '<li>';
				$output .= '<a href="' . get_permalink() . '" title="' . $title . '" rel="bookmark">';
				$output .= '<div class="ribbon ' . $class . '"><h6>';
				//$output .= '<a href="' . get_permalink() . '" title="' . $title . '" rel="bookmark">' . $title . '</a>';
				$output .= $title;
				$output .= '</div></h6>';
				$output .= '<div class="content">';
				if (has_post_thumbnail()) {
					$output .= get_the_post_thumbnail($post->ID, 'ribbon');
				}
				else {
					$output .= get_the_excerpt();
				}
				$output .= '</div>';

				if(get_post_meta($post->ID, 'Special', true)) {
	        $output .= '<div class="footer">' . wsf_get_price(array('field'=>'Special')) . '</div>';
	      }
				$output .= '</a>';
				$output .= wsf_get_price(array('limit' => 1));
				$output .= '</li>';
			}
			$output .= '</ul>';
			$output .= '</div>';
		}

		return $output;
	}

	static function register_script() {
		wp_register_script('specials-script', sl_relative_url('js/specials-script.js', __FILE__), array('jquery'), '1.0', true);
	}

	static function print_script() {
		if (self::$add_script) {
			wp_print_scripts('specials-script');
		}
	}
}
Specials_Shortcode::init();

?>
