<?php /** ProductTile component (quick-add). Props: $args['item'] */
$item = $args['item'];
$full = (int) round( $item['rating'] );
?>
<article class="group relative flex flex-col">
	<div class="relative aspect-square overflow-hidden bg-gray-50 transition-all duration-300 group-hover:shadow-lg">
		<?php if ( $item['image'] ) : ?>
			<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105">
		<?php endif; ?>
		<?php if ( $item['onSale'] ) : ?><span class="absolute left-3 top-3 bg-accent-600 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">Sale</span><?php endif; ?>
		<?php if ( $item['inStock'] ) : ?>
			<a href="<?php echo esc_url( $item['cartUrl'] ); ?>" aria-label="<?php echo esc_attr( ( 'simple' === $item['type'] ? 'Add to cart: ' : 'Choose options: ' ) . $item['title'] ); ?>" class="absolute bottom-4 right-4 z-10 flex h-12 w-12 items-center justify-center rounded-full bg-white text-brand-900 shadow-md transition-all duration-300 hover:bg-accent-600 hover:text-white active:scale-95 lg:translate-y-2 lg:opacity-0 lg:group-hover:translate-y-0 lg:group-hover:opacity-100 lg:focus-visible:translate-y-0 lg:focus-visible:opacity-100">
				<svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
			</a>
		<?php endif; ?>
	</div>
	<div class="space-y-2 pt-5">
		<h3 class="text-sm font-medium"><a href="<?php echo esc_url( $item['url'] ); ?>"><span class="absolute inset-0" aria-hidden="true"></span><?php echo esc_html( $item['title'] ); ?></a></h3>
		<div class="flex items-center gap-0.5" role="img" aria-label="<?php echo $item['reviews'] ? esc_attr( sprintf( '%.1f out of 5 stars', $item['rating'] ) ) : 'No reviews yet'; ?>">
			<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
				<svg class="h-4 w-4 <?php echo ( $item['reviews'] && $i <= $full ) ? 'text-amber-400' : 'text-gray-200'; ?>" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 00.95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 00-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 00-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 00-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 00.95-.69l1.07-3.29z"/></svg>
			<?php endfor; ?>
			<span class="ml-1 text-xs font-light text-gray-500"><?php echo $item['reviews'] ? '(' . (int) $item['reviews'] . ')' : 'No reviews yet'; ?></span>
		</div>
		<p class="text-lg font-bold"><?php echo esc_html( $item['price'] ); ?>
			<?php if ( $item['onSale'] && 'simple' === $item['type'] ) : ?><span class="ml-2 text-sm font-light text-gray-400 line-through"><?php echo esc_html( $item['regular'] ); ?></span><?php endif; ?></p>
	</div>
</article>
