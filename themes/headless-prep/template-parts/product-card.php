<?php /** ProductCard component. Props: $args['item'] */ $item = $args['item']; ?>
<article class="group relative flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
	<div class="aspect-square overflow-hidden bg-gray-50">
		<?php if ( $item['image'] ) : ?>
			<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" loading="lazy" class="h-full w-full object-cover object-center transition-transform duration-500 group-hover:scale-105">
		<?php endif; ?>
	</div>
	<?php if ( $item['onSale'] ) : ?><span class="absolute left-3 top-3 rounded-full bg-accent-600 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">Sale</span><?php endif; ?>
	<div class="flex flex-1 flex-col gap-4 p-6">
		<h3 class="line-clamp-2 min-h-[2.75rem] text-sm font-medium leading-snug"><a href="<?php echo esc_url( $item['url'] ); ?>"><span class="absolute inset-0" aria-hidden="true"></span><?php echo esc_html( $item['title'] ); ?></a></h3>
		<p class="text-lg font-bold"><?php echo esc_html( $item['price'] ); ?></p>
		<div class="mt-auto">
			<?php if ( ! $item['inStock'] ) : ?>
				<span class="block rounded-full bg-gray-100 px-4 py-3 text-center text-sm font-medium text-gray-400">Out of stock</span>
			<?php else : ?>
				<a href="<?php echo esc_url( $item['cartUrl'] ); ?>" class="relative z-10 block rounded-full bg-brand-700 px-4 py-3 text-center text-sm font-bold text-white transition-all duration-300 hover:bg-brand-900 hover:shadow-md active:scale-95"><?php echo 'simple' === $item['type'] ? 'Add to cart' : 'Choose options'; ?></a>
			<?php endif; ?>
		</div>
	</div>
</article>
