<?php
/**
 * Handler for the Ribbon shortcode
 * Ensures that the jQuery script is only loaded on pages that contain the shortcode
 *
 */

class PageBreak_Shortcode {
	static $add_script;

	static function init() {
		add_shortcode('page-break', array(__CLASS__, 'handle_shortcode'));

		add_action('init', array(__CLASS__, 'register_script'));
		add_action('wp_footer', array(__CLASS__, 'print_script'));
	}

	static function handle_shortcode($atts, $content = null) {
		self::$add_script = true;

		extract( shortcode_atts( array(
			'page' => '',
		), $atts ) );

		$output = '</div>';
		$output .= '</div>';

		if(!empty($page)) {
			$output .= sprintf('<div id="%s" class="entry-page">', $page);
		}
		else {
			$output .= '<div class="entry-page">';
		}

		$output .= '<div class="entry-content">';

		return $output;
	}

	static function register_script() {
		wp_register_script('jquery-cycle', get_bloginfo('stylesheet_directory') . '/js/jquery.cycle.all.js', array('jquery'), '1.0');
		wp_register_script('pager-script', get_stylesheet_directory_uri() . '/js/pager-script.js', array('jquery'), '1.0', true);
	}

	static function print_script() {
		if (self::$add_script) {
			wp_print_scripts('jquery-cycle');
			wp_print_scripts('pager-script');
		}
	}
}
PageBreak_Shortcode::init();

?>
