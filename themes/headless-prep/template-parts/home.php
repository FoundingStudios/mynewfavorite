<?php
/** Home: Hero, CategorySpotlight, FeaturedCollection. Edit the four text values below. */
$eyebrow   = get_bloginfo( 'name' );
$headline  = 'Shop the collection';
$subhead   = get_bloginfo( 'description' ) ?: 'Browse our latest arrivals and customer favorites.';
$cta_label = 'Shop now';

$hero_file = get_theme_file_path( 'assets/hero.jpg' ); // save your banner photo here
$hero_url  = file_exists( $hero_file ) ? get_theme_file_uri( 'assets/hero.jpg' ) : '';
$cats      = array_slice( hpe_categories(), 0, 6 );
$featured  = hpe_featured( 8 );
?>

<!-- Hero component -->
<section class="grid items-center gap-12 pb-20 lg:grid-cols-2 lg:gap-16 lg:pb-28">
	<div class="space-y-8">
		<p class="text-xs font-bold uppercase tracking-[0.2em] text-accent-600"><?php echo esc_html( $eyebrow ); ?></p>
		<h1 class="text-5xl font-bold leading-[1.05] tracking-tight text-brand-900 lg:text-7xl"><?php echo esc_html( $headline ); ?></h1>
		<p class="max-w-md text-lg font-light leading-relaxed text-gray-600"><?php echo esc_html( $subhead ); ?></p>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="inline-block border-2 border-accent-600 bg-accent-600 px-10 py-4 text-sm font-bold uppercase tracking-widest text-white transition-all duration-300 hover:bg-white hover:text-accent-600 active:scale-95"><?php echo esc_html( $cta_label ); ?></a>
	</div>
	<div class="relative aspect-[4/5] overflow-hidden bg-gradient-to-br from-brand-50 to-brand-100 lg:aspect-square">
		<?php if ( $hero_url ) : ?>
			<img src="<?php echo esc_url( $hero_url ); ?>" alt="" class="h-full w-full object-cover">
		<?php else : ?>
			<div class="flex h-full items-center justify-center p-10 text-center text-sm font-light text-brand-700">Banner image placeholder.<br>Save a photo as assets/hero.jpg in the theme.</div>
		<?php endif; ?>
	</div>
</section>

<!-- CategorySpotlight component -->
<?php if ( $cats ) : ?>
<section class="border-t border-gray-100 py-20 lg:py-24">
	<h2 class="mb-12 text-3xl font-bold tracking-tight">Shop by category</h2>
	<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 lg:gap-8">
		<?php foreach ( $cats as $c ) : ?>
			<a href="<?php echo esc_url( $c['url'] ); ?>" class="group relative block aspect-square overflow-hidden bg-gradient-to-br from-brand-900 to-brand-700 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg">
				<?php if ( $c['image'] ) : ?><img src="<?php echo esc_url( $c['image'] ); ?>" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"><?php endif; ?>
				<span class="absolute inset-0 bg-black/40 transition-colors duration-300 group-hover:bg-black/55"></span>
				<span class="absolute inset-0 flex flex-col items-center justify-center gap-1 px-4 text-center text-white">
					<span class="text-2xl font-bold tracking-tight"><?php echo esc_html( $c['name'] ); ?></span>
					<span class="text-sm font-light"><?php echo esc_html( sprintf( _n( '%d product', '%d products', $c['count'], 'headless-prep' ), $c['count'] ) ); ?></span>
				</span>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- FeaturedCollection component -->
<?php if ( $featured ) : ?>
<section class="border-t border-gray-100 py-20 lg:py-24">
	<div class="mb-12 flex items-end justify-between gap-6">
		<h2 class="text-3xl font-bold tracking-tight">Featured products</h2>
		<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="text-sm font-medium text-brand-700 transition-colors duration-300 hover:text-accent-600">View all</a>
	</div>
	<div class="grid grid-cols-1 gap-x-8 gap-y-12 sm:grid-cols-2 lg:grid-cols-4">
		<?php foreach ( $featured as $item ) { get_template_part( 'template-parts/product-card-quick', null, [ 'item' => $item ] ); } ?>
	</div>
</section>
<?php endif; ?>
