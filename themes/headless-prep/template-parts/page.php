<?php /** Generic page: cart, checkout, account, posts, 404. */ ?>
<div class="mx-auto max-w-4xl">
<?php if ( is_404() ) : ?>
	<h1 class="text-2xl font-bold">Page not found</h1>
	<p class="mt-3 text-gray-600">That page doesn’t exist. <a class="text-brand-700 underline" href="<?php echo esc_url( home_url( '/' ) ); ?>">Back to the shop</a>.</p>
<?php else : while ( have_posts() ) : the_post(); ?>
	<h1 class="mb-8 text-3xl font-bold tracking-tight"><?php the_title(); ?></h1>
	<div class="rounded-2xl border border-gray-100 bg-white p-6 sm:p-10"><?php the_content(); ?></div>
<?php endwhile; endif; ?>
</div>
