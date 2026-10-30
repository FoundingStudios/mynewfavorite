<?php
/**
 * Plugin Name: Headless Commerce Engine
 * Description: Brand taxonomy, custom product fields and clean, typed GraphQL data for a headless Next.js storefront.
 * Version:     0.1.0
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce, wp-graphql, wp-graphql-woocommerce
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Taxonomy + custom field (REST + GraphQL)
 * ---------------------------------------------------------------------- */
add_action( 'init', function () {

	// Note: WooCommerce 9.6+ may ship a native "product_brand" taxonomy, so this
	// uses its own slug (hce_brand) to avoid a collision. No front-end rewrite.
	register_taxonomy( 'hce_brand', [ 'product' ], [
		'labels'              => [ 'name' => 'Brands', 'singular_name' => 'Brand' ],
		'public'              => true,
		'hierarchical'        => false,
		'show_admin_column'   => true,
		'rewrite'             => false,
		'show_in_rest'        => true,
		'show_in_graphql'     => true,
		'graphql_single_name' => 'HceBrand',
		'graphql_plural_name' => 'HceBrands',
	] );

	// 'show_in_graphql' is not a core meta argument (WPGraphQL ignores it here);
	// the real GraphQL exposure is register_graphql_field() below. Kept so every
	// custom field declares both flags explicitly.
	register_post_meta( 'product', '_hce_subtitle', [
		'type'              => 'string',
		'single'            => true,
		'description'       => 'Short plain-text subtitle',
		'show_in_rest'      => true,
		'show_in_graphql'   => true,
		'sanitize_callback' => 'sanitize_text_field',
		'auth_callback'     => fn() => current_user_can( 'edit_products' ),
	] );
} );

/* -------------------------------------------------------------------------
 * Helpers
 * ---------------------------------------------------------------------- */
function hce_plain( $value ): string {
	return trim( wp_strip_all_tags( html_entity_decode( (string) $value, ENT_QUOTES | ENT_HTML5 ) ) );
}

function hce_price( ?float $v ): ?float {
	return ( null === $v || '' === $v ) ? null : round( $v, wc_get_price_decimals() );
}

function hce_attr_pairs( WC_Product $p ): array {
	$out = [];
	foreach ( $p->get_attributes() as $key => $value ) {
		$term  = taxonomy_exists( $key ) ? get_term_by( 'slug', $value, $key ) : null;
		$out[] = [
			'name'  => hce_plain( wc_attribute_label( $key, $p ) ),
			'slug'  => (string) $key,
			'value' => hce_plain( $term ? $term->name : $value ),
		];
	}
	return $out;
}

function hce_price_data( WC_Product $p ): array {
	$variable = $p->is_type( 'variable' );
	return [
		'currency' => get_woocommerce_currency(),
		'regular'  => hce_price( (float) $p->get_regular_price() ?: null ),
		'sale'     => hce_price( $p->get_sale_price() !== '' ? (float) $p->get_sale_price() : null ),
		'current'  => hce_price( $p->get_price() !== '' ? (float) $p->get_price() : null ),
		'min'      => hce_price( $variable ? (float) $p->get_variation_price( 'min' ) : (float) $p->get_price() ),
		'max'      => hce_price( $variable ? (float) $p->get_variation_price( 'max' ) : (float) $p->get_price() ),
		'onSale'   => $p->is_on_sale(),
	];
}

/* -------------------------------------------------------------------------
 * GraphQL: typed objects only, numbers stay numbers, no HTML strings.
 * ---------------------------------------------------------------------- */
add_action( 'graphql_register_types', function () {

	register_graphql_object_type( 'HcePrice', [
		'description' => 'Unformatted pricing (numbers, no currency markup).',
		'fields'      => [
			'currency' => [ 'type' => 'String' ],
			'regular'  => [ 'type' => 'Float' ],
			'sale'     => [ 'type' => 'Float' ],
			'current'  => [ 'type' => 'Float' ],
			'min'      => [ 'type' => 'Float' ],
			'max'      => [ 'type' => 'Float' ],
			'onSale'   => [ 'type' => 'Boolean' ],
		],
	] );

	register_graphql_object_type( 'HceAttribute', [
		'description' => 'Product attribute with its plain-text options (e.g. Size: S, M, L).',
		'fields'      => [
			'name'         => [ 'type' => 'String' ],
			'slug'         => [ 'type' => 'String' ],
			'options'      => [ 'type' => [ 'list_of' => 'String' ] ],
			'forVariation' => [ 'type' => 'Boolean' ],
		],
	] );

	register_graphql_object_type( 'HceVariationAttribute', [
		'fields' => [
			'name'  => [ 'type' => 'String' ],
			'slug'  => [ 'type' => 'String' ],
			'value' => [ 'type' => 'String' ],
		],
	] );

	register_graphql_object_type( 'HceVariation', [
		'fields' => [
			'databaseId' => [ 'type' => 'Int' ],
			'sku'        => [ 'type' => 'String' ],
			'inStock'    => [ 'type' => 'Boolean' ],
			'price'      => [ 'type' => 'HcePrice' ],
			'attributes' => [ 'type' => [ 'list_of' => 'HceVariationAttribute' ] ],
		],
	] );

	register_graphql_object_type( 'HceProductData', [
		'description' => 'Clean product data for headless frontends.',
		'fields'      => [
			'subtitle'   => [ 'type' => 'String' ],
			'price'      => [ 'type' => 'HcePrice' ],
			'attributes' => [ 'type' => [ 'list_of' => 'HceAttribute' ] ],
			'variations' => [ 'type' => [ 'list_of' => 'HceVariation' ] ],
		],
	] );

	$resolver = function ( $source ) {
		$id = (int) ( $source->databaseId ?? $source->ID ?? 0 );
		$p  = $id ? wc_get_product( $id ) : null;
		if ( ! $p ) {
			return null;
		}

		$attributes = [];
		foreach ( $p->get_attributes() as $attr ) {
			if ( ! $attr instanceof WC_Product_Attribute ) {
				continue;
			}
			$options = $attr->is_taxonomy()
				? wc_get_product_terms( $p->get_id(), $attr->get_name(), [ 'fields' => 'names' ] )
				: $attr->get_options();
			$attributes[] = [
				'name'         => hce_plain( wc_attribute_label( $attr->get_name(), $p ) ),
				'slug'         => $attr->get_name(),
				'options'      => array_map( 'hce_plain', $options ),
				'forVariation' => $attr->get_variation(),
			];
		}

		$variations = [];
		if ( $p->is_type( 'variable' ) ) {
			foreach ( $p->get_children() as $vid ) {
				$v = wc_get_product( $vid );
				if ( ! $v || ! $v->exists() ) {
					continue;
				}
				$variations[] = [
					'databaseId' => $v->get_id(),
					'sku'        => $v->get_sku(),
					'inStock'    => $v->is_in_stock(),
					'price'      => hce_price_data( $v ),
					'attributes' => hce_attr_pairs( $v ),
				];
			}
		}

		return [
			'subtitle'   => hce_plain( get_post_meta( $p->get_id(), '_hce_subtitle', true ) ),
			'price'      => hce_price_data( $p ),
			'attributes' => $attributes,
			'variations' => $variations,
		];
	};

	// Exposed on each concrete product type: product { headless { ... } }
	foreach ( [ 'SimpleProduct', 'VariableProduct', 'ExternalProduct', 'GroupProduct' ] as $type ) {
		register_graphql_field( $type, 'headless', [
			'type'        => 'HceProductData',
			'description' => 'Plain, typed product data (no HTML).',
			'resolve'     => $resolver,
		] );
	}
} );
