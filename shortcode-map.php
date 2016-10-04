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
 * Handler for the Ribbon shortcode
 * Ensures that the jQuery script is only loaded on pages that contain the shortcode
 *
 */

class Map_Shortcode {
	static $add_script;

	static function init() {
		add_shortcode('map', array(__CLASS__, 'handle_shortcode'));

		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
	}

	static function handle_shortcode($atts, $content = null) {
		self::$add_script = true;

		extract( shortcode_atts( array(
			'height'	=> 500,
			'id'     	=> 'map',
			'lat'    	=> '50.062635',
			'lon'   	=> '-5.56421',
			'pins'		=> '',
			'title' 	=> 'Lamorna Cove',
			'zoom'  	=> 12,
		), $atts ) );

		$output = '';
		$output .= sprintf('<div id="%s-container">', $id);
		$output .= sprintf('	<div id="%s" class="map" data-lat="%s" data-lon="%s" data-pins="%s" data-height="%s" data-title="%s" data-zoom="%s"></div>', $id, $lat, $lon, $pins, $height, $title, $zoom);
		$output .= '	<div class="mapContent">' . do_shortcode($content) . '</div>';
		$output .= '</div>';

		return $output;
	}

	static function register_script() {
		wp_register_script('google-maps', "https://maps.google.com/maps/api/js?key=AIzaSyA5ctQ0z_-sYAReYXQcZHDtlOC4y6G1SZ4", '', '1.0', true);
		wp_register_script('map-script', plugins_url('/js/map-script.js', __FILE__), array('jquery'), '1.0', true);
	}

	static function print_script() {
		if (self::$add_script) {
			wp_print_scripts('google-maps');
			wp_print_scripts('map-script');
		}
	}
}
Map_Shortcode::init();

?>
