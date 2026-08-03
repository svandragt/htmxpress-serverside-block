<?php
/**
 * Pure render/filter logic for the block, kept separate from hook
 * registration so it can be unit tested without a WordPress bootstrap.
 */

function htmx_server_block_render_callback() {
	ob_start();
	load_template( __DIR__ . '/../templates/random_posts.php' );

	return ob_get_clean();
}

function htmx_server_block_register_template_paths( $paths ) {
	$paths[] = __DIR__ . '/../templates';

	return $paths;
}
