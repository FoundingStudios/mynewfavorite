<?php
/** CategoryArchive: breadcrumbs, hero, sub-category pills, filter sidebar + product grid. */
$term    = ( function_exists( 'is_product_category' ) && is_product_category() ) ? get_queried_object() : null;
$data    = hpe_catalog( $term ? $term->slug : '' );
$f       = hpe_filters();
$facets  = hpe_facets();
$crumbs  = hpe_breadcrumbs( $term );
$subs    = $term ? hpe_subcategories( $term->term_id ) : [];
$page    = max( 1, absint( $_GET['pg'] ?? 1 ) );
$base    = $term ? get_term_link( $term ) : wc_get_page_permalink( 'shop' );
$desc    = $term ? wp_strip_all_tags( term_description( $term->term_id, 'product_cat' ) ) : '';
$count   = sprintf( _n( '%s product', '%s products', $data['total'], 'headless-prep' ), number_format_i18n( $data['total'] ) );
?>
<!-- Breadcrumbs component -->
<nav aria-label="Breadcrumb" class="mb-8">
	<ol class="flex flex-wrap items-center gap-2 text-xs font-light text-gray-400">
		<?php foreach ( $crumbs as $i => $c ) : ?>
			<?php if ( $i ) : ?><li aria-hidden="true">/</li><?php endif; ?>
			<li><?php if ( $c['url'] ) : ?><a href="<?php echo esc_url( $c['url'] ); ?>" class="transition-colors duration-300 hover:text-gray-700"><?php echo esc_html( $c['name'] ); ?></a><?php else : ?><span aria-current="page" class="text-gray-600"><?php echo esc_html( $c['name'] ); ?></span><?php endif; ?></li>
		<?php endforeach; ?>
	</ol>
</nav>

<!-- ArchiveHero component -->
<header class="mb-10 max-w-2xl space-y-3">
	<h1 class="text-4xl font-bold tracking-tight lg:text-5xl"><?php echo esc_html( $term ? $term->name : 'Shop' ); ?></h1>
	<p class="font-light leading-relaxed text-gray-500"><?php echo esc_html( $desc ? $desc . ' ' : '' ); ?><span class="font-medium text-gray-700"><?php echo esc_html( $count ); ?></span></p>
</header>

<!-- SubcategoryPills component -->
<?php if ( $subs ) : ?>
	<nav class="mb-10 flex flex-wrap gap-3 text-sm" aria-label="Sub-categories">
		<?php foreach ( $subs as $s ) : ?>
			<a href="<?php echo esc_url( $s['url'] ); ?>" class="rounded-full border border-gray-200 px-5 py-2 font-medium text-gray-700 transition-all duration-300 hover:-translate-y-0.5 hover:border-brand-700 hover:text-brand-700 active:scale-95"><?php echo esc_html( $s['name'] ); ?> <span class="font-light text-gray-400">(<?php echo (int) $s['count']; ?>)</span></a>
		<?php endforeach; ?>
	</nav>
<?php endif; ?>

<!-- Mobile filter toggle -->
<div class="mb-6 lg:hidden">
	<button type="button" aria-controls="filters" aria-expanded="false" onclick="var a=document.getElementById('filters');a.classList.toggle('hidden');this.setAttribute('aria-expanded',!a.classList.contains('hidden'));" class="rounded-full border border-gray-200 px-5 py-2 text-sm font-medium transition-all duration-300 hover:border-gray-400 active:scale-95">Filters</button>
</div>

<div class="grid gap-12 lg:grid-cols-4">
	<!-- FilterSidebar component -->
	<aside id="filters" class="hidden lg:col-span-1 lg:block" aria-label="Product filters">
		<form method="get" action="<?php echo esc_url( $base ); ?>" class="space-y-8">
			<fieldset class="space-y-3">
				<legend class="mb-3 text-sm font-bold">Price</legend>
				<div class="flex items-center gap-2">
					<input type="number" min="0" step="1" name="min" value="<?php echo null !== $f['min'] ? esc_attr( $f['min'] ) : ''; ?>" placeholder="Min" aria-label="Minimum price" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm transition-colors duration-300 focus:border-brand-600 focus:outline-none">
					<span class="text-gray-300">–</span>
					<input type="number" min="0" step="1" name="max" value="<?php echo null !== $f['max'] ? esc_attr( $f['max'] ) : ''; ?>" placeholder="Max" aria-label="Maximum price" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm transition-colors duration-300 focus:border-brand-600 focus:outline-none">
				</div>
			</fieldset>

			<?php foreach ( $facets as $fa ) : ?>
				<fieldset class="space-y-3 border-t border-gray-100 pt-8">
					<legend class="mb-3 text-sm font-bold"><?php echo esc_html( $fa['label'] ); ?></legend>
					<?php foreach ( $fa['terms'] as $t ) : ?>
						<label class="flex cursor-pointer items-center gap-3 text-sm font-light text-gray-700 transition-colors duration-300 hover:text-gray-900">
							<input type="checkbox" name="f[<?php echo esc_attr( $fa['taxonomy'] ); ?>][]" value="<?php echo esc_attr( $t['slug'] ); ?>" <?php checked( in_array( $t['slug'], $f['attrs'][ $fa['taxonomy'] ] ?? [], true ) ); ?> class="h-4 w-4 accent-brand-700">
							<?php echo esc_html( $t['name'] ); ?>
						</label>
					<?php endforeach; ?>
				</fieldset>
			<?php endforeach; ?>

			<fieldset class="border-t border-gray-100 pt-8">
				<legend class="mb-3 text-sm font-bold">Availability</legend>
				<label class="flex cursor-pointer items-center gap-3 text-sm font-light text-gray-700">
					<input type="checkbox" name="stock" value="1" <?php checked( $f['stock'] ); ?> class="h-4 w-4 accent-brand-700"> In stock only
				</label>
			</fieldset>

			<div class="flex items-center gap-4 border-t border-gray-100 pt-8">
				<button type="submit" class="rounded-full bg-brand-700 px-6 py-2.5 text-sm font-bold text-white transition-all duration-300 hover:bg-brand-900 hover:shadow-md active:scale-95">Apply</button>
				<a href="<?php echo esc_url( $base ); ?>" class="text-sm font-medium text-gray-500 transition-colors duration-300 hover:text-gray-900">Clear all</a>
			</div>
		</form>
	</aside>

	<div class="lg:col-span-3">
		<!-- SortLinks component -->
		<div class="mb-8 flex flex-wrap items-center gap-x-6 gap-y-2 border-b border-gray-100 pb-4 text-sm">
			<span class="font-light text-gray-500">Sort by</span>
			<?php foreach ( [ 'newest' => 'Newest', 'price-asc' => 'Price ↑', 'price-desc' => 'Price ↓', 'name' => 'Name' ] as $k => $label ) : ?>
				<a href="<?php echo esc_url( add_query_arg( 'sort', $k, remove_query_arg( 'pg' ) ) ); ?>" <?php echo $data['sort'] === $k ? 'aria-current="true"' : ''; ?> class="transition-colors duration-300 <?php echo $data['sort'] === $k ? 'font-bold text-brand-700' : 'font-medium text-gray-500 hover:text-gray-900'; ?>"><?php echo esc_html( $label ); ?></a>
			<?php endforeach; ?>
		</div>

		<!-- ProductGrid component -->
		<?php if ( $data['items'] ) : ?>
			<div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-8 sm:gap-y-12 lg:grid-cols-3">
				<?php foreach ( $data['items'] as $item ) { get_template_part( 'template-parts/product-card', null, [ 'item' => $item ] ); } ?>
			</div>
		<?php else : ?>
			<p class="py-24 text-center font-light text-gray-500">No products match your filters. <a href="<?php echo esc_url( $base ); ?>" class="font-medium text-brand-700 underline">Clear filters</a></p>
		<?php endif; ?>

		<!-- Pagination component -->
		<?php if ( $data['pages'] > 1 ) : ?>
			<nav class="mt-16 flex flex-wrap justify-center gap-3 text-sm" aria-label="Pagination">
				<?php for ( $i = 1; $i <= $data['pages']; $i++ ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'pg', $i ) ); ?>" <?php echo $i === $page ? 'aria-current="page"' : ''; ?> class="rounded-full border px-4 py-2 font-medium transition-all duration-300 active:scale-95 <?php echo $i === $page ? 'border-brand-700 bg-brand-700 text-white' : 'border-gray-200 hover:border-gray-400'; ?>"><?php echo (int) $i; ?></a>
				<?php endfor; ?>
			</nav>
		<?php endif; ?>
	</div>
</div>
