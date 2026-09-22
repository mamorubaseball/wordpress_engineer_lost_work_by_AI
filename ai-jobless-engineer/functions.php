<?php
/**
 * AI Jobless Engineer theme setup.
 *
 * @package AIJoblessEngineer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function aje_setup() {
	add_theme_support( 'wp-block-styles' );
	add_theme_support( 'editor-styles' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'title-tag' );
	add_editor_style( 'style.css' );

	register_nav_menus(
		array(
			'primary' => __( 'Primary Navigation', 'ai-jobless-engineer' ),
			'social'  => __( 'Social Links', 'ai-jobless-engineer' ),
		)
	);
}
add_action( 'after_setup_theme', 'aje_setup' );

function aje_enqueue_assets() {
	$theme_version = wp_get_theme()->get( 'Version' );

	wp_enqueue_style(
		'aje-style',
		get_stylesheet_uri(),
		array( 'dashicons' ),
		$theme_version
	);

	wp_enqueue_script(
		'aje-theme',
		get_theme_file_uri( 'assets/js/theme.js' ),
		array(),
		$theme_version,
		true
	);
}
add_action( 'wp_enqueue_scripts', 'aje_enqueue_assets' );

function aje_register_product_post_type() {
	register_post_type(
		'product',
		array(
			'labels'       => array(
				'name'          => __( 'Products', 'ai-jobless-engineer' ),
				'singular_name' => __( 'Product', 'ai-jobless-engineer' ),
				'add_new_item'  => __( 'Add New Product', 'ai-jobless-engineer' ),
				'edit_item'     => __( 'Edit Product', 'ai-jobless-engineer' ),
			),
			'public'       => true,
			'show_in_rest' => true,
			'menu_icon'    => 'dashicons-portfolio',
			'has_archive'  => true,
			'rewrite'      => array( 'slug' => 'products' ),
			'supports'     => array( 'title', 'editor', 'excerpt', 'thumbnail', 'custom-fields', 'revisions' ),
			'taxonomies'   => array( 'post_tag' ),
		)
	);
}
add_action( 'init', 'aje_register_product_post_type' );

function aje_register_block_patterns() {
	register_block_pattern_category(
		'aje',
		array( 'label' => __( 'AI Jobless Engineer', 'ai-jobless-engineer' ) )
	);
}
add_action( 'init', 'aje_register_block_patterns' );

function aje_reading_time() {
	$content = wp_strip_all_tags( get_post_field( 'post_content', get_the_ID() ) );
	$content = preg_replace( '/\s+/u', '', $content );
	$count   = function_exists( 'mb_strlen' ) ? mb_strlen( $content ) : strlen( $content );
	$minutes = max( 1, (int) ceil( $count / 500 ) );

	return sprintf(
		/* translators: %d: reading time in minutes */
		_n( '%d min read', '%d min read', $minutes, 'ai-jobless-engineer' ),
		$minutes
	);
}

function aje_reading_time_shortcode() {
	return '<span class="aje-reading">' . esc_html( aje_reading_time() ) . '</span>';
}
add_shortcode( 'aje_reading_time', 'aje_reading_time_shortcode' );

function aje_document_title_parts( $title ) {
	if ( is_front_page() ) {
		$title['tagline'] = __( 'AIで仕事がなくなる。じゃあ、どうする？', 'ai-jobless-engineer' );
	}

	return $title;
}
add_filter( 'document_title_parts', 'aje_document_title_parts' );

function aje_meta_tags() {
	$description = get_bloginfo( 'description' );

	if ( is_singular() ) {
		$post = get_queried_object();
		if ( $post instanceof WP_Post ) {
			$description = has_excerpt( $post )
				? get_the_excerpt( $post )
				: wp_trim_words( wp_strip_all_tags( strip_shortcodes( $post->post_content ) ), 90, '…' );
		}
	}

	if ( ! $description ) {
		$description = __( 'AI時代を生きるエンジニアの、ツール・開発・キャリア・暮らしの実践記録。', 'ai-jobless-engineer' );
	}

	echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	echo '<meta property="og:type" content="' . ( is_singular() ? 'article' : 'website' ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( wp_get_document_title() ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $description ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( is_singular() ? get_permalink() : home_url( '/' ) ) . '">' . "\n";

	if ( is_singular() && has_post_thumbnail() ) {
		echo '<meta property="og:image" content="' . esc_url( get_the_post_thumbnail_url( null, 'large' ) ) . '">' . "\n";
	}
}
add_action( 'wp_head', 'aje_meta_tags', 5 );

// Codex invite feature.
require_once get_theme_file_path( 'inc/codex-invite.php' );
