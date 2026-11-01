<?php /** ProductPage component. */
while ( have_posts() ) : the_post();
	global $product;
	$product = wc_get_product( get_the_ID() );
	$d = hpe_product_full( $product );
?>
<div class="grid gap-12 md:grid-cols-2 lg:gap-16">
	<!-- Gallery component -->
	<div>
		<div class="aspect-square overflow-hidden rounded-2xl border border-gray-100 bg-gray-50">
			<?php if ( $d['gallery'] ) : ?><img id="main-img" src="<?php echo esc_url( $d['gallery'][0] ); ?>" alt="<?php echo esc_attr( $d['title'] ); ?>" class="h-full w-full object-cover"><?php endif; ?>
		</div>
		<?php if ( count( $d['gallery'] ) > 1 ) : ?>
			<div class="mt-3 grid grid-cols-5 gap-2">
				<?php foreach ( $d['gallery'] as $i => $src ) : ?>
					<button type="button" data-thumb="<?php echo esc_url( $src ); ?>" class="aspect-square overflow-hidden rounded-lg border border-gray-100 transition-all duration-300 hover:-translate-y-0.5 hover:shadow-md" aria-label="Show image <?php echo (int) $i + 1; ?>"><img src="<?php echo esc_url( $src ); ?>" alt="" class="h-full w-full object-cover"></button>
				<?php endforeach; ?>
			</div>
			<script>document.querySelectorAll('[data-thumb]').forEach(function(b){b.addEventListener('click',function(){document.getElementById('main-img').src=b.dataset.thumb;});});</script>
		<?php endif; ?>
	</div>
	<!-- ProductInfo component -->
	<div>
		<h1 class="text-3xl font-bold tracking-tight lg:text-4xl"><?php echo esc_html( $d['title'] ); ?></h1>
		<p class="mt-4 text-2xl font-bold"><?php echo esc_html( $d['price'] ); ?>
			<?php if ( $d['onSale'] && 'simple' === $d['type'] ) : ?><span class="ml-2 text-base font-normal text-gray-400 line-through"><?php echo esc_html( $d['regular'] ); ?></span><?php endif; ?></p>
		<p class="mt-1 text-sm <?php echo $d['inStock'] ? 'text-green-700' : 'text-red-600'; ?>"><?php echo $d['inStock'] ? 'In stock' : 'Out of stock'; ?></p>
		<div class="mt-6 font-light leading-relaxed text-gray-600"><?php echo $d['short']; // already sanitized in the data layer ?></div>
		<div class="mt-8"><?php woocommerce_template_single_add_to_cart(); ?></div>
		<p class="mt-6 text-xs text-gray-500"><?php echo $d['sku'] ? 'SKU: ' . esc_html( $d['sku'] ) . ' · ' : ''; ?><?php echo esc_html( $d['categories'] ); ?></p>
	</div>
</div>
<?php if ( $d['description'] ) : ?>
	<section class="mt-20 border-t border-gray-100 pt-12"><h2 class="mb-4 text-2xl font-bold tracking-tight">Details</h2><div class="max-w-3xl text-gray-700"><?php echo $d['description']; ?></div></section>
<?php endif; ?>
<?php if ( $d['related'] ) : ?>
	<section class="mt-20"><h2 class="mb-8 text-2xl font-bold tracking-tight">You may also like</h2>
		<div class="grid grid-cols-2 gap-x-4 gap-y-8 sm:gap-x-8 sm:gap-y-12 lg:grid-cols-4"><?php foreach ( $d['related'] as $item ) { get_template_part( 'template-parts/product-card', null, [ 'item' => $item ] ); } ?></div>
	</section>
<?php endif; ?>
<?php endwhile; ?>
