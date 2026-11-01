<?php
/** Catalog: home, shop, category and search results. */
$term  = ( function_exists( 'is_product_category' ) && is_product_category() ) ? get_queried_object() : null;
$q     = is_search() ? get_search_query() : '';
$data  = hpe_catalog( $term ? $term->slug : '', $q );
$page  = max( 1, absint( $_GET['pg'] ?? 1 ) );
$home  = is_front_page() && ! $q && 1 === $page;
$title = $q ? 'Results for “' . $q . '”' : ( $term ? $term->name : ( $home ? 'New arrivals' : 'Shop' ) );
$h     = $home ? '2' : '1';
?>

<div class="mb-8 flex flex-wrap items-end justify-between gap-6 border-b border-gray-100 pb-6">
	<h<?php echo $h; ?> class="text-3xl font-bold tracking-tight"><?php echo esc_html( $title ); ?></h<?php echo $h; ?>>
	<!-- SortSelect component -->
	<form method="get" class="flex items-center gap-3 text-sm">
		<?php if ( $q ) : ?><input type="hidden" name="s" value="<?php echo esc_attr( $q ); ?>"><input type="hidden" name="post_type" value="product"><?php endif; ?>
		<label for="sort" class="font-light text-gray-500">Sort by</label>
		<select id="sort" name="sort" onchange="this.form.submit()" class="rounded-full border border-gray-200 px-4 py-2 font-medium transition-colors duration-300 hover:border-gray-400">
			<?php foreach ( [ 'newest' => 'Newest', 'price-asc' => 'Price: low to high', 'price-desc' => 'Price: high to low', 'name' => 'Name' ] as $k => $label ) : ?>
				<option value="<?php echo esc_attr( $k ); ?>" <?php selected( $data['sort'], $k ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<noscript><button class="rounded-full border px-4 py-2">Apply</button></noscript>
	</form>
</div>

<!-- CategoryChips component -->
<nav class="mb-12 flex flex-wrap gap-3 text-sm" aria-label="Filter by category">
	<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="rounded-full border px-5 py-2 font-medium transition-all duration-300 hover:-translate-y-0.5 active:scale-95 <?php echo $term ? 'border-gray-200 text-gray-700 hover:border-gray-400' : 'border-brand-700 bg-brand-700 text-white'; ?>">All</a>
	<?php foreach ( hpe_categories() as $c ) : $on = $term && $term->slug === $c['slug']; ?>
		<a href="<?php echo esc_url( $c['url'] ); ?>" class="rounded-full border px-5 py-2 font-medium transition-all duration-300 hover:-translate-y-0.5 active:scale-95 <?php echo $on ? 'border-brand-700 bg-brand-700 text-white' : 'border-gray-200 text-gray-700 hover:border-gray-400'; ?>"><?php echo esc_html( $c['name'] ); ?></a>
	<?php endforeach; ?>
</nav>

<!-- ProductGrid component -->
<?php if ( $data['items'] ) : ?>
	<div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-8 sm:gap-y-12 lg:grid-cols-4">
		<?php foreach ( $data['items'] as $item ) { get_template_part( 'template-parts/product-card', null, [ 'item' => $item ] ); } ?>
	</div>
<?php else : ?>
	<p class="py-24 text-center font-light text-gray-500">No products found.</p>
<?php endif; ?>

<!-- Pagination component -->
<?php if ( $data['pages'] > 1 ) : ?>
	<nav class="mt-16 flex flex-wrap justify-center gap-3 text-sm" aria-label="Pagination">
		<?php for ( $i = 1; $i <= $data['pages']; $i++ ) : ?>
			<a href="<?php echo esc_url( add_query_arg( 'pg', $i ) ); ?>" <?php echo $i === $page ? 'aria-current="page"' : ''; ?> class="rounded-full border px-4 py-2 font-medium transition-all duration-300 active:scale-95 <?php echo $i === $page ? 'border-brand-700 bg-brand-700 text-white' : 'border-gray-200 hover:border-gray-400'; ?>"><?php echo (int) $i; ?></a>
		<?php endfor; ?>
	</nav>
<?php endif; ?>
