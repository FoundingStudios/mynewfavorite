<?php
$cats  = hpe_categories();
$count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'bg-white text-gray-900 antialiased min-h-screen flex flex-col' ); ?>>
<?php wp_body_open(); ?>
<a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:z-50 focus:bg-white focus:p-3">Skip to content</a>

<!-- Header component -->
<header class="sticky top-0 z-40 border-b border-gray-100 bg-white/95 backdrop-blur">
	<div class="mx-auto flex h-20 max-w-7xl items-center gap-4 px-4 sm:px-6 lg:px-8">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="text-2xl font-bold tracking-tight text-brand-700 transition-colors duration-300 hover:text-brand-900"><?php bloginfo( 'name' ); ?></a>
		<nav class="hidden flex-1 items-center gap-8 text-sm font-medium text-gray-600 md:flex" aria-label="Categories">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="transition-colors duration-300 hover:text-gray-900">Shop</a>
			<?php foreach ( array_slice( $cats, 0, 5 ) as $c ) : ?>
				<a href="<?php echo esc_url( $c['url'] ); ?>" class="transition-colors duration-300 hover:text-gray-900"><?php echo esc_html( $c['name'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<form role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>" class="ml-auto hidden sm:block">
			<input type="hidden" name="post_type" value="product">
			<input type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="Search products" aria-label="Search products" class="w-48 rounded-full border border-gray-200 bg-gray-50 px-4 py-2 text-sm font-light transition-all duration-300 focus:border-brand-600 focus:bg-white focus:outline-none lg:w-64">
		</form>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="text-sm text-gray-600 transition-colors duration-300 hover:text-gray-900">Account</a>
		<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="relative text-gray-700 transition-colors duration-300 hover:text-brand-700" aria-label="Cart, <?php echo (int) $count; ?> items">
			<svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
			<span class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-accent-600 text-xs font-bold text-white"><?php echo (int) $count; ?></span>
		</a>
	</div>
	<!-- Mobile menu (no JavaScript) -->
	<details class="border-t border-gray-100 md:hidden">
		<summary class="cursor-pointer px-4 py-2 text-sm font-medium text-gray-600">Menu</summary>
		<div class="flex flex-col gap-2 px-4 pb-3 text-sm">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>">Shop</a>
			<?php foreach ( $cats as $c ) : ?><a href="<?php echo esc_url( $c['url'] ); ?>"><?php echo esc_html( $c['name'] ); ?></a><?php endforeach; ?>
			<form role="search" action="<?php echo esc_url( home_url( '/' ) ); ?>"><input type="hidden" name="post_type" value="product"><input type="search" name="s" placeholder="Search products" aria-label="Search products" class="w-full rounded-md border border-gray-300 px-3 py-1.5"></form>
		</div>
	</details>
</header>

<main id="main" class="mx-auto w-full max-w-7xl flex-grow px-4 py-12 sm:px-6 lg:px-8 lg:py-16">
