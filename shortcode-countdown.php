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
 * Handler for the Countdown shortcode
 * Ensures that the script is only loaded on pages that contain the shortcode
 *
 * v1.0 2012-08-09 Initial Version
 *
 * Copyright 2012 Summerleaze Computer Services
 */

class Countdown_Shortcode {
	static $add_script;

	static function init() {
		add_shortcode('countdown', array(__CLASS__, 'handle_shortcode'));

		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
	}

	static function handle_shortcode($atts, $content = null) {
		self::$add_script = true;

		extract( shortcode_atts( array(
			'deadline' => '2013-03-13 00:00',
		), $atts ) );

		$output = '<div id="countdown" data-deadline="' . $deadline . '">' . date('j M Y H:i', strtotime($deadline)) . '</div>';
		return $output;
	}

	static function register_script() {
		wp_register_script('deadline-script', get_stylesheet_directory_uri() . '/js/deadline.js', array('jquery'), '1.0', true);
	}

	static function print_script() {
		if (self::$add_script) {
			wp_print_scripts('deadline-script');
		}
	}
}
Countdown_Shortcode::init();

?>
