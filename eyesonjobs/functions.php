<?php
/**
 * Eyes on Jobs theme bootstrap.
 *
 * @package EyesOnJobs
 */

defined( 'ABSPATH' ) || exit;

define( 'EOJ_THEME_VERSION', '1.0.0' );
define( 'EOJ_THEME_DIR', get_template_directory() );
define( 'EOJ_THEME_URI', get_template_directory_uri() );

require EOJ_THEME_DIR . '/inc/customizer.php';
require EOJ_THEME_DIR . '/inc/template-tags.php';
require EOJ_THEME_DIR . '/inc/shortcodes.php';
require EOJ_THEME_DIR . '/inc/starter-content.php';

/**
 * Theme supports, menus and image sizes.
 */
function eoj_setup() {
	load_theme_textdomain( 'eyesonjobs', EOJ_THEME_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 280,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);
	add_editor_style( array( eoj_fonts_url(), 'assets/css/main.css', 'assets/css/editor.css' ) );

	register_nav_menus(
		array(
			'primary' => __( 'Main menu', 'eyesonjobs' ),
			'footer'  => __( 'Footer quick links', 'eyesonjobs' ),
			'legal'   => __( 'Footer legal links', 'eyesonjobs' ),
		)
	);

	add_post_type_support( 'page', 'excerpt' );
}
add_action( 'after_setup_theme', 'eoj_setup' );

/**
 * Google Fonts URL (Plus Jakarta Sans).
 */
function eoj_fonts_url() {
	return 'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap';
}

/**
 * Front-end styles and scripts.
 */
function eoj_assets() {
	wp_enqueue_style( 'eoj-fonts', eoj_fonts_url(), array(), null ); // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
	wp_enqueue_style( 'eoj-main', EOJ_THEME_URI . '/assets/css/main.css', array(), EOJ_THEME_VERSION );
	wp_enqueue_style( 'eoj-classic', EOJ_THEME_URI . '/assets/css/classic.css', array( 'eoj-main' ), EOJ_THEME_VERSION );
	wp_enqueue_script( 'eoj-main', EOJ_THEME_URI . '/assets/js/main.js', array(), EOJ_THEME_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'eoj_assets' );

/**
 * Preconnect to Google Fonts for a faster first paint.
 *
 * @param array  $urls          URLs.
 * @param string $relation_type Relation.
 */
function eoj_resource_hints( $urls, $relation_type ) {
	if ( 'preconnect' === $relation_type ) {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'eoj_resource_hints', 10, 2 );

/**
 * Footer widget area.
 */
function eoj_widgets() {
	register_sidebar(
		array(
			'name'          => __( 'Blog sidebar', 'eyesonjobs' ),
			'id'            => 'sidebar-1',
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h3 class="widget-title">',
			'after_title'   => '</h3>',
		)
	);
}
add_action( 'widgets_init', 'eoj_widgets' );

/**
 * Body classes used by the stylesheet.
 *
 * @param array $classes Classes.
 */
function eoj_body_class( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'eoj-home';
	}
	if ( ! eoj_has_page_hero() ) {
		$classes[] = 'eoj-no-hero';
	}
	return $classes;
}
add_filter( 'body_class', 'eoj_body_class' );

/**
 * Shorter, cleaner excerpts.
 */
add_filter(
	'excerpt_length',
	function () {
		return 24;
	}
);
add_filter(
	'excerpt_more',
	function () {
		return '&hellip;';
	}
);

/**
 * Mark JS as available before paint (enables scroll animations). If the
 * main script never loads, the class is removed so content stays visible.
 */
function eoj_js_flag() {
	echo "<script>document.documentElement.classList.add('eoj-js');setTimeout(function(){if(!document.documentElement.classList.contains('eoj-ready')){document.documentElement.classList.remove('eoj-js');}},3500);</script>\n";
}
add_action( 'wp_head', 'eoj_js_flag', 1 );

/**
 * Default social-sharing image (the official logo banner) for pages without a featured image.
 */
function eoj_og_image() {
	if ( is_singular() && has_post_thumbnail() ) {
		return;
	}
	echo '<meta property="og:image" content="' . esc_url( EOJ_THEME_URI . '/assets/img/logo-banner.png' ) . "\">\n";
}
add_action( 'wp_head', 'eoj_og_image', 5 );
