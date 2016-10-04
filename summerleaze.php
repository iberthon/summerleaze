<?php
/*
Plugin Name: Summerleaze Functions
Plugin URI: http://www.summerleaze.biz/wp-Plugin
Description: Useful functions shared by Summerleaze themes
Version: 1.5.5
Author: Ian Berthon
Author URI: http://www.summerleaze.biz

Copyright 2014-2015 Ian Berthon (email: ian@summerleaze.biz)

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

v1.0.0	2014-02-03 Initial Version - code pulled from various wordpress projects...
v1.1.0	2014-02-04 Automatic updating now working
v1.1.1	2014-02-04 Initial refactoring
v1.1.2	2014-02-04 Fixed bug in cross slider handler
v1.1.3	2014-02-12 Added [list] shortcode
v1.1.4	2014-02-12 Disabled automatic updates due to .tmp folder problem
v1.2.0	2014-03-03 Integrated meta data display functions
v1.3.0	2014-03-10 Removed get-category shortcode, added sl_get_featured_post
v1.3.1	2014-03-11 Re-enabled automatic updates for testing...
v1.3.2	2014-03-11 Simplified box shortcode handler
v1.3.3	2014-03-12 Added text to sl-headings shortcode; Added copyright shortcode
v1.3.4	2014-03-24 Fixed some bugs in shortcode-slider; Removed cross slider shortcode from shortcode-link
v1.3.5	2014-04-23 Added 'wp_reset_query()' when using custom queries.
v1.3.6	2014-06-01 Added 'sl_get_post_name', 'sl_url_exists' from HO Soccer theme
v1.3.7	2014-06-07 sl_get_media_id() now allows the slug as well as title to be used
v1.3.8	2014-06-23 Added a couple of options to tweet function
v1.3.8	2014-06-24 Added ability to convert Tweets into News stories
v1.3.8	2014-06-28 Bugfixes
v1.3.9	2014-06-30 Renamed .inc files to .php files
v1.3.10	2014-07-26 Added Seasons function (Hood Arms)
v1.3.11 2014-08-12 Added sl_get_media_data() function
v1.3.12 2014-09-09 Added sl_relative_url() function
v1.3.13 2014-09-30 Modified copyright shortcode to handle start being the current year
v1.3.14 2015-02-03 Added [centred] shortcode (same as [box class="centred"])
v1.3.15 2015-02-03 Moved sl_image_filter code here from themes that use it
v1.3.16 2015-02-06 Added sample wp_title filter from the Codex
v1.3.17 2015-03-06 Many fixes for sl_image_filter. Added sl_media_filter and sl_thumb_filter
v1.3.18 2015-06-08 Updates to [copyright] and sl_ShowPageInfo for HO Soccer
v1.3.19 2015-06-12 Added [panel] and [flex] shortcodes
v1.3.20 2015-06-29 Added sl_add_trailing_slash() function
v1.3.21 2015-06-29 Made [panel] shortcode a bit more flexible
v1.4.0	2015-07-29 Added sl_get_the_content() function
v1.5.0	2015-07-30 Added [get-tweets] shortcode
v1.5.1	2015-10-08 Fix [list] formatting whencontent is empty
v1.5.2  2016-05-10 Added sl_load_page_script function
v1.5.3  2016-05-24 Various bug fixes
v1.5.4	2016-06-14 Added asynchronous/deferred script loading
v1.5.5	2016-07-22 Changed [box] shortcode to use <blockquote>
*/

require_once('shortcode-link.php');
require_once('shortcode-map.php');
//require_once('shortcode-pagebreak.php');
//require_once('shortcode-slider.php');
require_once('shortcode-specials.php');

require_once('tmhOAuth.php');
require_once('tmhUtilities.php');

function add_header_clacks($headers) {
	$headers['X-Clacks-Overhead'] = 'GNU Terry Pratchett';
	return $headers;
}
add_filter('wp_headers', 'add_header_clacks');

if(!function_exists('sl_theme_init')) {
	function sl_theme_init() {
		//remove_all_filters('image_send_to_editor');
		add_filter('disable_captions', create_function('$a','return true;'));
		add_filter('image_send_to_editor', 'sl_image_filter', 20, 8);
		add_filter('media_send_to_editor', 'sl_media_filter', 20, 3);
		add_filter('post_thumbnail_html',  'sl_thumb_filter', 20, 5);
	}
	add_action('init', 'sl_theme_init', 99);
}

/**
 * Filters script loading to add async/defer tags
 */
function sl_async_script_tag($tag, $handle, $src) {
	//echo "<!-- TAG:'" . rtrim($tag) . "' HANDLE:'" . $handle . "' SRC:'" . $src . "' -->\n";
	if (strpos($src, '#asyncload') !== false) {
		if (!is_admin()) {
			$tag = str_replace( ' src', ' async="async" src', $tag );	
		}
	}
	if (strpos($src, '#deferload') !== false) {
		if (!is_admin()) {
			$tag = str_replace( ' src', ' defer="defer" src', $tag );	
		}
	}
	return $tag;
}
add_filter('script_loader_tag', 'sl_async_script_tag', 11, 3);

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function sl_wp_title($title = '', $sep = '|') {
	if ( is_feed() ) {
		return $title;
	}

	global $page, $paged;

	// Add the blog name
	$title = get_bloginfo( 'name', 'display' ) . wp_title( $sep, false, 'right' );
	//DumpVar($title);
	
	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " $sep $site_description";
	}

	// Add a page number if necessary:
	if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
		$title .= " $sep " . sprintf( __( 'Page %s', '_s' ), max( $paged, $page ) );
	}

	return $title;
}
//add_filter('wp_title', 'sl_wp_title', 10, 2 );

function sl_ShowPageInfo($file, $showDetails = 0) {
	$current_user = wp_get_current_user();
	if($showDetails) {
		echo 'User ID           : ' . $current_user->ID . "\n";
		echo 'Username          : ' . $current_user->user_login . "\n";
		echo 'User email        : ' . $current_user->user_email . "\n";
		echo 'User first name   : ' . $current_user->user_firstname . "\n";
		echo 'User last name    : ' . $current_user->user_lastname . "\n";
		echo 'User display name : ' . $current_user->display_name . "\n";
	}
	if((defined('SHOW_PAGE_INFO') && SHOW_PAGE_INFO == true)) { // || $current_user->user_login == 'ian') {
		echo '<pre>';
		echo $file;
		//echo '<br />';
		//print_r( get_defined_vars() );
		if(is_archive()) { echo ', IS_ARCHIVE'; }
		if(is_category()) { echo ', IS_CATEGORY'; }
		if(is_singular()) { echo ', IS_SINGULAR'; }
		echo '</pre>';
	}
}

/**
 * Display navigation to next/previous pages when applicable
 */
function sl_content_nav( $nav_id ) {
	global $wp_query;

	if ($wp_query->max_num_pages > 1 ) {
		echo '<nav id="' . $nav_id . '">';
		echo '	<h3 class="assistive-text">' . __( 'Post navigation', THEME_DOMAIN ) . '</h3>';
		if(function_exists('wp_paginate')) {
			//$paginate_args = array('query' => $sticky_posts);
			wp_paginate();
		}
		else {
			echo '  <div class="navigation">';
			echo '	  <div class="nav-previous">' . next_posts_link( __( '<span class="meta-nav">&larr;</span> Older posts', THEME_DOMAIN ) ) . '</div>';
			echo '	  <div class="nav-next">' . previous_posts_link( __( 'Newer posts <span class="meta-nav">&rarr;</span>', THEME_DOMAIN ) ) . '</div>';
			echo '  </div>';
		}
		echo '</nav>';
	}
}

function sl_posted_on($show_author = false) {
	printf( __('	<span class="sep">Posted on </span>
									<a href="%1$s" title="%2$s" rel="bookmark">
										<time class="entry-date" datetime="%3$s" pubdate>%4$s</time>
									</a>
							', THEME_DOMAIN),
		esc_url(get_permalink()),
		esc_attr(get_the_time()),
		esc_attr(get_the_date('c')),
		esc_html(get_the_date())
	);
	if($show_author) {
		printf( __('	<span class="by-author">
										<span class="sep"> by </span>
										<span class="author vcard">
											<a class="url fn n" href="%5$s" title="%6$s" rel="author">%7$s</a>
										</span>
									</span>', THEME_DOMAIN),
			esc_url(get_author_posts_url(get_the_author_meta('ID'))),
			sprintf(esc_attr__('View all posts by %s', THEME_DOMAIN), get_the_author()),
			esc_html(get_the_author())
		);
	}
}

function sl_entry_meta() {
	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', THEME_DOMAIN ) );

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', THEME_DOMAIN ) );

	$date = sprintf( '<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date" datetime="%3$s">%4$s</time></a>',
		esc_url( get_permalink() ),
		esc_attr( get_the_time() ),
		esc_attr( get_the_date( 'c' ) ),
		esc_html( get_the_date() )
	);

	$author = sprintf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
		esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
		esc_attr( sprintf( __( 'View all posts by %s', THEME_DOMAIN ), get_the_author() ) ),
		get_the_author()
	);

	// Translators: 1 is category, 2 is tag, 3 is the date and 4 is the author's name.
	if ( $tag_list ) {
		$utility_text = __( 'This entry was posted in %1$s and tagged %2$s on %3$s<span class="by-author"> by %4$s</span>.', THEME_DOMAIN );
	} elseif ( $categories_list ) {
		$utility_text = __( 'This entry was posted in %1$s on %3$s<span class="by-author"> by %4$s</span>.', THEME_DOMAIN );
	} else {
		$utility_text = __( 'This entry was posted on %3$s<span class="by-author"> by %4$s</span>.', THEME_DOMAIN );
	}

	printf(
		$utility_text,
		$categories_list,
		$tag_list,
		$date,
		$author
	);
}

function sl_post_footer() {
	global $post;

	$show_sep = false;
	if (get_post_type() == 'post' || get_post_type() == 'concert') {
		/* translators: used between list items, there is a space after the comma */
		$categories_list = get_the_category_list( __( ', ', THEME_DOMAIN ) );
		if ( $categories_list ) {
			echo '<span class="cat-links">';
			printf( __( '<span class="%1$s">Posted in</span> %2$s', THEME_DOMAIN ), 'entry-utility-prep entry-utility-prep-cat-links', $categories_list );
			$show_sep = true;
			echo '</span>';
		}

		//$tags_list = get_the_tag_list( '', __( ', ', THEME_DOMAIN ) );
		if ( $tags_list ) {
			if ( $show_sep ) {
				echo '<span class="sep"> | </span>';
			}
			echo '<span class="tag-links">';
			printf( __( '<span class="%1$s">Tagged:</span> %2$s', THEME_DOMAIN ), 'entry-utility-prep entry-utility-prep-tag-links', $tags_list );
			$show_sep = true;
			echo '</span>';
		}
	}
	if ( comments_open() ) {
		if ( $show_sep ) {
			echo '<span class="sep"> | </span>';
		}
		echo '<span class="comments-link">' . comments_popup_link( '<span class="leave-reply">' . __( 'Leave a reply', THEME_DOMAIN ) . '</span>', __( '<b>1</b> Reply', THEME_DOMAIN ), __( '<b>%</b> Replies', THEME_DOMAIN ) ) . '</span>';
	}

	if ( $show_sep ) {
		echo '<span class="sep"> | </span>';
	}
	edit_post_link( __( 'Edit', THEME_DOMAIN ), '<span class="edit-link">', '</span>' );
}

function sl_get_featured_posts($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'id' => 'featured-posts',
		'limit' => 5,
		'orderby' => null,
		'slug' => 'featured',
		'type' => 'any',
	), $atts ) );

	$args = array(
	  'category_name' => $slug,
	  'orderby' => 'date',
	  'order' => 'ASC',
	  'post_status' => 'any',
		'post_type' => $type,
	  'showposts' => $limit
	);

	$myPosts = new WP_Query($args);
	if ( $myPosts->have_posts() ) {
		//echo '<div id="' . $id . '" class="cycle-slideshow" data-cycle-slides="> article" data-cycle-fx="carousel" data-cycle-timeout="10000">';
		echo '<div id="' . $id . '" class="carousel">';
		echo '<ul>';
		while ( $myPosts->have_posts() ) {
			$myPosts->the_post();

			$post_id = get_the_ID();
			$post_permalink = get_permalink();

			echo '<li id="post-' . $post_id . '" class="' . join(' ', get_post_class()) . '">';
			echo '	<a href="' . $post_permalink . '">';
			// Output the featured image.
			if ( has_post_thumbnail() ) {
				 echo get_the_post_thumbnail($post_id, 'featured-post');
			}
			echo '   <h1 class="entry-title">' . get_the_title() . '</h1>';
			echo '   </a>';
			echo '</li>';
		}
		echo '</ul>';
		echo '</div>';
	}
	wp_reset_query();
}

function sl_image_filter($html, $id, $caption, $title, $align, $url, $size, $alt) {
	$title = get_the_title($id); // $title always seems to be blank (bug in WP?) so get it from $id
	$image = wp_get_attachment_image_src($id, $size);
	$src = $image[0];

 	$backtrace = wp_debug_backtrace_summary();
	$_debug = ExportVar("sl_image_filter", $html, $id, $caption, $title, $align, $url, $size, $alt);

	// Don't display title or caption...
	$no_caption = stripos($size, '-no-caption');
	if($no_caption || $size == 'thumbnail') {
		$size = substr($size, 0, $no_caption);
		$caption = '';
		$title = '';
	}

	// Don't include 'alignnone'
	if($align == 'none') {
		$align = '';
	}

	// Don't include 'size-none'
	if($size == 'none') {
		$size = '';
	}

	if($caption) {
		$lines = preg_split ('/$\R?^/m', $caption);
		$caption = join('<br />', $lines);
	}

	if(empty($alt)) {
		$alt = $title;
	}

	$classes = array();

	// Are we dealing with an image-link or an image (== image if $url is empty)
	if(!empty($url)) {
		$classes[] = 'image-link link-size-' . $size;
		if($size) {
			$img_class = 'size-' . $size;
		}
	}
	else {
		if($size) {
			array_push($classes, 'size-' . $size);
		}
	}

	if($align) {
		array_push($classes, 'align' . $align);
	}

	$class = join(' ', $classes);
	if(!empty($class)) {
		$class = sprintf(' class="%s"', $class);
	}
	if(!empty($img_class)) {
		$img_class = sprintf(' class="%s"', $img_class);
	}

	if(!empty($url)) {
		if(!empty($title)) {
			$title = sprintf('<span class="title">%s</span>', $title);
		}
		$html = sprintf('<a%s href="%s"><img%s src="%s" alt="%s" />%s%s</a>', $class, $url, $img_class, $src, $alt, $title, $caption);
	}
	else {
		if(!empty($title)) {
			$title = sprintf(' title="%s"', $title);
		}
		$html = sprintf('<img%s src="%s"%s alt="%s" />', $class, $src, $title, $alt);
	}

	//$html .= $_debug;
	//$html .= '<pre>' . $backtrace . '</pre>'

	return $html;
}

function sl_media_filter($html, $id, $attachment) {
	//$_debug = ExportVar("sl_media_filter", $html, $id, $attachment);

	if (!wp_attachment_is_image($id)) {
		$description = $attachment['post_content'];
		$caption = $attachment['post_excerpt'];
		$title = $attachment['post_title'];
		$url = $attachment['url'];

		if(empty($caption)) {
			$caption = $title;
		}

		$html = sprintf('<a class="media-link" href="%s" target="_blank"><span>%s</span></a>', $url, $caption);
  }

	$html .= $_debug;

	return $html;
}

function sl_thumb_filter($html, $post_id, $post_thumbnail_id, $size, $attr) {

	$_debug = ExportVar("sl_thumb_filter", $html, $post_id, $post_thumbnail_id, $size, $attr);

	if($html) {
		$image = wp_get_attachment_image_src($post_thumbnail_id, $size);
		$src = $image[0];

		$_debug .= ExportVar($image);

		$options = '';
		if(!empty($size)) {
			$options .= sprintf(' class="size-%s"', $size);
		}
		$options .= ' alt="Post Thumbnail"';

		$html = sprintf('<img src="%s"%s />', $src, $options);
	}

	//$html .= $_debug;

	return $html;
}

function sl_get_the_content() {
	$content = apply_filters( 'the_content', get_the_content(__( 'Continue reading <span class="meta-nav">&rarr;</span>', THEME_DOMAIN)));
	$content = str_replace( ']]>', ']]&gt;', $content );
	return $content;
}

/*
 * SHORTCODE HANDLERS
 */

function sl_list_shortcode_handler($atts, $content = null) {
	extract( shortcode_atts( array(
		'class' => '',
	), $atts ) );

	$entries = array();
	foreach($atts as $key => $value) {
		if(strtolower(substr($key, 0, 5)) == 'entry') {
			array_push($entries, $value);
		}
	}

	if(!empty($entries)) {
		$output = '<ul' . (empty($class) ? '' : ' class="' . $class . '"') . '>';
		foreach($entries as $entry) {
			if(strpos($entry, '|') !== false) {
				list($name, $content) = explode('|', $entry);
				$output .= sprintf('<li><span class="name">%s</span> &middot; %s</li>', $name, $content);
			}
			else {
				$output .= sprintf('<li>%s</li>', $entry);
			}
		}
		$output .= '</ul>';
	}

	return $output;
}
add_shortcode('list', 'sl_list_shortcode_handler');

function sl_headings_test_shortcode_handler($atts, $content = null) {
	extract( shortcode_atts( array(
		'class' => 'blueBox',
	), $atts ) );

	$output = '';
	$text = do_shortcode($content);
	for($i = 1; $i <= 6; $i++) {
		$output .= sprintf('<h%d>Heading #%d: %s</h%d>', $i, $i, $text, $i);
	}
	return $output;
}
add_shortcode('sl-headings', 'sl_headings_test_shortcode_handler');

function sl_flex_shortcode_handler($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'class' => '',
		'id'    => '',
	), $atts ) );
	//DumpVar($atts);

	$output = '<div';
	if(!empty($id)) {
		$output .= sprintf(' id="%s"', $id);
	}
	$classes = array('flex');
	if(!empty($class)) {
		$classes = array_merge($classes, explode(' ', $class));
	}
	$output .= sprintf(' class="%s"', implode(' ', $classes));
	$output .= '>';

	$output .= do_shortcode($content);
	$output .= '</div>';
	return $output;
}
add_shortcode('flex', 'sl_flex_shortcode_handler');

function sl_panel_shortcode_handler($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'bk_image'		=> '',
		'bk_pos'			=> '',
		'bk_size'			=> '',
		'class' 			=> '',
		'href'				=> '',
		'id'    			=> '',
		'target'			=> '',
		'title'				=> '',
		'title_class'	=> '',
	), $atts ) );
	//DumpVar($atts);

	$output = '';
	$el = empty($href) ? 'div' : 'a';

	$output = '<' . $el;
	if(!empty($id)) {
		$output .= sprintf(' id="%s"', $id);
	}
	$classes = array('panel');
	if(!empty($class)) {
		$classes = array_merge($classes, explode(' ', $class));
		//DumpVar($classes);
	}
	$output .= sprintf(' class="%s"', implode(' ', $classes));

	if(!empty($bk_image)) {
		$url = sl_get_media_url($bk_image);
		$output .= sprintf(' data-bk-image="%s"', $url);
	}
	if(!empty($bk_pos)) {
		$output .= sprintf(' data-bk-pos="%s"', $bk_pos);
	}
	if(!empty($bk_size)) {
		$output .= sprintf(' data-bk-pos="%s"', $bk_size);
	}

	if(!empty($href)) {
		if(strpos($href, 'http') === false) {
			$href = home_url($href);
		}
		$output .= sprintf(' href="%s"', $href);
		if(!empty($target)) {
			$output .= sprintf(' target="%s"', $target);
		}
	}
	$output .= '>';

	if(!empty($title)) {
		$output .= '<span';
		if(!empty($title_class)) {
			$output .= sprintf(' class="%s"', $title_class);
		}
		$output .= sprintf('>%s</span>', $title);
	}
	$output .= do_shortcode($content);
	$output .= '</' . $el . '>';

	return $output;
}
add_shortcode('panel', 'sl_panel_shortcode_handler');

function sl_box_shortcode_handler($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'class' => '',
		'id'    => '',
		'title' => '',
	), $atts ) );

	$html = '<blockquote';
	if(!empty($id)) {
		$html .= sprintf(' id="%s"', $id);
	}
	if(!empty($class)) {
		$classes = explode(' ', $class);
		array_unshift($classes, 'box');
		$html .= sprintf(' class="%s"', implode(' ', $classes));
	}
	$html .= '>';

	if(!empty($title)) {
		$html = sprintf("<h3>%s</h3>\n", $title);
	}
	$html .= do_shortcode($content);
	$html .= '</blockquote>';
	return $html;
}
add_shortcode('box', 'sl_box_shortcode_handler');
add_shortcode('box1', 'sl_box_shortcode_handler');	// work around the recursive shortcode problem...
add_shortcode('box2', 'sl_box_shortcode_handler');

function sl_photo_box_shortcode_handler($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'class' => '',
		'id'    => '',
		'title' => '',
	), $atts ) );

	$output = '<div';
	if(!empty($id)) {
		$output .= sprintf(' id="%s"', $id);
	}
	$classes = explode(' ', $class);
	if(!in_array('photo-box', $classes)) {
		array_unshift($classes, 'photo-box');
	}
	$output .= sprintf(' class="%s">', implode(' ', $classes));

	if(!empty($title)) {
		$output = sprintf("<h3>%s</h3>\n", $title);
	}
	$output .= do_shortcode($content);
	$output .= '</div>';
	return $output;
}
add_shortcode('photo-box', 'sl_photo_box_shortcode_handler');

function sl_centred_shortcode_handler($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'class' => '',
		'id'    => '',
		'title' => '',
	), $atts ) );
	//DumpVar($atts);

	$output = '<div';
	if(!empty($id)) {
		$output .= sprintf(' id="%s"', $id);
	}

	$classes = array_merge(array('centred'), explode(' ', $class));
	if(!empty($classes)) {
		$output .= sprintf(' class="%s"', implode(' ', $classes));
	}
	$output .= '>';

	if(!empty($title)) {
		$output = sprintf("<h3>%s</h3>\n", $title);
	}
	$output .= do_shortcode($content);
	$output .= '</div>';
	return $output;
}
add_shortcode('centred', 'sl_centred_shortcode_handler');

function sl_menu_shortcode($atts, $content = null) {
	extract( shortcode_atts( array(
		'class' => 'menu',
		'name'  => null,
	), $atts ) );
	//DumpVar($atts);
	return wp_nav_menu( array( 'menu' => $name, 'menu_class' => $class, 'echo' => false ) );
}
add_shortcode('menu', 'sl_menu_shortcode');

function sl_blockquote_shortcode($atts, $content = null) {
	extract( shortcode_atts( array(
		'cite'	=> '',
		'class' => '',
		'title' => '',
	), $atts ) );

	$options = '';
	if(isset($class)) {
		$options .= sprintf(' class="%s"', $class);
	}
	if(isset($style)) {
		$options .= sprintf(' style="%s"', $style);
	}

	if($title) {
		$title = sprintf("<h6>%s</h6>", $title);
	}

	if($cite) {
		$cite = sprintf('<cite>%s</cite>', $cite);
	}

	$output = sprintf('<blockquote%s>%s%s%s</blockquote>', $options, $title, do_shortcode($content), $cite);

	return $output;
}
add_shortcode('blockquote', 'sl_blockquote_shortcode');
add_shortcode('pullquote', 'sl_blockquote_shortcode');
add_shortcode('quote', 'sl_blockquote_shortcode');

function sl_video_shortcode_handler($atts, $content = null) {
	$params = shortcode_atts( array(
		'allowfullscreen' => '',
		'class'           => '',
		'frameborder'     => '0',
		'height' 					=> '315',
		'id'     					=> '',
		'name'   					=> '',
		'width'  					=> '420',
	), $atts );
	extract($params);

	$output = '';
	if(!empty($name)) {

  	$output .= sprintf('<div class="video-container"><iframe src="%s?feature=oembed"', $name);
  	foreach($params as $k => $v) {
  		if($k != 'name' && $v) {
  			$output .= sprintf(' %s="%s"', $k, $v);
  		}
		}
		$output .= '></iframe></div>';
	}
	return $output;
}
add_shortcode('video', 'sl_video_shortcode_handler');

function sl_social_media($atts, $content = null) {
	extract( shortcode_atts( array(
		'class'							=> '',
		'facebook_button'   => 'Find us on<br />Facebook',
		'facebook_page'			=> false,
		'facebook_like'			=> false,
		'facebook_like_box'	=> false,
		'trip_advisor'			=> false,
		'twitter'  					=> false,
	), $atts ) );

	$output = '';

	if($twitter) {
		//$output .= sprintf('<a href="%s" class="twitter-follow-button" data-show-count="false" data-size="large">Follow @TheHoodArms</a>', $twitter);
		//$output .= "<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>";
		$output .= sprintf('<a class="button" href="%s" target="_blank" title="Follow us on Twitter"><span class="twitter">Follow<br />@TheHoodArms</span></a>', $twitter);
	}

	if($facebook_page || $facebook_like || $facebook_like_box) {
		// Load JavaScript Libraries
		//wp_enqueue_script('facebook-jssdk', '//connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v2.0', '', '2.0', true);

		$facebook_icon = THEME_URI . '/images/facebook.png';
		//$output .= '<div id="fb-root"></div><script>(function(d, s, id) {var js, fjs = d.getElementsByTagName(s)[0];if (d.getElementById(id)) return;js = d.createElement(s); console.info("Loading Facebook SDK..."); js.id = id;js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&appId=311510725702525&version=v2.0";fjs.parentNode.insertBefore(js, fjs);}(document, "script", "facebook-jssdk"));</script>';

		if($facebook_page) {
			//$output .= sprintf('<a href="%s" target="_blank" title="Find Us On Facebook"><img src="%s" alt="Faceboook Icon" /></a>', $facebook, $facebook_icon);
			$output .= sprintf('<a class="button" href="%s" target="_blank" title="Find us on Facebook"><span class="facebook">%s</span></a>', $facebook_page, $facebook_button);
		}
		if($facebook_like) {
			$output .= sprintf('<div style="display:block" class="fb-like" data-href="%s" data-width="100" data-layout="button" data-action="like" data-show-faces="true" data-share="true"></div>', $facebook_like);
		}
		if($facebook_like_box) {
			$output .= sprintf('<div class="fb-like-box" data-href="%s" data-width="260" data-colorscheme="light" data-show-faces="true" data-header="true" data-stream="false" data-show-border="true"></div>', $facebook_like_box);
			//$output .= sprintf('<iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2FTheHoodArms&amp;width&amp;height=290&amp;colorscheme=light&amp;show_faces=true&amp;header=true&amp;stream=false&amp;show_border=true&amp;appId=311510725702525" scrolling="no" frameborder="0" style="border:none; overflow:hidden; height:290px;" allowTransparency="true"></iframe>');
		}
	}

	if($trip_advisor) {
		$output .= sprintf('<a class="button" href="%s" target="_blank" title="Review us on Trip Advisor"><span class="trip-advisor">Review us on<br />Trip Advisor</span></a>', $trip_advisor);
	}

	if(!empty($output)) {
		$options = '';
		if(!empty($class)) {
			$options .= sprintf(' class="%s"', $class);
		}
		$output = sprintf('<div id="social-media"%s>%s</div>', $options, $output);
	}
	return $output;
}
//add_shortcode('social-media', 'sl_social_media');

function sl_tweets_shortcode($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'create_post' => true,
		'date_format' => 'j M H:i',
		'id'    			=> 'tweets',
		'limit' 			=> 5,
		'name'  			=> 'HOSoccer_UK',
		'profile_img' => false,
		'title'				=> '<h6>Latest Tweets</h6>',
	), $atts ) );

	$consumer_key    = 'KFgDt2kCcWjWu5spl0NQXQ';
	$consumer_secret = 'xQQeqcJWf82eVDrL0InCe5Poo0GekqUN7wyTdKrU';
	$credentials     = base64_encode($consumer_key . ':' . $consumer_secret);
	$user_token      = '211194656-fZyGUedFMKtolrW0E1jFxFlZu8gf1dvZONI1a19t';
	$user_secret     = '7ebBT25oDGH2VDnxRTL9GRX4vYWxeR1DU3E766sfBUA';

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key'    => $consumer_key,
		'consumer_secret' => $consumer_secret,
		'user_token'      => $user_token,
		'user_secret'     => $user_secret,
		'curl_ssl_verifypeer'   => false,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array(
		'include_entities' => '1',
		'include_rts'      => '1',
		'screen_name'      => $name,
		'count'            => $limit,
	));
	//DumpVar($tmhOAuth->response);

	$output = '';
	if ($code == 200) {
  	$timeline = json_decode($tmhOAuth->response['response'], true);

		//DumpVar($timeline);

		if(!empty($title)) {
			$output .= $title;
		}
		$output .= '<div id="tweets">';

  	foreach ($timeline as $tweet) {
			$text = $tweet['text'];

			//DumpVar($tweet);

			if($create_post) {
				create_post_from_tweet($tweet);
			}

			// Expand any URLs in the text
			foreach($tweet['entities']['urls'] as $url) {
				//DumpVar($url);
				$s = sprintf('<a href="%s" target="_blank">%s</a>', $url['expanded_url'], $url['display_url']);
				$text = str_replace($url['url'], $s, $text);
			}

			if(!empty($tweet['entities']['media'])) {
				foreach($tweet['entities']['media'] as $media) {
					$s = sprintf('<a href="%s" target="_blank">%s</a>', $media['url'], $media['display_url']);
					$text = str_replace($media['url'], $s, $text);
				}
			}

			if(!empty($tweet['entities']['user_mentions'])) {
				foreach($tweet['entities']['user_mentions'] as $user) {
					$at_tag = sprintf('@%s', $user['screen_name']);
					$s = sprintf('<a href="https://twitter.com/%s" target="_blank">%s</a>', $user['screen_name'], $at_tag);
					$text = str_replace($at_tag, $s, $text);
				}
			}

			// Assemble the HTML
			$output .= '  <div class="tweet">';
			if($profile_img) {
				$output .= '    <img src="' . $tweet['user']['profile_image_url'] . '" alt="profile image" />';
			}
			$output .= '    <div>';
			$output .= '      ' . $text;
			$output .= '      <p class="when">' . date($date_format, strtotime($tweet['created_at'])) . '</p>';
			$output .= '    </div>';
			$output .= '  </div>';
		}
		$output .= '</div>';
	}
	return $output;
}
add_shortcode('tweets', 'sl_tweets_shortcode');

function sl_get_tweets_shortcode($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'date_format' => 'j M H:i',
		'id'    			=> 'tweets',
		'limit' 			=> 25,
		'name'  			=> 'HOSoccer_UK',
	), $atts ) );

	$consumer_key    = 'KFgDt2kCcWjWu5spl0NQXQ';
	$consumer_secret = 'xQQeqcJWf82eVDrL0InCe5Poo0GekqUN7wyTdKrU';
	$credentials     = base64_encode($consumer_key . ':' . $consumer_secret);
	$user_token      = '211194656-fZyGUedFMKtolrW0E1jFxFlZu8gf1dvZONI1a19t';
	$user_secret     = '7ebBT25oDGH2VDnxRTL9GRX4vYWxeR1DU3E766sfBUA';

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key'    => $consumer_key,
		'consumer_secret' => $consumer_secret,
		'user_token'      => $user_token,
		'user_secret'     => $user_secret,
		'curl_ssl_verifypeer'   => false,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array(
		'include_entities' => '1',
		'include_rts'      => '1',
		'screen_name'      => $name,
		'count'            => $limit,
	));
	//DumpVar($tmhOAuth->response);

	if ($code == 200) {
  	$timeline = json_decode($tmhOAuth->response['response'], true);
		//DumpVar($timeline);

  	foreach ($timeline as $tweet) {
			//DumpVar($tweet);
			create_post_from_tweet($tweet);
		}
	}
}
add_shortcode('get-tweets', 'sl_get_tweets_shortcode');


/*
https://instagram.com/oauth/authorize/?client_id=bf5a23e6b856480989fbb5d62248c7f2&redirect_uri=http://www.ho-soccer.co.uk&response_type=code
http://www.ho-soccer.co.uk/?code=e5f5d792d930445b86d26896e3777398


*/


function sl_get_instagram_shortcode($atts = array(), $content = null) {
	extract( shortcode_atts( array(
		'date_format' => 'j M H:i',
		'limit' 			=> 25,
		'name'  			=> 'hosoccer_uk',
	), $atts ) );

	$consumer_key    = 'KFgDt2kCcWjWu5spl0NQXQ';
	$consumer_secret = 'xQQeqcJWf82eVDrL0InCe5Poo0GekqUN7wyTdKrU';
	$credentials     = base64_encode($consumer_key . ':' . $consumer_secret);
	$user_token      = '211194656-fZyGUedFMKtolrW0E1jFxFlZu8gf1dvZONI1a19t';
	$user_secret     = '7ebBT25oDGH2VDnxRTL9GRX4vYWxeR1DU3E766sfBUA';

	$tmhOAuth = new tmhOAuth(array(
		'consumer_key'    => $consumer_key,
		'consumer_secret' => $consumer_secret,
		'user_token'      => $user_token,
		'user_secret'     => $user_secret,
		'curl_ssl_verifypeer'   => false,
	));

	$code = $tmhOAuth->request('GET', $tmhOAuth->url('1.1/statuses/user_timeline'), array(
		'include_entities' => '1',
		'include_rts'      => '1',
		'screen_name'      => $name,
		'count'            => $limit,
	));
	//DumpVar($tmhOAuth->response);

	if ($code == 200) {
  	$timeline = json_decode($tmhOAuth->response['response'], true);
		//DumpVar($timeline);

  	foreach ($timeline as $tweet) {
			//DumpVar($tweet);
			create_post_from_tweet($tweet);
		}
	}
}
add_shortcode('get-tweets', 'sl_get_tweets_shortcode');


function sl_copyright_shortcode_handler($atts, $content = null) {
	extract( shortcode_atts( array(
		'start' => '',
	), $atts ) );
	if(!empty($start)) {
		if($start != date('Y')) {
			$start .= '-';
		}
		else {
			$start = '';
		}
	}
	$output = sprintf('Copyright &copy; %s%d <a href="%s">%s</a> %s', $start, date("Y"), home_url(), get_bloginfo(), do_shortcode($content));
	return $output;
}
add_shortcode('copyright', 'sl_copyright_shortcode_handler');

function sl_facebook_shortcode_handler($atts, $content = null) {
	extract( shortcode_atts( array(
		'label' => 'Find us on Facebook',
		'page'  => 'http://www.facebook.com',
	), $atts ) );

	$output .= sprintf('<a class="facebook" href="%s" target="_blank" title="%s">%s</a>', $page, $label, $label);

	return $output;
}
add_shortcode('facebook', 'sl_facebook_shortcode_handler');

/*
 * HELPER FUNCTIONS
 */

function sl_add_trailing_slash($path) {
	$path = trim(str_replace('\\', '/', $path));
	if(substr($path, -1) != '/') {
		$path .= '/';
	}
	return $path;
}

function create_post_from_tweet($tweet) {
	if(!tweet_post_exists($tweet['id_str']) && !array_key_exists('retweeted_status', $tweet)) {
		$text = $tweet['text'];

		// Strip media links from the text
		$featured = '';
		if(!empty($tweet['entities']['media'])) {
			foreach($tweet['entities']['media'] as $media) {
				//DumpVar($media);
				//$s = sprintf('<img class="aligncenter size-medium" src="%s">', $media['media_url']);
				$text = str_replace($media['url'], '', $text);
			}
			$featured = array_shift($tweet['entities']['media']);
		}

		$created_at = strtotime($tweet['created_at']);

		$post = array(
		  'post_category' => array(277),
		  'post_content'  => $text,
		  'post_date'			=> date('Y-m-d H:i:s', $created_at),
		  'post_status'   => 'publish',
			'post_title'    => 'Tweet: ' . $tweet['created_at'],
		);
		$post_id = wp_insert_post($post, $wp_error);

		//DumpVar($wp_error);

		if(!is_wp_error($wp_error)) {
			add_post_meta($post_id, '_tweet_id', $tweet['id_str']);
			if($featured) {
				add_post_meta($post_id, '_thumbnail_url', $featured['media_url']);
			}
		}
	}
}

function tweet_post_exists($tweet_id) {
	global $wpdb;

	$result = FALSE;
	$sql = sprintf("SELECT * FROM wp_posts JOIN wp_postmeta on wp_postmeta.post_id=wp_posts.ID WHERE meta_key='_tweet_id' AND meta_value='%s'", $tweet_id);
	$row = $wpdb->get_row($sql);

	//DumpVar($sql, $row);

	return $row;
}

function sl_get_media($title, $class = '', $id = '') {
	$result = null;
	if(!empty($title)) {
		$url = sl_get_media_url($title);
		if($url) {
	  	$result = '<img';
			if(!empty($id)) {
				$result .= sprintf(' id="%s"', $id);
			}
			if(!empty($class)) {
				$result .= sprintf(' class="%s"', $class);
			}
			$result .= sprintf(' src="%s" alt="%s" />', $url, $title);
		}
	}
	return $result;
}

function sl_get_media_url($title) {
	$result = null;
	if(!empty($title)) {
		$media_id = sl_get_media_id($title);
		if($media_id) {
	  	$result = wp_get_attachment_url($media_id);
	  }
	}
	return $result;
}

function sl_get_media_id($title) {
  $query_images = sl_get_media_data();

  $id = null;
  foreach ( $query_images->posts as $image) {
  	if(	strtolower($title) == strtolower($image->post_title) ||
  			strtolower($title) == preg_replace('/(-\d+)$/', '', strtolower($image->post_name))
  		) {
  		$id = $image->ID;
  		break;
  	}
  }
  return $id;
}

function sl_get_media_array($list) {
  $query_images = sl_get_media_data();

	$titles = explode('|', strtolower($list));

  $results = array();
  foreach ($query_images->posts as $image) {
  	if(in_array(strtolower($image->post_title), $titles)) {
  		$results[strtolower($image->post_title)] = array('id' => $image->ID, 'caption' => $image->post_excerpt, 'description' => $image->post_content);
  	}
  }
  return $results;
}

function sl_get_media_data($type = 'image') {
  $args = array(
		'post_type' => 'attachment',
		'post_mime_type' => $type,
		'post_status' => 'inherit',
		'posts_per_page' => -1,
  );
  $query_images = new WP_Query( $args );

	wp_reset_query();

  return $query_images;
}

function sl_get_post_name($strip = false) {
	global $post;

	if($strip) {
		// Strip any numbers Wordpress may have added to the end of the name
		$slug = preg_replace('/(-\d+)$/', '', $post->post_name);
	}
	else {
		$slug = $post->post_name;
	}

	return $slug;
}

function sl_url_exists($url) {
	$exists = true;
	$headers = @get_headers($url);
	if($headers[0] == 'HTTP/1.1 404 Not Found') {
    $exists = false;
	}
	return $exists;
}

function sl_relative_url($path = '', $plugin = '') {
	$path = wp_normalize_path( $path );
	$plugin = wp_normalize_path( $plugin );

	$url = set_url_scheme( THEME_URI );

	if (!empty($plugin) && is_string($plugin)) {
		$folder = dirname(plugin_basename($plugin));
		if ($folder != '.') {
			$a = preg_split('/wp-content/', $folder);
			$b = preg_split('#/#', end($a));
			$url .= '/' . ltrim(end($b), '/');
		}
	}

	if ($path && is_string($path)) {
		$url .= '/' . ltrim($path, '/');
	}

	//DumpVar($url);

	return $url;
}

function sl_paging_nav() {
	// Previous/next page navigation.
	if(function_exists('wp_paginate')) { wp_paginate(); }
}

function sl_load_page_script() {
	$post_name = $GLOBALS['wp_query']->posts[0]->post_name;
	$script_file = THEME_FOLDER . '/js/' . $post_name . '.js';
	$script_uri = THEME_URI . '/js/' . $post_name . '.js#asyncload';
	//DumpVar($post_name, $script_file, $script_uri);
	if(is_page() && file_exists($script_file)) {
		wp_enqueue_script('post-script', $script_uri, array('jquery'), '1.0');
	}
}

/*
 * DEBUGGING FUNCTIONS
 */

function DumpVar() {
	printf("<pre>\n");
	var_dump(func_get_args());
	printf("</pre>\n");
}

function ExportVar() {
	return '<pre>' . var_export(func_get_args(), TRUE) . '</pre>';
}

function LogPrintf() {
	$fp = fopen('ian.log', 'a');
	fwrite($fp, sprintf("\n%s:: ", date(DATE_RFC822)));
	//fwrite($fp, var_export(func_get_args(), TRUE));
	fwrite($fp, sprintf(func_get_args()));
	fclose($fp);
}

function LogExport() {
	$fp = fopen('ian.log', 'a');
	fwrite($fp, sprintf("\n%s:: ", date(DATE_RFC822)));
	fwrite($fp, var_export(func_get_args(), TRUE));
	fclose($fp);
}

/* Hook to the 'all' action */
function backtrace_filters_and_actions() {
  /* The arguments are not truncated, so we get everything */
  $arguments = func_get_args();
  $tag = array_shift( $arguments ); /* Shift the tag */

  /* Get the hook type by backtracing */
  $backtrace = debug_backtrace();
  $hook_type = $backtrace[3]['function'];

  $output = "<pre>";
  $output .= "<i>$hook_type</i> <b>$tag</b>\n";
  foreach ( $arguments as $argument ) {
    $output .= "\t\t" . htmlentities(var_export( $argument, true )) . "\n";
	}
	$output .= "\n";
	$output .= "</pre>";

	$fp = fopen('/tmp/ian.log', 'ab');
	fwrite($fp, sprintf("\n%s:: ", date(DATE_RFC822)));
	fwrite($fp, $output);
	fclose($fp);
}
//add_action('all', 'backtrace_filters_and_actions');

function list_filters() {
	global $wp_filter;

	DumpVar($wp_filter);
}
//add_action('wp_footer', 'list_filters');

/*
function sl_image_filter($html, $id, $caption, $title, $align, $url, $size, $alt) {
	$title = get_the_title($id); // $title always seems to be blank (bug in WP?) so get it from $id
	$image = wp_get_attachment_image_src($id, $size);
	$src = $image[0];

 	$backtrace = wp_debug_backtrace_summary();
	$_debug = ExportVar("sl_image_filter", $html, $id, $caption, $title, $align, $url, $size, $alt);

	// Don't display captions on thumbnails
	if($size == 'thumbnail') {
		$caption = '';
	}

	if($caption) {
		$lines = preg_split ('/$\R?^/m', $caption);
		$caption = join('<br />', $lines);
	}

	if(empty($alt)) {
		$alt = $title;
	}

	$classes = array();

	// Are we dealing with an image-link or an image (== image if $url is empty)
	if(!empty($url)) {
		$classes[] = 'image-link';
	}
	if($align) {
		array_push($classes, 'align' . $align);
	}
	if($size) {
		array_push($classes, 'size-' . $size);
	}
	$class = join(' ', $classes);

	if(!empty($url)) {
		$text = sprintf('<span class="title">%s</span>%s', $title, $caption);
		$html = sprintf('<a class="%s" href="%s"><img src="%s" alt="%s" />%s</a>', $class, $url, $src, $alt, $text);
	}
	else {
		$html = sprintf('<img class="%s" src="%s" %s%s/>', $class, $src, $title, $alt);
	}

	//$html .= $_debug;
	//$html .= '<pre>' . $backtrace . '</pre>'

	return $html;
}

function sl_media_filter($html, $id, $attachment) {
	//$_debug = ExportVar("sl_media_filter", $html, $id, $attachment);

	if (!wp_attachment_is_image($id)) {
		$description = $attachment['post_content'];
		$caption = $attachment['post_excerpt'];
		$title = $attachment['post_title'];
		$url = $attachment['url'];

		if(empty($caption)) {
			$caption = $title;
		}

		$html = sprintf('<a class="media-link" href="%s" target="_blank"><span>%s</span></a>', $url, $caption);
  }

	$html .= $_debug;

	return $html;
}
*/

?>
