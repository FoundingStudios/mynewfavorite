<?php
/** Router: one branch per future Next.js route. */
defined( 'ABSPATH' ) || exit;
get_header();
if ( function_exists( 'is_product' ) && is_product() ) {
	get_template_part( 'template-parts/product' );
} elseif ( ( is_front_page() || is_home() ) && ! is_search() ) {
	get_template_part( 'template-parts/home' );
} elseif ( function_exists( 'is_shop' ) && ( is_shop() || is_product_category() ) ) {
	get_template_part( 'template-parts/archive' );
} elseif ( is_search() ) {
	get_template_part( 'template-parts/catalog' );
} else {
	get_template_part( 'template-parts/page' ); // cart, checkout, account, 404, posts
}
get_footer();
