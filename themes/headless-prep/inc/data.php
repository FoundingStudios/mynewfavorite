<?php
/**
 * Data layer. Every function returns plain arrays shaped like the GraphQL `headless` fields.
 * Templates only read these arrays; in Next.js, replace these functions with GraphQL calls.
 */
defined( 'ABSPATH' ) || exit;

function hpe_money( $n ): string {
	return ( null === $n || '' === $n ) ? '' : html_entity_decode( get_woocommerce_currency_symbol() ) . number_format_i18n( (float) $n, wc_get_price_decimals() );
}

function hpe_price( WC_Product $p ): array {
	if ( function_exists( 'hce_price_data' ) ) { // from the Headless Commerce Engine plugin
		return hce_price_data( $p );
	}
	$c = (float) $p->get_price();
	return [ 'regular' => (float) $p->get_regular_price(), 'sale' => null, 'current' => $c, 'min' => $c, 'max' => $c, 'onSale' => $p->is_on_sale() ];
}

function hpe_price_label( array $pr ): string {
	return ( $pr['min'] ?? null ) !== ( $pr['max'] ?? null )
		? hpe_money( $pr['min'] ) . ' – ' . hpe_money( $pr['max'] )
		: hpe_money( $pr['current'] ?? null );
}

function hpe_product( WC_Product $p ): array {
	$pr = hpe_price( $p );
	return [
		'id'      => $p->get_id(),
		'title'   => $p->get_name(),
		'url'     => get_permalink( $p->get_id() ),
		'image'   => (string) wp_get_attachment_image_url( $p->get_image_id(), 'large' ),
		'type'    => $p->get_type(),
		'inStock' => $p->is_in_stock(),
		'onSale'  => ! empty( $pr['onSale'] ),
		'price'   => hpe_price_label( $pr ),
		'regular' => hpe_money( $pr['regular'] ?? null ),
		'cartUrl' => $p->add_to_cart_url(),
		'rating'  => (float) $p->get_average_rating(),
		'reviews' => (int) $p->get_review_count(),
	];
}

function hpe_product_full( WC_Product $p ): array {
	$ids     = array_filter( array_merge( [ $p->get_image_id() ], $p->get_gallery_image_ids() ) );
	$related = wc_get_related_products( $p->get_id(), 4 );
	return hpe_product( $p ) + [
		'gallery'     => array_values( array_filter( array_map( fn( $i ) => wp_get_attachment_image_url( $i, 'large' ), $ids ) ) ),
		'short'       => wp_kses_post( wpautop( $p->get_short_description() ) ),
		'description' => wp_kses_post( wpautop( $p->get_description() ) ),
		'sku'         => $p->get_sku(),
		'categories'  => wp_strip_all_tags( wc_get_product_category_list( $p->get_id(), ', ' ) ),
		'related'     => $related ? array_map( 'hpe_product', wc_get_products( [ 'include' => $related, 'limit' => 4 ] ) ) : [],
	];
}

function hpe_categories(): array {
	$terms = get_terms( [ 'taxonomy' => 'product_cat', 'hide_empty' => true, 'exclude' => [ (int) get_option( 'default_product_cat' ) ] ] );
	if ( is_wp_error( $terms ) ) { return []; }
	return array_map( fn( $t ) => [ 'name' => $t->name, 'slug' => $t->slug, 'url' => get_term_link( $t ), 'count' => $t->count, 'image' => (string) wp_get_attachment_image_url( (int) get_term_meta( $t->term_id, 'thumbnail_id', true ), 'large' ) ], $terms );
}


function hpe_featured( int $n = 8 ): array {
	$args  = [ 'status' => 'publish', 'limit' => $n, 'orderby' => 'date', 'order' => 'DESC' ];
	$items = wc_get_products( $args + [ 'featured' => true ] );
	if ( count( $items ) < $n ) { // top up with the newest products
		$ids   = array_map( fn( $p ) => $p->get_id(), $items );
		$items = array_merge( $items, wc_get_products( [ 'exclude' => $ids, 'limit' => $n - count( $items ) ] + $args ) );
	}
	return array_map( 'hpe_product', $items );
}

/** Active filters from the URL: ?min=&max=&stock=1&f[pa_size][]=m */
function hpe_filters(): array {
	$f = [
		'min'   => ( isset( $_GET['min'] ) && is_numeric( $_GET['min'] ) ) ? (float) $_GET['min'] : null,
		'max'   => ( isset( $_GET['max'] ) && is_numeric( $_GET['max'] ) ) ? (float) $_GET['max'] : null,
		'stock' => ! empty( $_GET['stock'] ),
		'attrs' => [],
	];
	foreach ( (array) ( $_GET['f'] ?? [] ) as $tax => $slugs ) {
		$tax = sanitize_key( $tax );
		if ( 0 === strpos( $tax, 'pa_' ) && taxonomy_exists( $tax ) ) {
			$f['attrs'][ $tax ] = array_map( 'sanitize_title', (array) $slugs );
		}
	}
	return $f;
}

/** Products for catalog/archive pages, filtered by category, search and the URL filters. */
function hpe_catalog( string $category = '', string $search = '' ): array {
	$f     = hpe_filters();
	$sorts = [ 'newest' => [ 'date', 'DESC' ], 'price-asc' => [ 'meta_value_num', 'ASC' ], 'price-desc' => [ 'meta_value_num', 'DESC' ], 'name' => [ 'title', 'ASC' ] ];
	$sort  = sanitize_key( wp_unslash( $_GET['sort'] ?? '' ) );
	$sort  = isset( $sorts[ $sort ] ) ? $sort : 'newest';

	$tax = [ 'relation' => 'AND', [ 'taxonomy' => 'product_visibility', 'field' => 'name', 'terms' => [ 'exclude-from-catalog' ], 'operator' => 'NOT IN' ] ];
	if ( $category ) { $tax[] = [ 'taxonomy' => 'product_cat', 'field' => 'slug', 'terms' => $category ]; }
	foreach ( $f['attrs'] as $t => $slugs ) { if ( $slugs ) { $tax[] = [ 'taxonomy' => $t, 'field' => 'slug', 'terms' => $slugs ]; } }

	$meta = [ 'relation' => 'AND' ];
	if ( null !== $f['min'] || null !== $f['max'] ) {
		$meta[] = [ 'key' => '_price', 'type' => 'NUMERIC', 'compare' => 'BETWEEN', 'value' => [ $f['min'] ?? 0, $f['max'] ?? 999999999 ] ];
	}
	if ( $f['stock'] ) { $meta[] = [ 'key' => '_stock_status', 'value' => 'instock' ]; }

	$args = [
		'post_type' => 'product', 'post_status' => 'publish', 'posts_per_page' => 12,
		'paged' => max( 1, absint( $_GET['pg'] ?? 1 ) ),
		'orderby' => $sorts[ $sort ][0], 'order' => $sorts[ $sort ][1],
		'tax_query' => $tax, 'meta_query' => $meta,
	];
	if ( 'meta_value_num' === $sorts[ $sort ][0] ) { $args['meta_key'] = '_price'; }
	if ( $search ) { $args['s'] = $search; }

	$q     = new WP_Query( $args );
	$items = [];
	foreach ( $q->posts as $post ) {
		$p = wc_get_product( $post );
		if ( $p ) { $items[] = hpe_product( $p ); }
	}
	return [ 'items' => $items, 'pages' => (int) $q->max_num_pages, 'total' => (int) $q->found_posts, 'sort' => $sort ];
}

/** Filterable attributes: only global attributes (Products > Attributes) that have terms in use. */
function hpe_facets(): array {
	$out = [];
	foreach ( wc_get_attribute_taxonomies() as $a ) {
		$tax   = wc_attribute_taxonomy_name( $a->attribute_name );
		$terms = get_terms( [ 'taxonomy' => $tax, 'hide_empty' => true ] );
		if ( is_wp_error( $terms ) || ! $terms ) { continue; }
		$out[] = [ 'taxonomy' => $tax, 'label' => $a->attribute_label, 'terms' => array_map( fn( $t ) => [ 'slug' => $t->slug, 'name' => $t->name ], $terms ) ];
	}
	return $out;
}

function hpe_breadcrumbs( $term = null ): array {
	$crumbs = [ [ 'name' => 'Home', 'url' => home_url( '/' ) ] ];
	if ( $term ) {
		foreach ( array_reverse( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) ) as $id ) {
			$a = get_term( $id, 'product_cat' );
			if ( $a && ! is_wp_error( $a ) ) { $crumbs[] = [ 'name' => $a->name, 'url' => get_term_link( $a ) ]; }
		}
		$crumbs[] = [ 'name' => $term->name, 'url' => '' ];
	} else {
		$crumbs[] = [ 'name' => 'Shop', 'url' => '' ];
	}
	return $crumbs;
}

function hpe_subcategories( int $parent ): array {
	$terms = get_terms( [ 'taxonomy' => 'product_cat', 'parent' => $parent, 'hide_empty' => true ] );
	if ( is_wp_error( $terms ) ) { return []; }
	return array_map( fn( $t ) => [ 'name' => $t->name, 'url' => get_term_link( $t ), 'count' => $t->count ], $terms );
}
