</main>

<!-- Footer component -->
<footer class="mt-auto border-t border-gray-100 bg-gray-50">
	<div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-14 text-sm text-gray-500 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
		<p>&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php bloginfo( 'name' ); ?></p>
		<nav class="flex gap-4" aria-label="Footer">
			<a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>" class="transition-colors duration-300 hover:text-gray-900">Shop</a>
			<a href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" class="transition-colors duration-300 hover:text-gray-900">Account</a>
			<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="transition-colors duration-300 hover:text-gray-900">Cart</a>
		</nav>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>
