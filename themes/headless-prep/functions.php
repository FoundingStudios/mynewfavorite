<?php
defined( 'ABSPATH' ) || exit;

require_once get_theme_file_path( 'inc/data.php' );

add_action( 'after_setup_theme', function () {
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', [ 'search-form', 'gallery', 'caption', 'style', 'script' ] );
	add_theme_support( 'woocommerce' );
} );

add_action( 'wp_enqueue_scripts', function () {
	$file = get_theme_file_path( 'assets/app.css' );
	wp_enqueue_style( 'headless-prep', get_theme_file_uri( 'assets/app.css' ), [], file_exists( $file ) ? filemtime( $file ) : null );
} );

// All views render through index.php (the router), not WooCommerce's own templates.
add_filter( 'template_include', fn() => get_theme_file_path( 'index.php' ), 999 );
